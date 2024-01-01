<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240101115428 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE t_warehouseProductStorage ADD maxQuantity INT NOT NULL, ADD dimensions_length VARCHAR(20) NOT NULL, ADD dimensions_width VARCHAR(20) NOT NULL, ADD dimensions_height VARCHAR(20) NOT NULL, CHANGE specificProductModelId specificProductModelId VARCHAR(255) DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_82887F3D5E237E06 ON t_warehouseStorageSpace (name)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_82887F3D5E237E06 ON t_warehouseStorageSpace');
        $this->addSql('ALTER TABLE t_warehouseProductStorage DROP maxQuantity, DROP dimensions_length, DROP dimensions_width, DROP dimensions_height, CHANGE specificProductModelId specificProductModelId VARCHAR(255) NOT NULL');
    }
}
