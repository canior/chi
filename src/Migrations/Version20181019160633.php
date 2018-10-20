<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181019160633 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('insert into project_meta (meta_key, meta_value, memo) values ("home_banner_1", "home_banner_1" ,"主页banner1")');
        $this->addSql('insert into project_meta (meta_key, meta_value, memo) values ("home_banner_2", "home_banner_2" ,"主页banner2")');
        $this->addSql('insert into project_meta (meta_key, meta_value, memo) values ("home_banner_3", "home_banner_3" ,"主页banner3")');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('delete from project_meta where meta_key in ("home_banner_1", "home_banner_2", "home_banner_3")');
    }
}
