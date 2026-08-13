<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260813120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Google Analytics settings to site_settings';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings ADD ga_enabled TINYINT(1) NOT NULL, ADD ga_measurement_id VARCHAR(20) DEFAULT NULL');
        $this->addSql('UPDATE site_settings SET ga_enabled = 0');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE site_settings DROP ga_enabled, DROP ga_measurement_id');
    }
}
