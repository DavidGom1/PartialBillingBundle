<?php

/*
 * This file is part of the Kimai time-tracking app.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace KimaiPlugin\SimpleAccountingBundle\Migrations;

use App\Doctrine\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * @version 1.1
 */
final class Version20260110121252 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Added the database table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE kimai2_partial_billing_entry (id INT AUTO_INCREMENT NOT NULL, project_id INT NOT NULL, amount DOUBLE PRECISION NOT NULL, created_at DATETIME NOT NULL, comment LONGTEXT DEFAULT NULL, INDEX IDX_BFB24132166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE kimai2_partial_billing_entry ADD CONSTRAINT FK_BFB24132166D1F9C FOREIGN KEY (project_id) REFERENCES kimai2_projects (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE kimai2_partial_billing_entry DROP FOREIGN KEY FK_BFB24132166D1F9C');
        $this->addSql('DROP TABLE kimai2_partial_billing_entry');
    }
}
