<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231230205949 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE t_factoryManufactureProduct (specificProductModelId VARCHAR(255) NOT NULL, quantity INT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', manufactureProductId VARCHAR(255) NOT NULL, PRIMARY KEY(manufactureProductId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE t_warehouseProductStorage (specificProductModelId VARCHAR(255) NOT NULL, areaName VARCHAR(100) NOT NULL, shelf VARCHAR(100) NOT NULL, quantity INT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', storageSpaceId INT UNSIGNED AUTO_INCREMENT NOT NULL, PRIMARY KEY(storageSpaceId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE t_warehouseStorageSpace (name VARCHAR(255) NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', storageSpaceId INT UNSIGNED AUTO_INCREMENT NOT NULL, PRIMARY KEY(storageSpaceId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE t_warehouseProductStorage ADD CONSTRAINT FK_6E9D0D27EEFA6CC1 FOREIGN KEY (storageSpaceId) REFERENCES t_warehouseStorageSpace (storageSpaceId)');
        $this->addSql('ALTER TABLE t_salesSpecificProductModel ADD dimensions_length VARCHAR(20) NOT NULL, ADD dimensions_width VARCHAR(20) NOT NULL, ADD dimensions_height VARCHAR(20) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE t_warehouseProductStorage DROP FOREIGN KEY FK_6E9D0D27EEFA6CC1');
        $this->addSql('DROP TABLE t_factoryManufactureProduct');
        $this->addSql('DROP TABLE t_warehouseProductStorage');
        $this->addSql('DROP TABLE t_warehouseStorageSpace');
        $this->addSql('ALTER TABLE t_salesSpecificProductModel DROP dimensions_length, DROP dimensions_width, DROP dimensions_height');
    }
}
