<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181026091448 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_statistics ADD order_num INT NOT NULL, ADD buyers_num INT NOT NULL, ADD return_users_num INT NOT NULL, ADD return_users_rate NUMERIC(10, 2) NOT NULL, CHANGE sold_total order_amount_total NUMERIC(10, 2) NOT NULL');
        $this->addSql('ALTER TABLE share_source CHANGE id id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE share_source_user CHANGE share_source_id share_source_id CHAR(36) NOT NULL COMMENT \'(DC2Type:guid)\'');
        $this->addSql('ALTER TABLE share_source_user ADD CONSTRAINT FK_FEFAD1F77ECBD1ED FOREIGN KEY (share_source_id) REFERENCES share_source (id)');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE product_statistics ADD sold_total NUMERIC(10, 2) NOT NULL, DROP order_num, DROP order_amount_total, DROP buyers_num, DROP return_users_num, DROP return_users_rate');
        $this->addSql('ALTER TABLE share_source CHANGE id id CHAR(13) NOT NULL COLLATE utf8mb4_unicode_ci');
        $this->addSql('ALTER TABLE share_source_user DROP FOREIGN KEY FK_FEFAD1F77ECBD1ED');
        $this->addSql('ALTER TABLE share_source_user CHANGE share_source_id share_source_id INT NOT NULL');
    }
}
