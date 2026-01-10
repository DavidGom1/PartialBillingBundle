<?php

namespace KimaiPlugin\SimpleAccountingBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use KimaiPlugin\SimpleAccountingBundle\Entity\SimpleEntry;

class SimpleEntryRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SimpleEntry::class);
    }

    public function getSumForProject(int $projectId): float
    {
        $qb = $this->createQueryBuilder('s');
        $qb->select('SUM(s.amount)')
           ->where('IDENTITY(s.project) = :project')
           ->setParameter('project', $projectId);

        try {
            $result = $qb->getQuery()->getSingleScalarResult();
            return $result === null ? 0.0 : (float) $result;
        } catch (\Exception $e) {
            return 0.0;
        }
    }
}
