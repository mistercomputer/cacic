<?php

namespace Cacic\CommonBundle\Migrations;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20141218072911 extends AbstractMigration implements ContainerAwareInterface
{
    private $container;

    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $em = $this->container->get('doctrine.orm.entity_manager');
        $logger = $this->container->get('logger');

        $cocar = $this->container->get('kernel')->getBundle('CocarBundle');

        if (empty($cocar)) {
            $logger->info("O Cocar não está instalado. Não é necessário executar o migration");
            return;
        }

        # Backup de todos os contadores
        $this->addSql("create table tb_printer_counter_bak as
                       select p.id as printer_id,
                              p.name,
                              p.description,
                              p.communitysnmpprinter,
                              p.host,
                              p.serie,
                              p.local,
                              c.date,
                              c.prints,
                              c.id as counter_id
                       from tb_printer p
                       inner join tb_printer_counter c on c.printer_id = p.id");

        # Remove todas as impressoras cujo serial for nulo
        $printer_list = $em->getRepository('CocarBundle:Printer')->findBy(array(
           'serie' => null
        ));

        foreach ($printer_list as $printer) {
            $logger->info("Removendo impressora ".$printer->getHost());

            $counter_list = $em->getRepository('CocarBundle:PrinterCounter')->findBy(array(
               'printer' => $printer->getId()
            ));

            foreach($counter_list as $counter) {
                $em->remove($counter);
            }

            $em->remove($printer);
        }

        $em->flush();

    }

    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
