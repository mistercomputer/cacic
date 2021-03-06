<?php

namespace Cacic\CommonBundle\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20140424182701 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != "postgresql", "Migration can only be executed safely on 'postgresql'.");
        $sm = $this->connection->getSchemaManager();

        $logger = $this->container->get('logger');
        $rootDir = $this->container->get('kernel')->getRootDir();
        $upgrade1 = $rootDir."/../src/Cacic/CommonBundle/Resources/data/upgrade-3.0b4-3.sql";
        $upgradeSQL1 = file_get_contents($upgrade1);

        // Altera o modelo de dados
        $columns = $sm->listTableColumns('computador_coleta');
        if (!array_key_exists('dt_hr_inclusao', $columns)) {
            $logger->debug("Adicionando coluna dt_hr_inclusao na tabela computador_coleta");
            $this->addSql("ALTER TABLE computador_coleta ADD dt_hr_inclusao TIMESTAMP(0) WITHOUT TIME ZONE");
        }

        $logger->debug("Arquivo de atualização: $upgrade1");

        // Chama o container para executar o arquivo de atualização
        // FIXME: Só funciona no PostgreSQL
        $this->addSql($upgradeSQL1);
        $this->addSql("SELECT upgrade()");

        // Impede que o campo seja nulO
        $this->addSql("ALTER TABLE computador_coleta ALTER COLUMN dt_hr_inclusao SET NOT NULL");

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
