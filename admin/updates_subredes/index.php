<?
 /*
 Copyright 2000, 2001, 2002, 2003, 2004, 2005 Dataprev - Empresa de Tecnologia e Informa��es da Previd�ncia Social, Brasil

 Este arquivo � parte do programa CACIC - Configurador Autom�tico e Coletor de Informa��es Computacionais

 O CACIC � um software livre; voc� pode redistribui-lo e/ou modifica-lo dentro dos termos da Licen�a P�blica Geral GNU como
 publicada pela Funda��o do Software Livre (FSF); na vers�o 2 da Licen�a, ou (na sua opni�o) qualquer vers�o.

 Este programa � distribuido na esperan�a que possa ser  util, mas SEM NENHUMA GARANTIA; sem uma garantia implicita de ADEQUA��O a qualquer
 MERCADO ou APLICA��O EM PARTICULAR. Veja a Licen�a P�blica Geral GNU para maiores detalhes.

 Voc� deve ter recebido uma c�pia da Licen�a P�blica Geral GNU, sob o t�tulo "LICENCA.txt", junto com este programa, se n�o, escreva para a Funda��o do Software
 Livre(FSF) Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
 */
session_start();
/*
 * verifica se houve login e tamb�m regras para outras verifica��es (ex: permiss�es do usu�rio)!
 */
if(!isset($_SESSION['id_usuario'])) 
  die('Acesso negado!');
else { // Inserir regras para outras verifica��es (ex: permiss�es do usu�rio)!
}

require_once('../../include/library.php');

AntiSpy('1,2,3'); // Permitido somente a estes cs_nivel_administracao...
// 1 - Administra��o
// 2 - Gest�o Central
// 3 - Supervis�o

if ($_POST['ExecutaUpdates']=='Executar Updates')
	{				
	// Enviarei tamb�m ao updates_subredes.php uma rela��o de agentes e vers�es para inser��o na tabela redes_versoes_modulos, no caso da ocorr�ncia de Servidor de Updates verificado anteriormente.
	// Exemplo de estrutura de agentes_versoes: col_soft.exe#22010103*col_undi.exe#22010103
	$v_agentes_versoes = '';
	foreach($HTTP_POST_VARS as $i => $v) 
		{
		//echo 'v: '.$v.'   i: '.$i.'<br>';
		if ($v && substr($i,0,7)=='update_' && $v <> 'on')
			{
			// O envio de versoes_agentes.ini deve ser incondicional!
			if ($v_updates == '') $v_updates = 'versoes_agentes.ini';
			
			if ($v_updates <> '') $v_updates .= '__';
			$v_updates .= $v;		
			}
		if ($v && substr($i,0,6)=='redes_' && $v <> 'on')
			{
			if ($v_redes <> '') $v_redes .= '__';			
			$v_redes .= $v;

			if ($v_force_redes <> '') $v_force_redes .= ',';						
			$v_force_redes .= '_fr_'.$v.'_fr_';
			}			

		// Verifico se a vers�o foi FOR�ADA ao update.
		if ($v && substr($i,0,6)=='force_' && $v <> 'on')
			{
			// O envio de versoes_agentes.ini deve ser incondicional!
			if ($v_force_modulos == '') $v_force_modulos = '_fm_versoes_agentes.ini_fm_';
			
			if ($v_force_modulos <> '') 
				{
				$v_force_modulos .= ",";			
				}
			$v_force_modulos .= '_fm_'.$v.'_fm_';		
			}								

		if ($v && substr($i,0,15)=='agentes_versoes')
			{
			$v_agentes_versoes = '_-_'.$v;
			}						
		}
		
	//echo 'v_updates: '.$v_updates.'<br><br>';
	//echo 'v_redes: '.$v_redes.'<br><br>';
	//echo 'v_force_modulos: '.$v_force_modulos.'<br><br>';

	// O tratamento de v_force_modulos foi transferido para updates_subredes.php

	// O script updates_subredes.php espera receber o par�metro v_parametros contendo uma string com a seguinte forma��o:
	// objeto1__objeto2__objetoN_-_rede1__rede2__rede3__redeN  
	// Onde: __  = Separador de itens
	//       _-_ = Separador de Matrizes		
        header ("Location: updates_subredes.php?v_parametros=".$v_updates.'_-_'.$v_redes.'_-_'.$v_force_modulos.$v_agentes_versoes);			
	}
