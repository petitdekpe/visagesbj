<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260823025703 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Editorial structure for personnalite: rename bio to parcours, add contribution_benin, rayonnement, actualite (date/text/url), sources, and category';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite ADD contribution_benin LONGTEXT DEFAULT NULL, ADD rayonnement LONGTEXT DEFAULT NULL, ADD actualite_date DATETIME DEFAULT NULL, ADD actualite_text LONGTEXT DEFAULT NULL, ADD actualite_url VARCHAR(255) DEFAULT NULL, ADD category VARCHAR(255) DEFAULT NULL, ADD sources LONGTEXT DEFAULT NULL, CHANGE bio parcours LONGTEXT DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE personnalite ADD bio LONGTEXT DEFAULT NULL, DROP parcours, DROP contribution_benin, DROP rayonnement, DROP actualite_date, DROP actualite_text, DROP actualite_url, DROP category, DROP sources');
    }
}
