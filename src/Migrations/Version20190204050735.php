<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190204050735 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('alter table product add column video_file_id int');
        $this->addSql('alter table product add column total_group_user_order_required int');
        $this->addSql('alter table product add column supplier_price decimal(10,2)');
        $this->addSql('alter table product add column supplier_user_id int');

        $this->addSql('alter table upgrade_user_order add column group_user_order_id int');

        $this->addSql('alter table group_order add column total_group_user_order_required int');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
