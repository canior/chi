<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190611040041 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE `product` DROP COLUMN `look_num`, DROP COLUMN `product_category_id`;');
        $this->addSql('ALTER TABLE `course` ADD COLUMN `look_num` int(11) NULL COMMENT \'观看次数\' AFTER `table_user_count`,ADD COLUMN `course_category_id` int(11) NULL COMMENT \'课程显示类别\' AFTER `look_num`, ADD COLUMN `course_actual_category_id` int(11) NULL COMMENT \'课程实际类别\' AFTER `course_category_id`;');
        $this->addSql('ALTER TABLE `category` ADD COLUMN `show_free_zone` tinyint(1) NOT NULL COMMENT \'是否推荐首页免费专区  0 否 1 是\' AFTER `aliyun_video_expires_at`, ADD COLUMN `show_recommend_zone` tinyint(1) NOT NULL COMMENT \'是否首页推荐专区  0 否 1 是\' AFTER `show_free_zone`, ADD COLUMN `single_course` tinyint(1) NOT NULL COMMENT \'是否为单课程 0 否 1 是\' AFTER `show_recommend_zone`, ADD COLUMN `is_deleted` tinyint(1) NOT NULL COMMENT \'是否删除 0 否 1 是\' AFTER `single_course`, ADD COLUMN `priority` int(11) NOT NULL DEFAULT 0 COMMENT \'优先级 越大越靠前\' AFTER `is_deleted`;');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
