<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20261107143850 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY `order_items_ibfk_2`');
        $this->addSql('ALTER TABLE order_items ADD product_name VARCHAR(255) DEFAULT NULL, ADD options VARCHAR(255) DEFAULT NULL, CHANGE product_id product_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT FK_62809DB04584665A FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE orders ADD order_number VARCHAR(50) DEFAULT NULL, ADD customer_email VARCHAR(180) DEFAULT NULL, ADD phone VARCHAR(50) DEFAULT NULL, ADD delivery_address LONGTEXT DEFAULT NULL, ADD notes LONGTEXT DEFAULT NULL, ADD status_updated_at DATETIME DEFAULT NULL');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_E52FFDEE551F0F81 ON orders (order_number)');
        $this->addSql('CREATE INDEX idx_orders_customer_email ON orders (customer_email)');
        $this->addSql('CREATE INDEX idx_orders_order_number ON orders (order_number)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX UNIQ_E52FFDEE551F0F81 ON orders');
        $this->addSql('DROP INDEX idx_orders_customer_email ON orders');
        $this->addSql('DROP INDEX idx_orders_order_number ON orders');
        $this->addSql('ALTER TABLE orders DROP order_number, DROP customer_email, DROP phone, DROP delivery_address, DROP notes, DROP status_updated_at');
        $this->addSql('ALTER TABLE order_items DROP FOREIGN KEY FK_62809DB04584665A');
        $this->addSql('ALTER TABLE order_items DROP product_name, DROP options, CHANGE product_id product_id INT NOT NULL');
        $this->addSql('ALTER TABLE order_items ADD CONSTRAINT `order_items_ibfk_2` FOREIGN KEY (product_id) REFERENCES products (id) ON DELETE CASCADE');
    }
}
