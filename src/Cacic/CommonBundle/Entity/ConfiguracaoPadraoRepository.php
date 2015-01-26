<?php

namespace Cacic\CommonBundle\Entity;

use Doctrine\ORM\EntityRepository;

/**
 * ConfiguracaoPadraoRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ConfiguracaoPadraoRepository extends EntityRepository
{

	/**
	 * 
	 * Método de listagem das configurações padrão salvas no sistema
	 */
	public function listar()
	{
		$_dql = "SELECT cp
				FROM CacicCommonBundle:ConfiguracaoPadrao cp";
		
        $query = $this->getEntityManager()->createQuery( $_dql );
        return $query->getArrayResult();
	}
	
	/**
	 * 
	 * Recupera um array na forma [idConfiguracao] => [vlConfiguracao]
	 */
	public function getArrayChaveValor()
	{
		$configuracoes = $this->listar();
		$return = array();
		
		foreach ( $configuracoes as $config )
		{
			$return[ $config['idConfiguracao'] ] = $config[ 'vlConfiguracao' ];
		}
		
		return $return;
	}
	
}
