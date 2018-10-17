<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181017153502 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FFFE54D947');
        $this->addSql('CREATE TABLE group_user_order (id INT AUTO_INCREMENT NOT NULL, group_order_id INT NOT NULL, user_address_id INT NOT NULL, total NUMERIC(10, 2) NOT NULL, order_rewards NUMERIC(10, 2) NOT NULL, carrier_name VARCHAR(255) NOT NULL, tracking_no VARCHAR(255) DEFAULT NULL, is_delivered TINYINT(1) NOT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, INDEX IDX_C026097284AF2147 (group_order_id), INDEX IDX_C026097252D06999 (user_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_user_order_rewards (id INT AUTO_INCREMENT NOT NULL, group_user_order_id INT NOT NULL, user_id INT DEFAULT NULL, user_rewards NUMERIC(10, 2) NOT NULL, created_at INT NOT NULL, INDEX IDX_DE67228432FB477A (group_user_order_id), INDEX IDX_DE672284A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_user_order ADD CONSTRAINT FK_C026097284AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('ALTER TABLE group_user_order ADD CONSTRAINT FK_C026097252D06999 FOREIGN KEY (user_address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE group_user_order_rewards ADD CONSTRAINT FK_DE67228432FB477A FOREIGN KEY (group_user_order_id) REFERENCES group_user_order (id)');
        $this->addSql('ALTER TABLE group_user_order_rewards ADD CONSTRAINT FK_DE672284A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_order_rewards');
        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FF52D06999');
        $this->addSql('DROP INDEX IDX_A505B8FFFE54D947 ON group_order');
        $this->addSql('DROP INDEX IDX_A505B8FF52D06999 ON group_order');
        $this->addSql('ALTER TABLE group_order ADD user_id INT NOT NULL, ADD product_id INT NOT NULL, ADD status VARCHAR(50) NOT NULL, DROP group_id, DROP user_address_id, DROP total, DROP order_rewards, DROP carrier_name, DROP tracking_no, DROP is_delivered, CHANGE updated_at expired_at INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FFA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FF4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_A505B8FFA76ED395 ON group_order (user_id)');
        $this->addSql('CREATE INDEX IDX_A505B8FF4584665A ON group_order (product_id)');
        $this->addSql('ALTER TABLE product_review DROP FOREIGN KEY FK_1B3FC06284AF2147');
        $this->addSql('DROP INDEX IDX_1B3FC06284AF2147 ON product_review');
        $this->addSql('ALTER TABLE product_review CHANGE group_order_id group_user_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC06232FB477A FOREIGN KEY (group_user_order_id) REFERENCES group_user_order (id)');
        $this->addSql('CREATE INDEX IDX_1B3FC06232FB477A ON product_review (group_user_order_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_user_order_rewards DROP FOREIGN KEY FK_DE67228432FB477A');
        $this->addSql('ALTER TABLE product_review DROP FOREIGN KEY FK_1B3FC06232FB477A');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, status VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, expired_at INT DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_6DC044C5A76ED395 (user_id), INDEX IDX_6DC044C54584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_order_rewards (id INT AUTO_INCREMENT NOT NULL, group_order_id INT NOT NULL, user_id INT DEFAULT NULL, user_rewards NUMERIC(10, 2) NOT NULL, created_at INT NOT NULL, INDEX IDX_40B1793E84AF2147 (group_order_id), INDEX IDX_40B1793EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C54584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE group_order_rewards ADD CONSTRAINT FK_40B1793E84AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('ALTER TABLE group_order_rewards ADD CONSTRAINT FK_40B1793EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('DROP TABLE group_user_order');
        $this->addSql('DROP TABLE group_user_order_rewards');
        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FFA76ED395');
        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FF4584665A');
        $this->addSql('DROP INDEX IDX_A505B8FFA76ED395 ON group_order');
        $this->addSql('DROP INDEX IDX_A505B8FF4584665A ON group_order');
        $this->addSql('ALTER TABLE group_order ADD group_id INT NOT NULL, ADD user_address_id INT NOT NULL, ADD total NUMERIC(10, 2) NOT NULL, ADD order_rewards NUMERIC(10, 2) NOT NULL, ADD carrier_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, ADD tracking_no VARCHAR(255) DEFAULT NULL COLLATE utf8mb4_unicode_ci, ADD is_delivered TINYINT(1) NOT NULL, DROP user_id, DROP product_id, DROP status, CHANGE expired_at updated_at INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FF52D06999 FOREIGN KEY (user_address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FFFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('CREATE INDEX IDX_A505B8FFFE54D947 ON group_order (group_id)');
        $this->addSql('CREATE INDEX IDX_A505B8FF52D06999 ON group_order (user_address_id)');
        $this->addSql('DROP INDEX IDX_1B3FC06232FB477A ON product_review');
        $this->addSql('ALTER TABLE product_review CHANGE group_user_order_id group_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC06284AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('CREATE INDEX IDX_1B3FC06284AF2147 ON product_review (group_order_id)');
    }
}
