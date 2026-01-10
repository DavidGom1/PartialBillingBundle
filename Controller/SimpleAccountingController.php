<?php

namespace KimaiPlugin\SimpleAccountingBundle\Controller;

use App\Controller\AbstractController;
use App\Entity\Timesheet;
use App\Repository\ActivityRepository;
use App\Repository\ProjectRepository;
use App\Repository\TimesheetRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/simple-accounting')]
class SimpleAccountingController extends AbstractController
{
    /**
     * Dashboard: List of projects to access their accounting
     */
    #[Route(path: '/', name: 'simple_accounting_index', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): Response
    {
        $projects = $projectRepository->findAll();

        return $this->render('@SimpleAccounting/index.html.twig', [
            'projects' => $projects,
        ]);
    }

    /**
     * Detail page for a specific project: Stats + Entries Table
     */
    #[Route(path: '/project/{id}', name: 'simple_accounting_project_details', methods: ['GET', 'POST'])]
    public function projectDetails(
        int $id,
        Request $request,
        ProjectRepository $projectRepository,
        TimesheetRepository $timesheetRepository,
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Contracts\Translation\TranslatorInterface $translator
    ): Response
    {
        /** @var \KimaiPlugin\SimpleAccountingBundle\Repository\SimpleEntryRepository $simpleEntryRepository */
        $simpleEntryRepository = $entityManager->getRepository(\KimaiPlugin\SimpleAccountingBundle\Entity\SimpleEntry::class);

        $project = $projectRepository->find($id);
        if (!$project) {
            throw $this->createNotFoundException($translator->trans('partial_billing.error.project_not_found'));
        }

        // --- Handle New Entry Submission (Simple Form on the same page) ---
        if ($request->isMethod('POST')) {
            $amount = (float)$request->request->get('amount');
            $comment = $request->request->get('comment');
            $dateStr = $request->request->get('date');
            
            $date = null;
            if ($dateStr) {
                 try {
                     $date = new \DateTime($dateStr);
                 } catch (\Exception $e) {}
            }

            // Check if editing existing or creating new
            $entryId = $request->request->get('entry_id');
            
            if ($entryId) {
                // Edit
                $entry = $simpleEntryRepository->find($entryId);
                if ($entry) {
                    $entry->setAmount($amount);
                    $entry->setComment($comment);
                    if ($date) {
                        $entry->setCreatedAt($date);
                    }
                    $entityManager->flush();
                    $this->flashSuccess($translator->trans('partial_billing.flash.entry_updated'));
                }
            } else {
                // Create
                $entry = new \KimaiPlugin\SimpleAccountingBundle\Entity\SimpleEntry();
                $entry->setProject($project);
                $entry->setAmount($amount);
                $entry->setComment($comment);
                if ($date) {
                    $entry->setCreatedAt($date);
                }
                $entityManager->persist($entry);
                $entityManager->flush();
                $this->flashSuccess($translator->trans('partial_billing.flash.entry_created'));
            }
            
            return $this->redirectToRoute('simple_accounting_project_details', ['id' => $id]);
        }

        // --- Calculate Stats ---
        $accumulated = 0.0;
        $invoiced = 0.0;
        $simpleEntriesSum = 0.0;
        $errorMsg = null;

        try {
            // 1. Total Work (Billable)
            $qb = $timesheetRepository->createQueryBuilder('t');
            $qb->select('SUM(t.rate)')
               ->where('t.project = :project')
               ->andWhere('t.billable = true');
            $result = $qb->getQuery()->setParameter('project', $project)->getSingleScalarResult();
            $accumulated = $result === null ? 0.0 : (float)$result;

            // 2. Total Invoiced (Exported)
            $qb2 = $timesheetRepository->createQueryBuilder('t');
            $qb2->select('SUM(t.rate)')
                ->where('t.project = :project')
                ->andWhere('t.billable = true')
                ->andWhere('t.exported = true');
            $result2 = $qb2->getQuery()->setParameter('project', $project)->getSingleScalarResult();
            $invoiced = $result2 === null ? 0.0 : (float)$result2;

            // 3. Simple Entries Sum
            $simpleEntriesSum = $simpleEntryRepository->getSumForProject($project->getId());
            
        } catch (\Exception $e) {
            $errorMsg = $e->getMessage();
            $this->flashError($translator->trans('partial_billing.error.calculation', ['%error%' => $e->getMessage()]));
        }

        $remainingBase = $accumulated - $invoiced;
        $finalRemaining = $remainingBase - $simpleEntriesSum;

        // Fetch all entries for the table
        $entries = $simpleEntryRepository->findBy(['project' => $project], ['createdAt' => 'DESC']);

        return $this->render('@SimpleAccounting/view.html.twig', [
            'project' => $project,
            'stats' => [
                'accumulated' => $accumulated,
                'invoiced' => $invoiced,
                'remaining_base' => $remainingBase,
                'simple_entries' => $simpleEntriesSum,
                'final_remaining' => $finalRemaining
            ],
            'entries' => $entries,
            'error_msg' => $errorMsg
        ]);
    }

    #[Route(path: '/entry/{id}/delete', name: 'simple_accounting_entry_delete', methods: ['GET'])]
    public function deleteEntry(
        int $id, 
        \Doctrine\ORM\EntityManagerInterface $entityManager,
        \Symfony\Contracts\Translation\TranslatorInterface $translator
    ): Response
    {
        $simpleEntryRepository = $entityManager->getRepository(\KimaiPlugin\SimpleAccountingBundle\Entity\SimpleEntry::class);
        $entry = $simpleEntryRepository->find($id);
        if ($entry) {
            $projectId = $entry->getProject()->getId();
            $entityManager->remove($entry);
            $entityManager->flush();
            $this->flashSuccess($translator->trans('partial_billing.flash.entry_deleted'));
            
            return $this->redirectToRoute('simple_accounting_project_details', ['id' => $projectId]);
        }
        
        return $this->redirectToRoute('simple_accounting_index');
    }
}
