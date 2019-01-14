<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190114152623 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN name VARCHAR (255)');
        $this->addSql('ALTER TABLE user ADD COLUMN phone VARCHAR (255)');
        $this->addSql('ALTER TABLE user ADD COLUMN company VARCHAR (255)');
        $this->addSql('ALTER TABLE user ADD COLUMN id_num VARCHAR (255)');
        $this->addSql('ALTER TABLE user ADD COLUMN wechat VARCHAR (255)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
