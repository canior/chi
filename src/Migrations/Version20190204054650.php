<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190204054650 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('alter table product drop column video_file_id');
        $this->addSql('CREATE TABLE product_video (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, video_file_id INT NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id)) ENGINE = InnoDB');

    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
