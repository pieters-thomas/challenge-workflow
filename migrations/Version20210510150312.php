<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210510150312 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE comment (id INT AUTO_INCREMENT NOT NULL, ticket_id INT NOT NULL, user_id INT NOT NULL, private TINYINT(1) NOT NULL, content LONGTEXT DEFAULT NULL, INDEX IDX_9474526C5774FDDC (ticket_id), INDEX IDX_9474526C9D86650F (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE ticket (id INT AUTO_INCREMENT NOT NULL, ticket_owner_id INT NOT NULL, assigned_agent_id INT DEFAULT NULL, closed_by_id INT DEFAULT NULL, status SMALLINT NOT NULL, opened DATETIME NOT NULL, closed DATETIME DEFAULT NULL, priority SMALLINT NOT NULL, level SMALLINT NOT NULL, INDEX IDX_97A0ADA3FB559D78 (ticket_owner_id), INDEX IDX_97A0ADA349197702 (assigned_agent_id), INDEX IDX_97A0ADA3E1FA7797 (closed_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `user` (id INT AUTO_INCREMENT NOT NULL, agents_id INT DEFAULT NULL, email VARCHAR(180) NOT NULL, roles JSON NOT NULL, password VARCHAR(255) NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, joined_date DATE NOT NULL, agent_level SMALLINT DEFAULT NULL, closed_tickets SMALLINT DEFAULT NULL, reopened_tickets SMALLINT DEFAULT NULL, escalated_tickets SMALLINT DEFAULT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D649709770DC (agents_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C5774FDDC FOREIGN KEY (ticket_id) REFERENCES ticket (id)');
        $this->addSql('ALTER TABLE comment ADD CONSTRAINT FK_9474526C9D86650F FOREIGN KEY (user_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3FB559D78 FOREIGN KEY (ticket_owner_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA349197702 FOREIGN KEY (assigned_agent_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE ticket ADD CONSTRAINT FK_97A0ADA3E1FA7797 FOREIGN KEY (closed_by_id) REFERENCES `user` (id)');
        $this->addSql('ALTER TABLE `user` ADD CONSTRAINT FK_8D93D649709770DC FOREIGN KEY (agents_id) REFERENCES `user` (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C5774FDDC');
        $this->addSql('ALTER TABLE comment DROP FOREIGN KEY FK_9474526C9D86650F');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3FB559D78');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA349197702');
        $this->addSql('ALTER TABLE ticket DROP FOREIGN KEY FK_97A0ADA3E1FA7797');
        $this->addSql('ALTER TABLE `user` DROP FOREIGN KEY FK_8D93D649709770DC');
        $this->addSql('DROP TABLE comment');
        $this->addSql('DROP TABLE ticket');
        $this->addSql('DROP TABLE `user`');
    }
}
