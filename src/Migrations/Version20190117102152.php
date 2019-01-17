<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190117102152 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('alter table group_user_order add column group_user_order_type varchar(255) not null');
        $this->addSql('alter table group_order add column group_order_type varchar(255) not null');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
