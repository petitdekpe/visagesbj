<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823174338 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fongbe translation fields for first_name and last_name on personnalite';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite ADD first_name_fongbe VARCHAR(120) DEFAULT NULL, ADD last_name_fongbe VARCHAR(150) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite DROP first_name_fongbe, DROP last_name_fongbe');
    }
}
