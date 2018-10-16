<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181016124734 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE command_message (id INT AUTO_INCREMENT NOT NULL, command_class VARCHAR(255) NOT NULL, command_data LONGTEXT NOT NULL, run_at INT DEFAULT NULL, completed_at INT DEFAULT NULL, multithread INT NOT NULL, status VARCHAR(50) NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, upload_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, size VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, md5 VARCHAR(255) NOT NULL, upload_at INT NOT NULL, INDEX IDX_8C9F36106259C97B (upload_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `group` (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT NOT NULL, status VARCHAR(50) NOT NULL, expired_at INT DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_6DC044C5A76ED395 (user_id), INDEX IDX_6DC044C54584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_order (id INT AUTO_INCREMENT NOT NULL, group_id INT NOT NULL, user_address_id INT NOT NULL, total NUMERIC(10, 2) NOT NULL, order_rewards NUMERIC(10, 2) NOT NULL, carrier_name VARCHAR(255) NOT NULL, tracking_no VARCHAR(255) DEFAULT NULL, is_delivered TINYINT(1) NOT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, INDEX IDX_A505B8FFFE54D947 (group_id), INDEX IDX_A505B8FF52D06999 (user_address_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE group_order_rewards (id INT AUTO_INCREMENT NOT NULL, group_order_id INT NOT NULL, user_id INT DEFAULT NULL, user_rewards NUMERIC(10, 2) NOT NULL, created_at INT NOT NULL, INDEX IDX_40B1793E84AF2147 (group_order_id), INDEX IDX_40B1793EA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product (id INT AUTO_INCREMENT NOT NULL, sku VARCHAR(255) DEFAULT NULL, title VARCHAR(255) NOT NULL, short_description LONGTEXT DEFAULT NULL, price NUMERIC(10, 2) DEFAULT NULL, original_price NUMERIC(10, 2) DEFAULT NULL, rewards NUMERIC(10, 2) DEFAULT NULL, stock VARCHAR(255) DEFAULT NULL, freight NUMERIC(10, 2) DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_image (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, file_id INT NOT NULL, priority INT NOT NULL, INDEX IDX_64617F034584665A (product_id), INDEX IDX_64617F0393CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_review (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, group_order_id INT DEFAULT NULL, rate VARCHAR(255) DEFAULT NULL, review LONGTEXT NOT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, INDEX IDX_1B3FC0624584665A (product_id), INDEX IDX_1B3FC06284AF2147 (group_order_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_review_image (id INT AUTO_INCREMENT NOT NULL, product_review_id INT NOT NULL, image_file_id INT NOT NULL, INDEX IDX_5B170A2F508E2016 (product_review_id), INDEX IDX_5B170A2F6DB2EB0 (image_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE product_spec_image (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, file_id INT NOT NULL, priority INT NOT NULL, INDEX IDX_41A27BD44584665A (product_id), INDEX IDX_41A27BD493CB796C (file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE project_meta (id INT AUTO_INCREMENT NOT NULL, meta_key VARCHAR(255) NOT NULL, meta_value LONGTEXT DEFAULT NULL, memo LONGTEXT DEFAULT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE region (id INT AUTO_INCREMENT NOT NULL, parent_id INT DEFAULT NULL, code VARCHAR(50) DEFAULT NULL, name VARCHAR(50) NOT NULL, INDEX IDX_F62F176727ACA70 (parent_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, region_id INT DEFAULT NULL, parent_user_id INT DEFAULT NULL, username VARCHAR(180) NOT NULL, username_canonical VARCHAR(180) NOT NULL, email VARCHAR(180) NOT NULL, email_canonical VARCHAR(180) NOT NULL, enabled TINYINT(1) NOT NULL, salt VARCHAR(255) DEFAULT NULL, password VARCHAR(255) NOT NULL, last_login DATETIME DEFAULT NULL, confirmation_token VARCHAR(180) DEFAULT NULL, password_requested_at DATETIME DEFAULT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:array)\', nickname VARCHAR(255) DEFAULT NULL, total_rewards NUMERIC(10, 2) NOT NULL, wx_open_id VARCHAR(255) DEFAULT NULL, wx_union_id VARCHAR(255) DEFAULT NULL, avatar_url VARCHAR(255) DEFAULT NULL, gender VARCHAR(50) DEFAULT NULL, location VARCHAR(255) DEFAULT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D64992FC23A8 (username_canonical), UNIQUE INDEX UNIQ_8D93D649A0D96FBF (email_canonical), UNIQUE INDEX UNIQ_8D93D649C05FB297 (confirmation_token), UNIQUE INDEX UNIQ_8D93D64998260155 (region_id), UNIQUE INDEX UNIQ_8D93D649D526A7D3 (parent_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_activity (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, page VARCHAR(255) NOT NULL, created_at INT NOT NULL, INDEX IDX_4CF9ED5AA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_address (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, region_id INT DEFAULT NULL, address VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, phone VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, is_deleted TINYINT(1) NOT NULL, created_at INT NOT NULL, updated_at INT DEFAULT NULL, INDEX IDX_5543718BA76ED395 (user_id), INDEX IDX_5543718B98260155 (region_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_share (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_user_id INT DEFAULT NULL, status VARCHAR(50) NOT NULL, created_at INT NOT NULL, INDEX IDX_DC46602A76ED395 (user_id), INDEX IDX_DC46602D526A7D3 (parent_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE file ADD CONSTRAINT FK_8C9F36106259C97B FOREIGN KEY (upload_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C5A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE `group` ADD CONSTRAINT FK_6DC044C54584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FFFE54D947 FOREIGN KEY (group_id) REFERENCES `group` (id)');
        $this->addSql('ALTER TABLE group_order ADD CONSTRAINT FK_A505B8FF52D06999 FOREIGN KEY (user_address_id) REFERENCES user_address (id)');
        $this->addSql('ALTER TABLE group_order_rewards ADD CONSTRAINT FK_40B1793E84AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('ALTER TABLE group_order_rewards ADD CONSTRAINT FK_40B1793EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F034584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_image ADD CONSTRAINT FK_64617F0393CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC0624584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_review ADD CONSTRAINT FK_1B3FC06284AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('ALTER TABLE product_review_image ADD CONSTRAINT FK_5B170A2F508E2016 FOREIGN KEY (product_review_id) REFERENCES product_review (id)');
        $this->addSql('ALTER TABLE product_review_image ADD CONSTRAINT FK_5B170A2F6DB2EB0 FOREIGN KEY (image_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE product_spec_image ADD CONSTRAINT FK_41A27BD44584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_spec_image ADD CONSTRAINT FK_41A27BD493CB796C FOREIGN KEY (file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE region ADD CONSTRAINT FK_F62F176727ACA70 FOREIGN KEY (parent_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64998260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649D526A7D3 FOREIGN KEY (parent_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_activity ADD CONSTRAINT FK_4CF9ED5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_address ADD CONSTRAINT FK_5543718B98260155 FOREIGN KEY (region_id) REFERENCES region (id)');
        $this->addSql('ALTER TABLE user_share ADD CONSTRAINT FK_DC46602A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_share ADD CONSTRAINT FK_DC46602D526A7D3 FOREIGN KEY (parent_user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F0393CB796C');
        $this->addSql('ALTER TABLE product_review_image DROP FOREIGN KEY FK_5B170A2F6DB2EB0');
        $this->addSql('ALTER TABLE product_spec_image DROP FOREIGN KEY FK_41A27BD493CB796C');
        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FFFE54D947');
        $this->addSql('ALTER TABLE group_order_rewards DROP FOREIGN KEY FK_40B1793E84AF2147');
        $this->addSql('ALTER TABLE product_review DROP FOREIGN KEY FK_1B3FC06284AF2147');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C54584665A');
        $this->addSql('ALTER TABLE product_image DROP FOREIGN KEY FK_64617F034584665A');
        $this->addSql('ALTER TABLE product_review DROP FOREIGN KEY FK_1B3FC0624584665A');
        $this->addSql('ALTER TABLE product_spec_image DROP FOREIGN KEY FK_41A27BD44584665A');
        $this->addSql('ALTER TABLE product_review_image DROP FOREIGN KEY FK_5B170A2F508E2016');
        $this->addSql('ALTER TABLE region DROP FOREIGN KEY FK_F62F176727ACA70');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64998260155');
        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718B98260155');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F36106259C97B');
        $this->addSql('ALTER TABLE `group` DROP FOREIGN KEY FK_6DC044C5A76ED395');
        $this->addSql('ALTER TABLE group_order_rewards DROP FOREIGN KEY FK_40B1793EA76ED395');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649D526A7D3');
        $this->addSql('ALTER TABLE user_activity DROP FOREIGN KEY FK_4CF9ED5AA76ED395');
        $this->addSql('ALTER TABLE user_address DROP FOREIGN KEY FK_5543718BA76ED395');
        $this->addSql('ALTER TABLE user_share DROP FOREIGN KEY FK_DC46602A76ED395');
        $this->addSql('ALTER TABLE user_share DROP FOREIGN KEY FK_DC46602D526A7D3');
        $this->addSql('ALTER TABLE group_order DROP FOREIGN KEY FK_A505B8FF52D06999');
        $this->addSql('DROP TABLE command_message');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE `group`');
        $this->addSql('DROP TABLE group_order');
        $this->addSql('DROP TABLE group_order_rewards');
        $this->addSql('DROP TABLE product');
        $this->addSql('DROP TABLE product_image');
        $this->addSql('DROP TABLE product_review');
        $this->addSql('DROP TABLE product_review_image');
        $this->addSql('DROP TABLE product_spec_image');
        $this->addSql('DROP TABLE project_meta');
        $this->addSql('DROP TABLE region');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE user_activity');
        $this->addSql('DROP TABLE user_address');
        $this->addSql('DROP TABLE user_share');
    }
}
