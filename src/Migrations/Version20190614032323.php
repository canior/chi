<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190614032323 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        
        $this->addSql('ALTER TABLE message ADD data_type VARCHAR(255) DEFAULT NULL COMMENT "消息数据类型", ADD data_id VARCHAR(255) DEFAULT NULL COMMENT "数据ID", ADD expansion_data VARCHAR(1000) DEFAULT NULL COMMENT "拓展数据（JSON字符串）"');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
    }
}
