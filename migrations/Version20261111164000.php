<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Migration: Add status to users and created_by_user_id to products for Staff Role & Ownership support.
 */
final class Version20261111164000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add status to users and created_by_user_id to products for Staff Role and Record Ownership support';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD status VARCHAR(20) DEFAULT \'active\' NOT NULL');
        $this->addSql('ALTER TABLE products ADD created_by_user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE products ADD CONSTRAINT FK_B3BA5A5A7D182D95 FOREIGN KEY (created_by_user_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_B3BA5A5A7D182D95 ON products (created_by_user_id)');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE products DROP FOREIGN KEY FK_B3BA5A5A7D182D95');
        $this->addSql('DROP INDEX IDX_B3BA5A5A7D182D95 ON products');
        $this->addSql('ALTER TABLE products DROP created_by_user_id');
        $this->addSql('ALTER TABLE users DROP status');
    }
}
