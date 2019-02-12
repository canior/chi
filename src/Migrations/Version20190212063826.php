<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190212063826 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('alter table product add column share_image_file_id int');
        $this->addSql('CREATE TABLE user_parent_log (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, parent_user_id INT, share_source_id int, memo text, created_at INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');
        $this->addSql('alter table upgrade_user_order add column recommander_user_id int');
        $this->addSql('alter table upgrade_user_order add column partner_user_id int');
        $this->addSql('alter table upgrade_user_order add column partner_teacher_user_id int');
    }


    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
