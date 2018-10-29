<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181029100629 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE product_similar (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, similar_product_id INT NOT NULL, created_at INT NOT NULL, INDEX IDX_1A62232D4584665A (product_id), INDEX IDX_1A62232DA295ED9C (similar_product_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE product_similar ADD CONSTRAINT FK_1A62232D4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_similar ADD CONSTRAINT FK_1A62232DA295ED9C FOREIGN KEY (similar_product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE product_statistics DROP INDEX UNIQ_71B4D4094584665A, ADD INDEX IDX_71B4D4094584665A (product_id)');
        $this->addSql('ALTER TABLE product_statistics ADD year INT NOT NULL, ADD month INT NOT NULL, ADD day INT NOT NULL');
        $this->addSql('ALTER TABLE user_statistics DROP INDEX UNIQ_45B44DCEA76ED395, ADD INDEX IDX_45B44DCEA76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_statistics ADD year INT NOT NULL, ADD month INT NOT NULL, ADD day INT NOT NULL, ADD group_order_joined_num INT NOT NULL');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE product_similar');
        $this->addSql('ALTER TABLE product_statistics DROP INDEX IDX_71B4D4094584665A, ADD UNIQUE INDEX UNIQ_71B4D4094584665A (product_id)');
        $this->addSql('ALTER TABLE product_statistics DROP year, DROP month, DROP day');
        $this->addSql('ALTER TABLE user_statistics DROP INDEX IDX_45B44DCEA76ED395, ADD UNIQUE INDEX UNIQ_45B44DCEA76ED395 (user_id)');
        $this->addSql('ALTER TABLE user_statistics DROP year, DROP month, DROP day, DROP group_order_joined_num');
    }
}
