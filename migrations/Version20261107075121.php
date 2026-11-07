<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261107075121 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Sync MariaDB schema and indexes with Doctrine ORM entities and create messenger_messages table';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE newsletter_subscriptions CHANGE subscribed_at subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE newsletter_subscriptions RENAME INDEX email TO UNIQ_B3C13B0BE7927C74');
        $this->addSql('ALTER TABLE order_items RENAME INDEX order_id TO IDX_62809DB08D9F6D38');
        $this->addSql('ALTER TABLE order_items RENAME INDEX product_id TO IDX_62809DB04584665A');
        $this->addSql('ALTER TABLE orders CHANGE order_date order_date DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE orders RENAME INDEX user_id TO IDX_E52FFDEEA76ED395');
        $this->addSql('ALTER TABLE products CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE reviews CHANGE comment comment LONGTEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE reviews RENAME INDEX user_id TO IDX_6970EB0FA76ED395');
        $this->addSql('ALTER TABLE users CHANGE role role VARCHAR(50) DEFAULT \'customer\' NOT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL');
        $this->addSql('ALTER TABLE users RENAME INDEX email TO UNIQ_1483A5E9E7927C74');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE messenger_messages');
        $this->addSql('ALTER TABLE newsletter_subscriptions CHANGE subscribed_at subscribed_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE newsletter_subscriptions RENAME INDEX uniq_b3c13b0be7927c74 TO email');
        $this->addSql('ALTER TABLE orders CHANGE order_date order_date DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE orders RENAME INDEX idx_e52ffdeea76ed395 TO user_id');
        $this->addSql('ALTER TABLE order_items RENAME INDEX idx_62809db08d9f6d38 TO order_id');
        $this->addSql('ALTER TABLE order_items RENAME INDEX idx_62809db04584665a TO product_id');
        $this->addSql('ALTER TABLE products CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE reviews CHANGE comment comment TEXT DEFAULT NULL, CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE reviews RENAME INDEX idx_6970eb0fa76ed395 TO user_id');
        $this->addSql('ALTER TABLE users CHANGE role role VARCHAR(50) DEFAULT \'customer\', CHANGE created_at created_at DATETIME DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE users RENAME INDEX uniq_1483a5e9e7927c74 TO email');
    }
}
