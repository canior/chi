<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190219063314 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('alter table product add column share_image_file_id int');
        $this->addSql('CREATE TABLE user_parent_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_user_id INT, share_source_id int, memo text, created_at INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('alter table user_parent_log modify column share_source_id char(36)');
        $this->addSql('alter table user_parent_log modify column memo text COLLATE utf8mb4_unicode_ci');
        $this->addSql('alter table upgrade_user_order add column recommander_user_id int');
        $this->addSql('alter table upgrade_user_order add column partner_user_id int');
        $this->addSql('alter table upgrade_user_order add column partner_teacher_user_id int');
        $this->addSql('alter table product_review add column user_id int');
        $this->addSql('CREATE TABLE user_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, type varchar(50) not null, log text COLLATE utf8mb4_unicode_ci, backtrace text, created_at INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
