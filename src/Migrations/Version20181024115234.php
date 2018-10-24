<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20181024115234 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user_source DROP FOREIGN KEY FK_3AD8644EFC6C47DD');
        $this->addSql('CREATE TABLE share_source (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, product_id INT DEFAULT NULL, banner_file_id INT NOT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, page VARCHAR(255) NOT NULL, created_at INT NOT NULL, INDEX IDX_81B88F5AA76ED395 (user_id), INDEX IDX_81B88F5A4584665A (product_id), INDEX IDX_81B88F5AC79650AF (banner_file_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE share_source_user (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, share_source_id INT NOT NULL, created_at INT NOT NULL, INDEX IDX_FEFAD1F7A76ED395 (user_id), INDEX IDX_FEFAD1F77ECBD1ED (share_source_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE share_source ADD CONSTRAINT FK_81B88F5AA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE share_source ADD CONSTRAINT FK_81B88F5A4584665A FOREIGN KEY (product_id) REFERENCES product (id)');
        $this->addSql('ALTER TABLE share_source ADD CONSTRAINT FK_81B88F5AC79650AF FOREIGN KEY (banner_file_id) REFERENCES file (id)');
        $this->addSql('ALTER TABLE share_source_user ADD CONSTRAINT FK_FEFAD1F7A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE share_source_user ADD CONSTRAINT FK_FEFAD1F77ECBD1ED FOREIGN KEY (share_source_id) REFERENCES share_source (id)');
        $this->addSql('DROP TABLE user_share');
        $this->addSql('DROP TABLE user_source');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE share_source_user DROP FOREIGN KEY FK_FEFAD1F77ECBD1ED');
        $this->addSql('CREATE TABLE user_share (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, created_at INT NOT NULL, share_source VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, page VARCHAR(255) NOT NULL COLLATE utf8mb4_unicode_ci, INDEX IDX_DC46602A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user_source (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, other_user_share_id INT DEFAULT NULL, created_at INT NOT NULL, INDEX IDX_3AD8644EA76ED395 (user_id), INDEX IDX_3AD8644EFC6C47DD (other_user_share_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE user_share ADD CONSTRAINT FK_DC46602A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_source ADD CONSTRAINT FK_3AD8644EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE user_source ADD CONSTRAINT FK_3AD8644EFC6C47DD FOREIGN KEY (other_user_share_id) REFERENCES user_share (id)');
        $this->addSql('DROP TABLE share_source');
        $this->addSql('DROP TABLE share_source_user');
    }
}
