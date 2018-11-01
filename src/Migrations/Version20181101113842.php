<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181101113842 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD column captain_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product ADD column joiner_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product ADD column parent_rewards decimal(10,2) NOT NULL default 0 ');
        $this->addSql('ALTER TABLE product ADD column regular_rewards decimal(10,2) NOT NULL default 0 ');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