else
	{
	?>
	<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
	<html>
	<head>
<link rel="stylesheet"   type="text/css" href="../../include/cacic.css">
<title></title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<? require_once('../../include/opcoes_avancadas_combos.js');  ?>
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_reloadPage(init) {  //reloads the window if Nav4 resized
  if (init==true) with (navigator) {if ((appName=="Netscape")&&(parseInt(appVersion)==4)) {
    document.MM_pgW=innerWidth; document.MM_pgH=innerHeight; onresize=MM_reloadPage; }}
  else if (innerWidth!=document.MM_pgW || innerHeight!=document.MM_pgH) location.reload();
}
MM_reloadPage(true);
//-->
function verificar()
	{
	var formRedes = window.document.forma;
	var boolModulos = false;
	var boolRedes   = false;
	var strFraseErro = '';
	var intInicioModulos = 0;
	var intInicioRedes   = 0;

	for (j=0;j<formRedes.elements.length;j++)
		if (formRedes.elements[j].type == 'checkbox' && (formRedes.elements[j].name).substring(0,16) == 'update_subredes_')
			{
			intInicioModulos = (intInicioModulos == 0?j:intInicioModulos);
			if (formRedes[j].checked && formRedes.elements[j].value != 'versoes_agentes.ini')
				boolModulos = true;
			}

	for (j=0;j<formRedes.elements.length;j++)
		if (formRedes.elements[j].type == 'checkbox' && formRedes.elements[j].id == 'redes')
			{
			intInicioRedes = (intInicioRedes == 0?j:intInicioRedes);			
			if (formRedes[j].checked)
				boolRedes = true;
			}

	if (boolModulos && boolRedes)
		return true;
	else
		{
		if (!boolModulos)
			{
			strFraseErro = 'M�dulos';
			}

		if (!boolRedes)
			{
			strFraseErro = (!boolModulos?' e ':'') + 'SubRedes';
			}
		alert('ATEN��O: Verifique as sele��es de '+strFraseErro);	
//		formRedes.elements[min(intInicioModulos,intInicioRedes)].focus();		
		}
	return false;
	}
</script>
	</head>
	
	<body background="../../imgs/linha_v.gif">
<script language="JavaScript" type="text/javascript" src="../../include/cacic.js"></script>					
	<table width="90%" border="0" align="center">
	  <tr> 
		<td class="cabecalho">Updates de SubRedes</td>
	  </tr>
	  <tr> 
		
    <td class="descricao">As informa&ccedil;&otilde;es referem-se aos objetos 
      constantes do reposit&oacute;rio, os quais poder&atilde;o ser assinalados 
      para verifica&ccedil;&atilde;o de exist&ecirc;ncia e/ou vers&otilde;es nas 
      SubRedes cadastradas.</td>
	  </tr>
	</table>
	
	<form method="post" ENCTYPE="multipart/form-data" name="forma">
	<script>
	function MarcaIncondicional(field,p_name) 
	{
	for (i = 1; i < field.length; i++) 
		if (field[i].type == 'checkbox' && field[i].name == p_name)
			field[i].checked = true;			

	return true;
	}
	function MarcaDesmarcaTodoEsseLocal(strIdLocal) 
		{
		var Formulario = window.document.forms[0];
		var arrRede;
		for (i = 0; i < Formulario.length; i++) 
			if (Formulario[i].type == 'checkbox' && (Formulario[i].name).substring(0,6) == 'redes_')
				{
				arrRede = (Formulario[i].name).split('_');
				if (strIdLocal == arrRede[2])
					if (Formulario[i].checked)
						Formulario[i].checked = false;			
					else
						Formulario[i].checked = true;								
				}

		return true;
		}

	</script>

  <div align="center">
    <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1">
      <?
		$v_classe = "label";
		?>
      <tr> 
        <td height="20"></td>
      </tr>
	  
      <tr> 
        <td nowrap align="center" colspan="5" class="<? echo $v_classe; ?>"><br>
          Conte�do do Reposit&oacute;rio:</td>
      </tr>
      <tr> 
        <td colspan="5" height="1" bgcolor="#333333"></td>
      </tr>
      <tr> 
        <td class="destaque" align="center" colspan="3" valign="middle"><input name="update_subredes" id="update_subredes" type="checkbox" onClick="MarcaDesmarcaTodos(this.form.update_subredes),MarcaIncondicional(this.form.update_subredes,'update_subredes_versoes_agentes.ini'),MarcaIncondicional(this.form.update_subredes,'force_update_subredes_versoes_agentes.ini');">  
          &nbsp;&nbsp;Marca/Desmarca todos os objetos
	    </td>
      </tr>
      <tr> 
        <td nowrap colspan="2">&nbsp;</td>
      </tr>
      <tr> 
        <td nowrap colspan="2"><table border="1" align="center" cellpadding="2" bordercolor="#999999">
            <tr bgcolor="#FFFFCC"> 
              <td bgcolor="#EBEBEB" align="center"><img src="../../imgs/checked.gif" border="no"></td>
              <td bgcolor="#EBEBEB" class="cabecalho_tabela">Arquivo</td>
              <td bgcolor="#EBEBEB" class="cabecalho_tabela">Tamanho(KB)</td>
              <td align="center" colspan="3" nowrap bgcolor="#EBEBEB" class="cabecalho_tabela">Vers&atilde;o</td>
			  <td align="center" nowrap bgcolor="#EBEBEB" class="cabecalho_tabela">For&ccedil;ar</td>			  
            </tr>
            <? 
	  if ($handle = opendir('../../repositorio')) 
		{
		$v_nomes_arquivos = array();	

		while (false !== ($v_arquivo = readdir($handle))) 
			{
			$v_arquivo = strtolower($v_arquivo);
			if (substr($v_arquivo,0,1) != "." and 
			    $v_arquivo != "netlogon" and 
				$v_arquivo != "supergerentes" and 
				$v_arquivo != "install" and 				
				$v_arquivo != "chkcacic.exe" and
				$v_arquivo != "chkcacic.ini" and				
				$v_arquivo != "vaca.exe") // Versoes Agentes Creator/Atualizator //and 				
				//$v_arquivo != "versoes_agentes.ini") 		
				{
				// Armazeno o nome do arquivo
				array_push($v_nomes_arquivos, $v_arquivo);
				}
			}

		if (file_exists('../../repositorio/versoes_agentes.ini'))
			{
			$v_array_versoes_agentes = parse_ini_file('../../repositorio/versoes_agentes.ini');
			}

		sort($v_nomes_arquivos,SORT_STRING);
		$v_agentes_versoes = ''; // Conter� as vers�es dos agentes para tratamento em updates_subredes.php
		for ($cnt_arquivos = 0; $cnt_arquivos < count($v_nomes_arquivos); $cnt_arquivos++)
			{
			$v_dados_arquivo = lstat('../../repositorio/'.$v_nomes_arquivos[$cnt_arquivos]);
			echo '<tr>';
			echo '<td><input name="update_subredes_'.$v_nomes_arquivos[$cnt_arquivos].'" id="update_subredes" type="checkbox" class="normal" onBlur="SetaClassNormal(this);" value="'.$v_nomes_arquivos[$cnt_arquivos].'"';
			if ($v_nomes_arquivos[$cnt_arquivos] == 'versoes_agentes.ini') echo ' checked disabled'; // Implementar o OnChange para impedir o Marca/Desmarca todos para este campo...
			echo ' ></td>';
			echo '<td>'.$v_nomes_arquivos[$cnt_arquivos].'</td>';										
//			echo '<td align="right">'.number_format(($v_dados_arquivo[7]/1024), 1, '', '.').'</td>';			
			// Adequa��o ao resultado no Debian Etch
			echo '<td align="right">'.number_format(($v_dados_arquivo[7]/10240), 1, '', '.').'</td>';						
			if (isset($v_array_versoes_agentes) && $versao_agente = $v_array_versoes_agentes[$v_nomes_arquivos[$cnt_arquivos]])
				{
				echo '<td align="center" colspan="3">'.$versao_agente.'</td>';																
				}
			else
				{
				$versao_agente = strftime("%d/%m/%Y  %H:%Mh", $v_dados_arquivo[9]);
				echo '<td align="center" colspan="3">'.$versao_agente.'</td>';							
				}
			$v_agentes_versoes .= ($v_agentes_versoes<>''?'#':'');
			$v_agentes_versoes .= $v_nomes_arquivos[$cnt_arquivos].'*'.$versao_agente;	
			echo '<td align="center"><input name="force_update_subredes_'.$v_nomes_arquivos[$cnt_arquivos].'" id="force_update_subredes" type="checkbox" class="normal" onBlur="SetaClassNormal(this);" value="'.$v_nomes_arquivos[$cnt_arquivos].'"';
			if ($v_nomes_arquivos[$cnt_arquivos] == 'versoes_agentes.ini') echo ' checked disabled';
			echo '></td></tr>';											
			}
		echo '<input name="agentes_versoes" id="agentes_versoes" type="hidden" value="'.$v_agentes_versoes.'">';

		}
	 ?>
          </table></td>

      </tr>
    </table>
    <br>
  </div>
  <table width="90%" border="0" align="center" cellpadding="0" cellspacing="1">
		<?
		$v_classe = "label";
		?>
		<tr> 
		  <td nowrap align="center" colspan="4" class="<? echo $v_classe; ?>"><br>
        SubRedes Cadastradas:</td>
		</tr>
		<tr> 
		  <td colspan="4" height="1" bgcolor="#333333"></td>
		</tr>
			  <tr> 
				<td class="destaque" align="center" colspan="4" valign="middle"><input name="redes" type="checkbox" id="redes" onClick="MarcaDesmarcaTodos(this.form.redes);">
        &nbsp;&nbsp;Marca/Desmarca todas as SubRedes</td>
			  </tr>
			  <tr>
			    <td height="10" colspan="2">&nbsp;</td>
    </tr>
			  <tr>
			    <td height="10" colspan="2"></td>
    </tr>
			  <tr>
			    <td height="10" colspan="2"><table width="200" border="1" align="center">
                  <tr>
                    <td height="10" colspan="2" nowrap><div align="center"><strong>Legenda para as SubRedes</strong></div></td>
                  </tr>
                  <tr>
                    <td height="10" nowrap bordercolor="#000000" class="td_amarelo"><div align="center">Amarelo</div></td>
                    <td align="left" valign="middle" nowrap class="dado_peq_sem_fundo">Exist&ecirc;ncia de <b>M&Oacute;DULO COM VERS&Atilde;O DIFERENTE</b></td>
                  </tr>
                  <tr>
                    <td height="10" nowrap bordercolor="#000000" class="td_laranja"><div align="center">Laranja</div></td>
                    <td align="left" valign="middle" nowrap class="dado_peq_sem_fundo"><span class="opcao_tabela"><b>INEXIST&Ecirc;NCIA PARCIAL</b>  de M&oacute;dulos</span></td>
                  </tr>
                  <tr>
                    <td height="10" nowrap bordercolor="#000000" class="td_vermelho"><div align="center">Vermelho</div></td>
                    <td align="left" valign="middle" nowrap class="dado_peq_sem_fundo"><span class="opcao_tabela"><b>INEXIST&Ecirc;NCIA TOTAL</b>  de M&oacute;dulos</span></td>
                  </tr>
                </table></td>
    </tr>
			  

		<tr> 
		  <td nowrap colspan="4"><br>
		  <table border="1" align="center" cellpadding="2" bordercolor="#999999">
		    <tr bgcolor="#FFFFCC"> 
		      <td bgcolor="#EBEBEB" class="cabecalho_tabela">Seq.</td>			
				  <td bgcolor="#EBEBEB" align="center"><img src="../../imgs/checked.gif" border="no"></td>				
				  <td bgcolor="#EBEBEB" class="cabecalho_tabela">IP</td>
				  
            <td bgcolor="#EBEBEB" class="cabecalho_tabela">Nome da Subrede</td>			
				  <td bgcolor="#EBEBEB" class="cabecalho_tabela">Serv. de Updates</td>							
				  <td bgcolor="#EBEBEB" class="cabecalho_tabela">Path</td>											
				  <td colspan="2" bgcolor="#EBEBEB" class="cabecalho_tabela">Localiza��o</td>											
	        </tr>
		    
		    <? 
	   	 	  $where = ($_SESSION['cs_nivel_administracao']<>1&&$_SESSION['cs_nivel_administracao']<>2?' AND loc.id_local = '.$_SESSION['id_local']:'');
				if ($_SESSION['te_locais_secundarios']<>'' && $where <> '')
					{
					// Fa�o uma inser��o de "(" para ajuste da l�gica para consulta	
					$where = str_replace(' loc.id_local = ',' (loc.id_local = ',$where);
					$where .= ' OR loc.id_local in ('.$_SESSION['te_locais_secundarios'].')) ';
					}

 			  Conecta_bd_cacic();			  
			  
			  $query = "	SELECT 		re.id_ip_rede,
										re.nm_rede,
										loc.id_local,
										loc.sg_local,
										re.te_serv_updates,
										re.te_path_serv_updates
							FROM 		redes re,
										locais loc
							WHERE		re.id_local = loc.id_local ".
										$where ."
							GROUP BY    re.id_ip_rede
							ORDER BY	loc.sg_local,
										re.nm_rede"; 
// ********************
			  	$queryALERTA = "	SELECT 		re.id_ip_rede,
												rvm.nm_modulo,
												rvm.te_versao_modulo
									FROM 		redes re,
												redes_versoes_modulos rvm,
												locais loc
									WHERE		re.id_local = rvm.id_local and
							            		re.id_ip_rede = rvm.id_ip_rede ".
												$where ." and loc.id_local = re.id_local
									ORDER BY	re.id_ip_rede,
							            		rvm.nm_modulo"; 
			$resultALERTA = mysql_query($queryALERTA) or die('Ocorreu um erro durante a consulta � tabela de redes ou sua sess�o expirou!'); 																
			$strTripaAmarelo = '#'; // Conter� os IPS das redes cujas vers�es de m�dulos divergirem das existentes no reposit�rio
			$strTripaLaranja = '#'; // Conter� os IPS das redes cuja quantidade de m�dulos seja diferente do total de m�dulos dispon�veis
			$strTripaRedes   = '#'; // Conter� os IPS de todas as redes, para verifica��o de aus�ncia total de m�dulos			
			$intFrequenciaRede = 0; // Acumular� a frequ�ncia de cada rede e dever� ser igual ao tamanho de versoes_agentes!
			$strRedeAtual = '';

			while ($rowALERTA = mysql_fetch_array($resultALERTA))
				{
				if ($rowALERTA['nm_modulo'] <> 'chkcacic.exe' &&
				    $rowALERTA['nm_modulo'] <> 'chkcacic.ini' &&
					$rowALERTA['nm_modulo'] <> 'versoes_agentes.ini' &&
					$rowALERTA['nm_modulo'] <> 'vaca.exe' &&					
					$rowALERTA['nm_modulo'] <> 'install' &&										
					$rowALERTA['nm_modulo'] <> '' &&															
					isset($v_array_versoes_agentes) && $versao_agente = $v_array_versoes_agentes[$rowALERTA['nm_modulo']])
					{
				
					if ($strRedeAtual <> '' && $strRedeAtual <> $rowALERTA['id_ip_rede'])
						{
						if ($intFrequenciaRede <> count($v_array_versoes_agentes))
							$strTripaLaranja .= $strRedeAtual . '#';

						$intFrequenciaRede = 1;
						}
					else
						$intFrequenciaRede ++;
	
					$strRedeAtual = $rowALERTA['id_ip_rede'];					
					
					$versao_agente = str_replace('.','',$versao_agente) . '0103';
					if ($versao_agente <> $rowALERTA['te_versao_modulo'])
						{
						$strPesquisaRede = '#'.$strRedeAtual.'#';
						$intPos = stripos2($strTripaAmarelo,$strPesquisaRede);
						if ($intPos === false)
							$strTripaAmarelo .= $strRedeAtual.'#';
						}
					}
				$strPesquisaRede = '#'.$strRedeAtual.'#';
				$intPos = stripos2($strTripaRedes,$strPesquisaRede);
				if ($intPos === false)
					$strTripaRedes .= $strRedeAtual.'#';
					
				}

// ********************										
		$result_redes = mysql_query($query) or die('Ocorreu um erro durante a consulta � tabela de redes ou sua sess�o expirou!'); 										
		$intSequencial = 1;
		while ($row = mysql_fetch_array($result_redes))
			{
			$strCheck = '';
			$strPesquisaRede = '#'.$row['id_ip_rede'].'#';			
			
			$intPosVermelho  = stripos2($strTripaRedes,$strPesquisaRede);

			if (!$intPosVermelho === false)
				$strClasseTD = 'normal';			
			else
				{
				$strClasseTD = 'td_vermelho';
				$strCheck    = 'checked';
				}

			if ($strCheck == '')
				{
				$intPosLaranja = stripos2($strTripaLaranja,$strPesquisaRede);
				if ($intPosLaranja === false)
					$strClasseTD = 'normal';			
				else
					{
					$strClasseTD = 'td_laranja';
					$strCheck    = 'checked';
					}
				}
			
			if ($strCheck == '')
				{
				$intPosAmarelo = stripos2($strTripaAmarelo,$strPesquisaRede);
				if ($intPosAmarelo === false)
					$strClasseTD = 'normal';			
				else
					{
					$strClasseTD = 'td_amarelo';
					$strCheck    = 'checked';
					}
				}


			
			?>
		    <tr>
		      <td class="<? echo $strClasseTD;?>" align="right"><? echo $intSequencial;?></td>									
			  <td class="<? echo $strClasseTD;?>"><input name="redes_<? echo $row['id_ip_rede'].'_'.$row['id_local'];?>" id="redes" type="checkbox" class="normal" onBlur="SetaClassNormal(this);" value="<? echo $row['id_ip_rede'];?>" <? echo $strCheck;?>></td>
			  <td class="<? echo $strClasseTD;?>"><? echo $row['id_ip_rede'];?></td>
			  <td class="<? echo $strClasseTD;?>"><? echo $row['nm_rede'];?></td>
			  <td class="<? echo $strClasseTD;?>"><? echo $row['te_serv_updates'];?></td>
			  <td class="<? echo $strClasseTD;?>"><? echo $row['te_path_serv_updates'];?></td>
			  <td class="<? echo $strClasseTD;?>" nowrap="nowrap" 
			<? 
			if ($_SESSION['cs_nivel_administracao']<>1 && $_SESSION['cs_nivel_administracao']<>2)
				{
				?>
				colspan="2"
				<?
				}
				?>
			><? echo $row['sg_local'];?></td>
			  <? 
			if ($_SESSION['cs_nivel_administracao']==1 || $_SESSION['cs_nivel_administracao']==2)
				{			
				?>
		      <td class="<? echo $strClasseTD;?>" nowrap="nowrap"><img src="../../imgs/checked.gif" border="no"  onClick="MarcaDesmarcaTodoEsseLocal('<? echo $row['id_local']; ?>');"></td>
				  <?
				}
				?>
	        </tr>
		    <?
			$intSequencial ++;							
			}
	?> 
	      </table></td></tr>
	</table>        
	<p align="center">
	<br>
	<input name="ExecutaUpdates" type="submit" id="ExecutaUpdates" value="Executar Updates"  onClick="return (verificar() && Confirma('Confirma Verifica��o/Atualiza��o de SubRedes?'));" <? echo ($_SESSION['cs_nivel_administracao']<>1&&$_SESSION['cs_nivel_administracao']<>3?'disabled':'')?>>
	
	</p>
	</form>		  			
	</body>
	</html>
	<?
	}
	?>
