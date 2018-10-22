<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181022083538 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_statistics (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, reviews_num INT NOT NULL, sold_total NUMERIC(10, 2) NOT NULL, UNIQUE INDEX UNIQ_71B4D4094584665A (product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_statistics (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, children_num INT NOT NULL, shared_num INT NOT NULL, group_order_num INT NOT NULL, group_user_order_num INT NOT NULL, spent_total NUMERIC(10, 2) NOT NULL, UNIQUE INDEX UNIQ_45B44DCEA76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_statistics ADD CONSTRAINT FK_71B4D4094584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE user_statistics ADD CONSTRAINT FK_45B44DCEA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_statistics');
        $this->addSql('DROP TABLE user_statistics');
    }
}
