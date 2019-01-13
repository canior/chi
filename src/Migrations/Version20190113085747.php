<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190113085747 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('CREATE TABLE course (id INT AUTO_INCREMENT NOT NULL, product_id INT NOT NULL, subject VARCHAR(50) NOT NULL, teacher_id INT NOT NULL, start_date INT(11) NOT NULL, end_date INT(11) NOT NULL, region_id INT NOT NULL, address TEXT, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE teacher (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(50) NOT NULL, title VARCHAR (50), description TEXT, teacher_avatar_file_id INT, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
        $this->addSql('CREATE TABLE course_student (id INT AUTO_INCREMENT NOT NULL, course_id INT NOT NULL, student_user_id INT NOT NULL, status VARCHAR(50) NOT NULL, created_at INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci ENGINE = InnoDB');
    }

    public function down(Schema $schema) : void
    {
        $this->addSql('DROP TABLE course');
        $this->addSql('DROP TABLE teacher');
        $this->addSql('DROP TABLE course_student');
    }
}
