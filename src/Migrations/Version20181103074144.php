<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181103074144 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product CHANGE captain_rewards group_order_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product CHANGE joiner_rewards group_order_user_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product CHANGE parent_rewards regular_order_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product CHANGE regular_rewards regular_order_user_rewards decimal(10,2) NOT NULL default 0 ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
