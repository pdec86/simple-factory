<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20240215084433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE t_factoryManufactureProduct ADD specificProductDimensions_length VARCHAR(20) NOT NULL, ADD specificProductDimensions_width VARCHAR(20) NOT NULL, ADD specificProductDimensions_height VARCHAR(20) NOT NULL');
        $this->addSql('ALTER TABLE t_warehouseProductStorage ADD specificProductDimensions LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:object)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE t_factoryManufactureProduct DROP specificProductDimensions_length, DROP specificProductDimensions_width, DROP specificProductDimensions_height');
        $this->addSql('ALTER TABLE t_warehouseProductStorage DROP specificProductDimensions');
    }
}
