<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190610021738 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        $this->addSql('ALTER TABLE course ADD table_count INT DEFAULT NULL COMMENT "桌数", ADD table_user_count INT DEFAULT NULL COMMENT "每桌人数"');
        
    }

    public function down(Schema $schema) : void
    {
    }
}
