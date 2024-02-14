<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231228192230 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fd_customBigIntSequence (sequenceId INT UNSIGNED AUTO_INCREMENT NOT NULL, sequenceName VARCHAR(255) NOT NULL, sequenceIncrement LONGTEXT NOT NULL, sequenceMinValue LONGTEXT NOT NULL, sequenceMaxValue LONGTEXT DEFAULT NULL, sequenceCurrentValue LONGTEXT NOT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', PRIMARY KEY(sequenceId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE t_salesProduct (name VARCHAR(255) NOT NULL, description LONGTEXT DEFAULT NULL, discontinued DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', productId INT UNSIGNED AUTO_INCREMENT NOT NULL, PRIMARY KEY(productId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE t_salesSpecificProductModel (name VARCHAR(255) NOT NULL, discontinued DATETIME DEFAULT NULL, createdAt DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', updatedAt DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', specificProductModelId VARCHAR(255) NOT NULL, codeEAN VARCHAR(13) NOT NULL, productId INT UNSIGNED DEFAULT NULL, INDEX IDX_2BD6ED6136799605 (productId), PRIMARY KEY(specificProductModelId)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE t_salesSpecificProductModel ADD CONSTRAINT FK_2BD6ED6136799605 FOREIGN KEY (productId) REFERENCES t_salesProduct (productId)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE t_salesSpecificProductModel DROP FOREIGN KEY FK_2BD6ED6136799605');
        $this->addSql('DROP TABLE fd_customBigIntSequence');
        $this->addSql('DROP TABLE t_salesProduct');
        $this->addSql('DROP TABLE t_salesSpecificProductModel');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
