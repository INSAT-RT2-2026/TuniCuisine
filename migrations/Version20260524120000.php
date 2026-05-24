<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20260524120000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add users, auth, recipe moderation, favorites, ratings, comments, notifications, activity log';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('CREATE TABLE users (id INT AUTO_INCREMENT NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, display_name VARCHAR(100) NOT NULL, roles JSON NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', UNIQUE INDEX uniq_user_email (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE notifications (id INT AUTO_INCREMENT NOT NULL, type VARCHAR(50) NOT NULL, title VARCHAR(255) NOT NULL, message LONGTEXT NOT NULL, link VARCHAR(255) DEFAULT NULL, is_read TINYINT(1) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', related_recipe_id INT DEFAULT NULL, related_tip_id INT DEFAULT NULL, recipient_id INT NOT NULL, INDEX IDX_6000B0D3E92F8F78 (recipient_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE user_favorites (id INT AUTO_INCREMENT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', user_id INT NOT NULL, recipe_id INT NOT NULL, UNIQUE INDEX uniq_user_recipe_favorite (user_id, recipe_id), INDEX IDX_81378E20A76ED395 (user_id), INDEX IDX_81378E2059D8A214 (recipe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recipe_ratings (id INT AUTO_INCREMENT NOT NULL, score INT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', user_id INT NOT NULL, recipe_id INT NOT NULL, UNIQUE INDEX uniq_user_recipe_rating (user_id, recipe_id), INDEX IDX_71147813A76ED395 (user_id), INDEX IDX_7114781359D8A214 (recipe_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE recipe_comments (id INT AUTO_INCREMENT NOT NULL, content LONGTEXT NOT NULL, status VARCHAR(20) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', author_id INT NOT NULL, recipe_id INT NOT NULL, reviewed_by_id INT DEFAULT NULL, INDEX IDX_761C6D2CF675F31B (author_id), INDEX IDX_761C6D2C59D8A214 (recipe_id), INDEX IDX_761C6D2CFC6B21F1 (reviewed_by_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE admin_activity_logs (id INT AUTO_INCREMENT NOT NULL, action VARCHAR(80) NOT NULL, details LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', admin_id INT NOT NULL, INDEX IDX_4941F92C642B8210 (admin_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');

        $this->addSql('ALTER TABLE recipes ADD status VARCHAR(20) NOT NULL DEFAULT \'published\', created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT \'(DC2Type:datetime_immutable)\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rejection_reason LONGTEXT DEFAULT NULL, deleted_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', submitted_by_id INT DEFAULT NULL, reviewed_by_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B27949E04A FOREIGN KEY (submitted_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE recipes ADD CONSTRAINT FK_A369E2B2FC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('CREATE INDEX IDX_A369E2B27949E04A ON recipes (submitted_by_id)');
        $this->addSql('CREATE INDEX IDX_A369E2B2FC6B21F1 ON recipes (reviewed_by_id)');

        $this->addSql('CREATE TABLE IF NOT EXISTS community_tips (id INT AUTO_INCREMENT NOT NULL, author_name VARCHAR(120) NOT NULL, content LONGTEXT NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', status VARCHAR(20) NOT NULL DEFAULT \'published\', reviewed_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', rejection_reason LONGTEXT DEFAULT NULL, submitted_by_id INT DEFAULT NULL, reviewed_by_id INT DEFAULT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE community_tips ADD CONSTRAINT FK_TIPS_SUBMITTED FOREIGN KEY (submitted_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE community_tips ADD CONSTRAINT FK_TIPS_REVIEWED FOREIGN KEY (reviewed_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE notifications ADD CONSTRAINT FK_6000B0D3E92F8F78 FOREIGN KEY (recipient_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorites ADD CONSTRAINT FK_81378E20A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user_favorites ADD CONSTRAINT FK_81378E2059D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_ratings ADD CONSTRAINT FK_71147813A76ED395 FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_ratings ADD CONSTRAINT FK_7114781359D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_comments ADD CONSTRAINT FK_761C6D2CF675F31B FOREIGN KEY (author_id) REFERENCES users (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_comments ADD CONSTRAINT FK_761C6D2C59D8A214 FOREIGN KEY (recipe_id) REFERENCES recipes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE recipe_comments ADD CONSTRAINT FK_761C6D2CFC6B21F1 FOREIGN KEY (reviewed_by_id) REFERENCES users (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE admin_activity_logs ADD CONSTRAINT FK_4941F92C642B8210 FOREIGN KEY (admin_id) REFERENCES users (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B27949E04A');
        $this->addSql('ALTER TABLE recipes DROP FOREIGN KEY FK_A369E2B2FC6B21F1');
        $this->addSql('ALTER TABLE community_tips DROP FOREIGN KEY FK_TIPS_SUBMITTED');
        $this->addSql('ALTER TABLE community_tips DROP FOREIGN KEY FK_TIPS_REVIEWED');
        $this->addSql('ALTER TABLE recipes DROP status, DROP created_at, DROP reviewed_at, DROP rejection_reason, DROP deleted_at, DROP submitted_by_id, DROP reviewed_by_id');
        $this->addSql('ALTER TABLE community_tips DROP status, DROP reviewed_at, DROP rejection_reason, DROP submitted_by_id, DROP reviewed_by_id');
        $this->addSql('DROP TABLE admin_activity_logs');
        $this->addSql('DROP TABLE recipe_comments');
        $this->addSql('DROP TABLE recipe_ratings');
        $this->addSql('DROP TABLE user_favorites');
        $this->addSql('DROP TABLE notifications');
        $this->addSql('DROP TABLE users');
    }
}
