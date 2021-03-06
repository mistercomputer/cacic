<?php

namespace Cacic\CommonBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 *
 * Repositório de métodos de consulta em DQL
 * @author lightbase
 *
 */
class SoRepository extends EntityRepository
{
    public function paginar( \Knp\Component\Pager\Paginator $paginator, $page = 1 )
    {
        $_dql = "SELECT s
				FROM CacicCommonBundle:So s
				ORDER BY s.teDescSo";

        return $paginator->paginate(
            $this->getEntityManager()->createQuery( $_dql ),
            $page,
            10
        );
    }

    /**
     *
     * Método de listagem dos Tipo de Software cadastrados e respectivas informações
     */
    public function listar()
    {
        $_dql = "SELECT s
				FROM CacicCommonBundle:So s
				ORDER BY s.teDescSo";

        return $this->getEntityManager()->createQuery( $_dql )->getArrayResult();
    }

    public function createIfNotExist( $te_so )
    {
        $qb = $this->createQueryBuilder('so')
            ->orWhere('teso.teSo28 = :te_so')
            ->orWhere('teso.teSo31 = :te_so')
            ->innerJoin('CacicCommonBundle:TeSo', 'teso', 'WITH', 'so.idSo = teso.idSo')
            ->setMaxResults(1)
            ->orderBy('teso.teSo28')
            ->setParameter('te_so', $te_so);

        $so =  $qb->getQuery()->getOneOrNullResult();

        if (empty($so)){
            $so = $this->findOneBy( array ( 'teSo' => $te_so ) );
        }

        if( empty( $so ) )
        {
            $so = new So();
            $so->setTeDescSo("$te_so");
            $so->setSgSo("Sigla a Cadastrar");
            $so->setTeSo($te_so);
            $so->setInMswindows("S");

            $this->getEntityManager()->persist( $so );
            $this->getEntityManager()->flush();
        }

        return $so;
    }

    /*
    * Listar so para carga no SGConf_PGFN
    */
    public function soSGConf (){

        $_dql = "SELECT s.idSo, s.teDescSo
                 FROM CacicCommonBundle:So s
                 GROUP BY s";

        return $this->getEntityManager()->createQuery( $_dql )->getArrayResult();
    }

    public function listarSo($idSo = null) {

        $qb = $this->createQueryBuilder('so')
            ->select('rede.idRede',
                'rede.nmRede',
                'rede.teIpRede',
                'loc.idLocal',
                'loc.nmLocal',
                'so.idSo',
                'so.teDescSo',
                'count(DISTINCT comp) as numComp')
            ->innerJoin('CacicCommonBundle:Computador', 'comp', 'WITH', 'comp.idSo = so.idSo')
            ->innerJoin("CacicCommonBundle:Rede", 'rede', 'WITH', 'comp.idRede = rede.idRede')
            ->innerJoin("CacicCommonBundle:Local", 'loc', 'WITH', 'rede.idLocal = loc.idLocal')
            ->andWhere("comp.ativo is null or comp.ativo = 't'")
            ->groupBy('rede.idRede', 'rede.nmRede', 'rede.teIpRede', 'loc.idLocal', 'loc.nmLocal', 'so.idSo', 'so.teDescSo');

        if (!empty($idSo)) {
            $locais = implode(', ', $idSo);
            $qb->andWhere("so.idSo in ($locais)");
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function listarSoCsv($idSo = null) {

        $qb = $this->createQueryBuilder('so')
            ->select('so.teDescSo',
                'loc.nmLocal',
                'rede.nmRede',
                'rede.teIpRede',
                'count(DISTINCT comp) as numComp')
            ->innerJoin('CacicCommonBundle:Computador', 'comp', 'WITH', 'comp.idSo = so.idSo')
            ->innerJoin("CacicCommonBundle:Rede", 'rede', 'WITH', 'comp.idRede = rede.idRede')
            ->innerJoin("CacicCommonBundle:Local", 'loc', 'WITH', 'rede.idLocal = loc.idLocal')
            ->andWhere("comp.ativo is null or comp.ativo = 't'")
            ->groupBy('so.teDescSo',
                'loc.nmLocal',
                'rede.nmRede',
                'rede.teIpRede');

        if (!empty($idSo)) {
            $locais = implode(', ', $idSo);
            $qb->andWhere("so.idSo in ($locais)");
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function detalhar($idSo = array(), $idRede = null, $idLocal = null) {

        $qb = $this->createQueryBuilder('so')
            ->select('comp.idComputador',
                'comp.nmComputador',
                'comp.teNodeAddress',
                'comp.teIpComputador',
                'comp.dtHrUltAcesso',
                'rede.idRede',
                'rede.nmRede',
                'rede.teIpRede',
                'loc.idLocal',
                'loc.nmLocal',
                'so.idSo',
                'so.teDescSo')
            ->innerJoin('CacicCommonBundle:Computador', 'comp', 'WITH', 'comp.idSo = so.idSo')
            ->innerJoin("CacicCommonBundle:Rede", 'rede', 'WITH', 'comp.idRede = rede.idRede')
            ->innerJoin("CacicCommonBundle:Local", 'loc', 'WITH', 'rede.idLocal = loc.idLocal')
            ->andWhere("comp.ativo is null or comp.ativo = 't'");

        if (!empty($idSo)) {
            $locais = implode(', ', $idSo);
            $qb->andWhere("so.idSo in ($locais)");
        }

        if (!empty($idRede)) {
            $qb->andWhere("rede.idRede = :idRede")
                ->setParameter('idRede', $idRede);
        }

        if (!empty($idLocal)) {
            $qb->andWhere('loc.idLocal = :idLocal')
                ->setParameter('idLocal', $idLocal);
        }

        return $qb->getQuery()->getArrayResult();
    }

    public function detalharCsv($idSo = array(), $idRede = null, $idLocal = null) {

        $qb = $this->createQueryBuilder('so')
            ->select('comp.nmComputador',
                'comp.teNodeAddress',
                'comp.teIpComputador',
                'so.teDescSo',
                'loc.nmLocal',
                'rede.nmRede',
                'rede.teIpRede',
                'comp.dtHrUltAcesso')
            ->innerJoin('CacicCommonBundle:Computador', 'comp', 'WITH', 'comp.idSo = so.idSo')
            ->innerJoin("CacicCommonBundle:Rede", 'rede', 'WITH', 'comp.idRede = rede.idRede')
            ->innerJoin("CacicCommonBundle:Local", 'loc', 'WITH', 'rede.idLocal = loc.idLocal')
            ->andWhere("comp.ativo is null or comp.ativo = 't'");

        if (!empty($idSo)) {
            $locais = implode(', ', $idSo);
            $qb->andWhere("so.idSo in ($locais)");
        }

        if (!empty($idRede)) {
            $qb->andWhere("rede.idRede = :idRede")
                ->setParameter('idRede', $idRede);
        }

        if (!empty($idLocal)) {
            $qb->andWhere('loc.idLocal = :idLocal')
                ->setParameter('idLocal', $idLocal);
        }

        return $qb->getQuery()->getArrayResult();
    }

}
