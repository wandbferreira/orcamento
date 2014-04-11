<?php
	include("conecta.php");
	include("includes/basic.inc.php");
	
	// **********|   SISTEMA DE OR«AMENTO PESSOAL   |**********
	//by Wanderson Ferreira - wandersonbferreira
        //O sistema È simples, na Època eu nem sequer sabia sobre a exitencia de PHP Orientado a Objetos.
        //O sistema falta muitas funÁıes b·sicas, mas d· pra utilizar, eu mesmo utilizo. xD
	//v0.2 - 07/02/2012 - [Gerenciar Pagamentos] 
        //v1.0 - 15/03/2012 - [Saques] [Consultar Pagamento] [Dinheiro no Porquinho]
	// ********************************************************


	$versao = "1.0";
	$pagina = $_GET['p'];
	$opcao  = $_GET['op'];
	$id     = $_GET['id'];
	
	if(empty($pagina) or !file_exists($pagina.".php")) $pagina = "consultar";
	$title = str_replace("-"," ",$pagina);
	$title = ucfirst($title);
	
	//REDIRECIONA PARA HOME
	function go_home($tempo = 3){
		//echo '<meta http-equiv="refresh" content="'.$tempo.'; url=index.php">';
	}
	
	//FUNCAO FORMATA PARA MOEDA/DINHEIRO
	function moeda($valor){
		return number_format($valor,2,","," ");
	}


	//FUN√á√ÉO QUE MARCA DE VERMELHO CASO ESTEJA NEGATIVO
	function red($valor){
		if ($valor < 0) echo ' class="red" ';
	}
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta http-equiv="content-language" content="pt-br" />
<meta name="description" content="" />
<meta name="keywords" content="" />
<title>.:: OrPess 2012 - <?php echo $title; ?> ::.</title>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<link rel="shortcut icon" type="image/x-icon" href="favicon.ico" />
<script type="text/javascript" src="includes/jquery-1.5.1.min.js"></script>
</head>

<body>
<div id="all">
	<div id="topo"></div>
    <div id="meio">
    	<div id="esq">
            <div id="logo"><a href="index.php"><img src="img/logo.png" width="235" height="80" alt="OrPess 2012" border="0" /></a></div>
            <ul id="menu">
            	<li class="title"><img src="img/menu-title.png" width="96" height="35" alt="Menu" /></li>
            	<li><a href="?p=adicionar"><img src="img/menu1.png" width="16" height="16" alt="" border="0" />Adicionar Pagamento</a></li>
                <li><a href="?p=consultar"><img src="img/menu2.png" width="16" height="16" alt="" border="0" />Consultar Pagamento</a></li>
                <li><a href="?p=saque"    ><img src="img/menu3.png" width="16" height="16" alt="" border="0" />Saque e Dep√≥sito</a></li>
                <li><a href="?p=opcoes"   ><img src="img/menu4.png" width="16" height="16" alt="" border="0" />Op√ß√µes Avan√ßadas</a></li>
            </ul>
            <div id="cofrinho">
            <?php
				//CALCULA VALOR GUARDADO AT√â HOJE
				$hoje = date("Y-m-d");
				$sql  = mysql_query("SELECT SUM(valor) as guardado FROM pagamentos WHERE tipo = 2 AND calculo = 0 AND data <= '".$hoje."'");
				$tot  = mysql_fetch_array($sql);
				$porquinho = $tot['guardado'];

				$sql  = mysql_query("SELECT SUM(valor) as retirado FROM pagamentos WHERE tipo = 2 AND calculo = 1 AND data <= '".$hoje."'");
				$tot  = mysql_fetch_array($sql);
				$porquinho -= $tot['retirado'];
				
				//MOSTRA VALOR GUARDADO
			?>
            	<b>R$ <?php echo moeda($porquinho);?></b>
            </div>
		</div>        
        <?php
        	//INCLUI O CONTEUDO
			include($pagina.".php");			
		?>
    <br clear="all" />    
    </div>
    <div id="base">Copyright 2012 ¬© Or√ßamento Pessoal  v<?=$versao?> - Todos os direitos reservados</div>
	<br />
	<div style="clear:both"></div>
</div>
</body>
</html>