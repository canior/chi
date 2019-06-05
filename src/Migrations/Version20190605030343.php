<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190605030343 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE follow  (id INT AUTO_INCREMENT NOT NULL, type varchar(50) NULL COMMENT "关注类型（课程，讲师）",user_id int(11) NULL COMMENT "用户ID",data_id int(11) NULL COMMENT "数据ID（课程ID，讲师ID）", PRIMARY KEY (id) ) ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        
    }
}
