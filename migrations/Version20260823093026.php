<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823093026 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add editorial_enabled toggle to personnalite (per-person switch between the new editorial layout and the legacy simple display)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite ADD editorial_enabled TINYINT DEFAULT 0 NOT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite DROP editorial_enabled');
    }
}
