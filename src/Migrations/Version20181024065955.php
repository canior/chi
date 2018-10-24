<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181024065955 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE user_source (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, other_user_share_id INT DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_3AD8644EA76ED395 (user_id), INDEX IDX_3AD8644EFC6C47DD (other_user_share_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_source ADD CONSTRAINT FK_3AD8644EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_source ADD CONSTRAINT FK_3AD8644EFC6C47DD FOREIGN KEY (other_user_share_id) REFERENCES user_share (id)');
        $this->addSql('ALTER TABLE user_share DROP FOREIGN KEY FK_DC46602D526A7D3');
        $this->addSql('DROP INDEX IDX_DC46602D526A7D3 ON user_share');
        $this->addSql('ALTER TABLE user_share ADD share_source VARCHAR(255) NOT NULL, ADD page VARCHAR(255) NOT NULL, DROP parent_user_id, DROP status');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE user_source');
        $this->addSql('ALTER TABLE user_share ADD parent_user_id INT DEFAULT NULL, ADD status VARCHAR(50) NOT NULL COLLATE utf8mb4_unicode_ci, DROP share_source, DROP page');
        $this->addSql('ALTER TABLE user_share ADD CONSTRAINT FK_DC46602D526A7D3 FOREIGN KEY (parent_user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_DC46602D526A7D3 ON user_share (parent_user_id)');
    }
}
