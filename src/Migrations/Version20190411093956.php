<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190411093956 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE product ADD COLUMN aliyun_video_id text');
        $this->addSql('ALTER TABLE product ADD COLUMN aliyun_video_url text');
        $this->addSql('ALTER TABLE product ADD COLUMN aliyun_video_image_url text');
        $this->addSql('ALTER TABLE product ADD COLUMN aliyun_video_expires_at int');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
