<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181030005914 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_user_order ADD product_id INT DEFAULT NULL, CHANGE group_order_id group_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE group_user_order ADD CONSTRAINT FK_C02609724584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('CREATE INDEX IDX_C02609724584665A ON group_user_order (product_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE group_user_order DROP FOREIGN KEY FK_C02609724584665A');
        $this->addSql('DROP INDEX IDX_C02609724584665A ON group_user_order');
        $this->addSql('ALTER TABLE group_user_order DROP product_id, CHANGE group_order_id group_order_id INT NOT NULL');
    }
}
