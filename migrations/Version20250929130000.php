<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Platforms\PostgreSQLPlatform;
use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

final class Version20250929130000 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create job_applications table';
    }

    public function up(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('
            CREATE TABLE job_applications (
                id VARCHAR(36) NOT NULL PRIMARY KEY,
                full_name VARCHAR(255) NOT NULL,
                email VARCHAR(255) NOT NULL,
                phone VARCHAR(50) NOT NULL,
                position VARCHAR(255) NOT NULL,
                notes TEXT DEFAULT NULL,
                cv_text TEXT NOT NULL,
                status VARCHAR(20) NOT NULL,
                applied_at TIMESTAMP WITHOUT TIME ZONE NOT NULL
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->abortIf(
            !$this->connection->getDatabasePlatform() instanceof PostgreSQLPlatform,
            'Migration can only be executed safely on PostgreSQL.'
        );

        $this->addSql('DROP TABLE job_applications');
    }
}
