<?
session_start();
//Mostrar computadores com nomes repetidos na base
require_once('../../include/library.php');
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<title>Relat&oacute;rio de Softwares Inventariados por M&aacute;quinas</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<script language="JavaScript" type="text/JavaScript">
<!--
function MM_openBrWindow(theURL,winName,features) { //v2.0
  window.open(theURL,winName,features);
}
//-->
</script>
<link href="../../include/cacic.css" rel="stylesheet" type="text/css">
</head>

<body bgcolor="#FFFFFF" topmargin="5">
<table border="0" align="left" cellpadding="0" cellspacing="0" bordercolor="#999999">
  <tr bgcolor="#E1E1E1"> 
    <td rowspan="5" bgcolor="#FFFFFF"><img src="../../imgs/cacic_novo.gif" width="50" height="50"></td>
    <td rowspan="5" bgcolor="#FFFFFF">&nbsp;</td>
    <td bgcolor="#FFFFFF">&nbsp;</td>
  </tr>
  <tr bgcolor="#E1E1E1"> 
    <td nowrap bgcolor="#FFFFFF"><font color="#333333" size="4" face="Verdana, Arial, Helvetica, sans-serif"><strong>CACIC 
      - Relat&oacute;rio de M&aacute;quinas com Invent&aacute;rio Desatualizado</strong></font></td>
  </tr>
  <tr> 
    <td height="1" bgcolor="#333333"></td>
  </tr>
  <tr> 
    <td><p align="left"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Gerado 
        em <? echo date("d/m/Y �\s H:i"); ?></font></p></td>
  </tr>
</table>
<br>
<br>
<br>
<br>
<?
conecta_bd_cacic();
$linha = '<tr bgcolor="#e7e7e7"> 
			  <td height="1"></td>
			  <td height="1"></td>
         </tr>';
?>
<?
	 $query = "SELECT a.te_nome_computador as nm_maquina, a.te_node_address, a.id_so, a.te_ip, a.dt_hr_ult_acesso, te_cpu_desc  
		FROM computadores a 
		WHERE (a.dt_hr_ult_acesso < DATE_SUB(now(), INTERVAL 8 DAY))
		ORDER BY a.dt_hr_ult_acesso, te_cpu_desc, a.te_nome_computador"; 
	$result = mysql_query($query) or die('Erro no acesso � tabela "computadores" ou sua sess�o expirou!');
?>
<table border="0" align="center" cellpadding="0" cellspacing="1">
  <tr> 
    <td align="center" nowrap></td>
  </tr>
  <tr> 
    <td height="1" bgcolor="#333333"></td>
  </tr>
  <tr> 
    <td> <table border="0" cellpadding="5" cellspacing="0" bordercolor="#333333" align="center">
        <tr bgcolor="#E1E1E1"> 
          <td nowrap class="cabecalho_tabela" align="center">&nbsp;&nbsp;</td>
          <td nowrap class="cabecalho_tabela" align="center"></td>
          <td nowrap class="cabecalho_tabela" align="center">&nbsp;&nbsp;</td>
          <td nowrap class="cabecalho_tabela" align="center" bgcolor="#E1E1E1"><div align="center">Nome da M&aacute;quina</div></td>
          <td nowrap class="cabecalho_tabela" >&nbsp;&nbsp;</td>
		  <td nowrap class="cabecalho_tabela"><div align="center">IP</div></td>
	  	  <td nowrap class="cabecalho_tabela" >&nbsp;&nbsp;</td>
	  	  <td nowrap class="cabecalho_tabela"><div align="center">&Uacute;ltima Coleta</div></td>
	  	  <td nowrap class="cabecalho_tabela" >&nbsp;&nbsp;</td>
	  	  <td nowrap class="cabecalho_tabela" ><div align="center">CPU</div></td>
	  	  <td nowrap class="cabecalho_tabela" >&nbsp;&nbsp;</td>
        </tr>
        <?  
	$Cor = 0;
	$NumRegistro = 1;
	
	while($row = mysql_fetch_array($result)) {
		  
	 ?>
        <tr <? if ($Cor) { echo 'bgcolor="#E1E1E1"'; } ?>> 
          <td nowrap>&nbsp;&nbsp;</td>
          <td class="dado_med_sem_fundo" nowrap><div align="left"><? echo $NumRegistro; ?></div></td>
          <td class="dado_med_sem_fundo" nowrap>&nbsp;&nbsp;</td>
          <td class="dado_med_sem_fundo" nowrap><div align="left"><a href="../../../relatorios/computador/computador.php?te_node_address=<? echo $row['te_node_address'];?>&id_so=<? echo $row['id_so'];?>" target="_blank"><? echo $row['nm_maquina']; ?></div></td>
          <td class="dado_med_sem_fundo" nowrap>&nbsp;&nbsp;</td>
	  	  <td class="dado_med_sem_fundo" align="center" nowrap><? echo $row['te_ip']; ?></td>
	  	  <td class="dado_med_sem_fundo" nowrap>&nbsp;&nbsp;</td>
	  	  <td class="dado_med_sem_fundo" align="center" nowrap><? echo date("d/m/Y H:i", strtotime($row['dt_hr_ult_acesso'])); ?></td>
	  	  <td class="dado_med_sem_fundo" nowrap>&nbsp;&nbsp;</td>
	  	  <td class="dado_med_sem_fundo" align="center" wrap><? echo $row['te_cpu_desc']; ?></td>
	  	  <td class="dado_med_sem_fundo" nowrap>&nbsp;&nbsp;</td>
          <? 
	$Cor=!$Cor;
	$NumRegistro++;
	}
?>
      </table></td>
  </tr>
  <tr> 
    <td height="1" bgcolor="#333333"></td>
  </tr>
  <tr> 
    <td height="10"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Clique 
      sobre o nome da m&aacute;quina para ver os detalhes</font> </td>
  </tr>
</table>
<p align="center"><font size="1" face="Verdana, Arial, Helvetica, sans-serif">Relat&oacute;rio 
  gerado pelo <strong>CACIC</strong> - Configurador Autom&aacute;tico e Coletor 
  de Informa&ccedil;&otilde;es Computacionais</font><br>
  <font size="1" face="Verdana, Arial, Helvetica, sans-serif">Software desenvolvido 
  pela Dataprev - Escrit&oacute;rio do Esp&iacute;rito Santo</font></p>	

</body>
</html>
