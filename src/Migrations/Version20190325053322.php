<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190325053322 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD COLUMN partner_teacher_recommander_user_id int');
        $this->addSql('ALTER TABLE user ADD COLUMN bianxian_user_level varchar(50)');
        $this->addSql('ALTER TABLE course ADD COLUMN owner_user_id int');
        $this->addSql('ALTER TABLE course ADD COLUMN is_online tinyint not null default 1');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
