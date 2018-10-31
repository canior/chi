<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181031091614 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user ADD pending_total_rewards NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE share_source ADD group_order_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE share_source ADD CONSTRAINT FK_81B88F5A84AF2147 FOREIGN KEY (group_order_id) REFERENCES group_order (id)');
        $this->addSql('CREATE INDEX IDX_81B88F5A84AF2147 ON share_source (group_order_id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE share_source DROP FOREIGN KEY FK_81B88F5A84AF2147');
        $this->addSql('DROP INDEX IDX_81B88F5A84AF2147 ON share_source');
        $this->addSql('ALTER TABLE share_source DROP group_order_id');
        $this->addSql('ALTER TABLE user DROP pending_total_rewards');
    }
}
