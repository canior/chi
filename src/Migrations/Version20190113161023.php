<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190113161023 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('DROP TABLE upgrade_user_order_rewards');
        $this->addSql('ALTER TABLE user_account_order DROP COLUMN user_upgrade_order_rewards_id');
        $this->addSql('ALTER TABLE user_account_order ADD COLUMN user_upgrade_order_id INT');
    }

    public function down(Schema $schema) : void
    {


    }
}
