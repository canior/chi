<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181018040833 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE group_user_order_log (id INT AUTO_INCREMENT NOT NULL, group_user_order_id INT NOT NULL, user_id INT NOT NULL, from_status VARCHAR(50) DEFAULT NULL, to_status VARCHAR(50) DEFAULT NULL, from_payment_status VARCHAR(50) DEFAULT NULL, to_payment_status VARCHAR(50) DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_5409633332FB477A (group_user_order_id), INDEX IDX_54096333A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE group_user_order_log ADD CONSTRAINT FK_5409633332FB477A FOREIGN KEY (group_user_order_id) REFERENCES group_user_order (id)');
        $this->addSql('ALTER TABLE group_user_order_log ADD CONSTRAINT FK_54096333A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user DROP INDEX UNIQ_8D93D649D526A7D3, ADD INDEX IDX_8D93D649D526A7D3 (parent_user_id)');
        $this->addSql('ALTER TABLE group_user_order ADD status VARCHAR(50) NOT NULL, ADD payment_status VARCHAR(50) NOT NULL, DROP is_delivered, CHANGE carrier_name carrier_name VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE product_review ADD status VARCHAR(50) NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE group_user_order_log');
        $this->addSql('ALTER TABLE group_user_order ADD is_delivered TINYINT(1) NOT NULL, DROP status, DROP payment_status, CHANGE carrier_name carrier_name VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE product_review DROP status');
        $this->addSql('ALTER TABLE user DROP INDEX IDX_8D93D649D526A7D3, ADD UNIQUE INDEX UNIQ_8D93D649D526A7D3 (parent_user_id)');
    }
}
