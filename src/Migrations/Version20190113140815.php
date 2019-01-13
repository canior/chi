<?php declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20190113140815 extends AbstractMigration
{
    public function up(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE upgrade_user_order_payment ADD COLUMN memo text');

    }

    public function down(Schema $schema) : void
    {
        $this->addSql('ALTER TABLE upgrade_user_order_payment DROP COLUMN memo');

    }
}
