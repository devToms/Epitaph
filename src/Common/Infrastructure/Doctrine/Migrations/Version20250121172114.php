<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250121172114 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        // Drop foreign key
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        
        // Update the column
        $this->addSql('ALTER TABLE product CHANGE id id INT AUTO_INCREMENT NOT NULL');
        
        // Restore foreign key
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product(id)');
    }

    public function down(Schema $schema): void
    {
        // Reverse changes
        $this->addSql('ALTER TABLE cart_item DROP FOREIGN KEY FK_F0FE25274584665A');
        $this->addSql('ALTER TABLE product CHANGE id id INT NOT NULL');
        $this->addSql('ALTER TABLE cart_item ADD CONSTRAINT FK_F0FE25274584665A FOREIGN KEY (product_id) REFERENCES product(id)');
    }

}
