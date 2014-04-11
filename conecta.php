<?php
	//Connect with database
	$local = "localhost";
	$user  = "root";
	$pass  = "wcorp@mysql";
	$db    = "w_orcamento";
	$conexao = mysql_connect($local,$user,$pass) or die("Erro de conexao");
	$banco   = mysql_select_db($db) or die("Erro com o banco");
	

?>