<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823172004 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add fongbe translation fields to personnalite (role, parcours, contribution_benin, rayonnement, actualite_text, achievements, sources)';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite ADD role_fongbe VARCHAR(255) DEFAULT NULL, ADD parcours_fongbe LONGTEXT DEFAULT NULL, ADD contribution_benin_fongbe LONGTEXT DEFAULT NULL, ADD rayonnement_fongbe LONGTEXT DEFAULT NULL, ADD actualite_text_fongbe LONGTEXT DEFAULT NULL, ADD achievements_fongbe LONGTEXT DEFAULT NULL, ADD sources_fongbe LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite DROP role_fongbe, DROP parcours_fongbe, DROP contribution_benin_fongbe, DROP rayonnement_fongbe, DROP actualite_text_fongbe, DROP achievements_fongbe, DROP sources_fongbe');
    }
}
