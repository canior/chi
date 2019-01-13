<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190113113914 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user ADD COLUMN teacher_id INT, ADD COLUMN user_level VARCHAR(50), ADD COLUMN user_account_total DECIMAL(10,2), ADD COLUMN recommand_stock INT');
        $this->addSql('CREATE TABLE user_account_order (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_account_order_type VARCHAR(50), amount DECIMAL (10,2), user_upgrade_order_rewards_id INT, payment_status VARCHAR(50), created_at INT NOT NULL, updated_at INT NOT NULL , PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upgrade_user_order (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, user_level VARCHAR(50) NOT NULL, total DECIMAL (10,2), status VARCHAR (50), payment_status VARCHAR (50), created_at INT NOT NULL, updated_at INT NOT NULL , PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upgrade_user_order_payment (id INT AUTO_INCREMENT NOT NULL, upgrade_user_order_id INT NOT NULL, amount DECIMAL (10,2), created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE upgrade_user_order_rewards (id INT AUTO_INCREMENT NOT NULL, upgrade_user_order_id INT NOT NULL, recommander_user_id INT NOT NULL, teacher_user_id INT, old_teacher_user_id INT, rewards DECIMAL (10,2), created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE user DROP teacher_id, DROP user_level, DROP user_account_total, DROP recommand_stock');
        $this->addSql('DROP TABLE user_account_order');
        $this->addSql('DROP TABLE upgrade_user_order');
        $this->addSql('DROP TABLE upgrade_user_order_payment');
        $this->addSql('DROP TABLE upgrade_user_order_rewards');
    }
}
