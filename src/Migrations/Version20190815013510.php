<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190815013510 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE course ADD check_status VARCHAR(50) DEFAULT NULL  COMMENT "审核状态"');
        $this->addSql('ALTER TABLE course ADD check_at INT DEFAULT NULL  COMMENT "审核时间"');
        $this->addSql('ALTER TABLE course ADD reason VARCHAR(255) DEFAULT NULL  COMMENT "理由"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        
    }
}
