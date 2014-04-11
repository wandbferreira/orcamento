<?php
/*
	Classes básicas para criação sistemas php
	Atualização: 22/08/2011
	1. Url Amigavel
	2. Consulta Paginada
	3. Formulário Padrão


	Legenda:
		< tipo nome > : variavel obrigatoria
		[ tipo nome ] : variavel opcional
		
*/


// 1. ******************   HABILITA O USO DE URL AMIGAVEIS   **************
//new url_amigavel(<boolean $mod_rewrite>,<string $local> , string $serv> , [string $extFinal] , [array $invalidos], [string $title] , [string $home] , [str $error])
class url_amigavel{
	var $mod_rewrite;
	var $base;
	var $nome;
	var $slogan;
	var $server;
	var $titulo;
	var $pagina;
	var $param;
	var $ext;
	var $keywords;
	var $descricao;
	var $email;
	var $automail;
	var $smtp;

	function __construct($rewrite=true, $local, $server, $ext=false, $invalid, $pre_key, $titleHome = "Home", $inicial = "home", $error = "erro"){
		//Coloca barra no final
		if (substr($local ,-1)!="/") $local  .= "/";
		if (substr($server,-1)!="/") $server .= "/";
		$this->local  = $local;
		$this->server = $server;	
		//Valida se está em outro dominio
		$sv_name = $_SERVER['SERVER_NAME'];
		if ($sv_name=="localhost") $this->server = $this->local;
		$domin = explode("/",$this->server,4);
		if ($sv_name!=$domin[2]){
			$domim = explode("/",$this->server);
			header("location:".$domin[0]."//".$domim[2].$_SERVER['REQUEST_URI']);
		}
		
		//-------------------|| URL AMIGAVEL ||--------------------------
		$url = $_GET['p'];

		//MOD_REWRITE Apache
		if (empty($rewrite)) $rewrite = "index.php/";
		if($rewrite===true) {
			$this->base = false;
			$this->mod_rewrite = true;	
		}else{
			$this->base = $rewrite;
			$this->mod_rewrite = false;
		}
		if (empty($url)){
			$url = explode($rewrite,$_SERVER['REQUEST_URI']);
			$url = $url[1];
		}
		if ($ext and $ext!="/") $url = str_replace($ext,"",$url);
		$url = explode("/",$url);
		
		//Separa os parametros
		$pagina = $url[0];
		$len = count($url);
		for($i=1;$i<=$len;$i++)
			$this->param[$i] = $url[$i];
		
		//Valida a pagina e adiciona Titulo
		$titulo = "";
		$separadores = array("-","_");
		if (empty($pagina))
			$url[0] = $pagina = $inicial;
			
		if (!file_exists($pagina.".php") or in_array($pagina,$invalid)){
			$redirect = $this->server.$this->base.$error.$ext;			
			header("location:$redirect");
		}else{
			//MONTA TITULO E PALAVRAS CHAVES
			$keys = "";
			if ($pagina==$inicial){
				$titulo = $titleHome;
			}else{
				foreach($url as $key => $param){
					if ( (!($param>0) or strlen($param)>6) and !empty($param)) {
						if($key>0) $titulo .= " - ";
						$titu = str_replace($separadores," ",$param);
						if(strpos($param,"pagina-")===false and $pagina!=$inicial)
							$keys .= $titu.",";
						$titulo .= $titu;
						$titulo = str_replace(" E "," e ",ucwords($titulo));
					}
				}
			}
		}
		//Keywords Pré-definidas
		if ($pre_key)
		foreach($pre_key as $pre)
			if ($pre[0]==$pagina){ $keys = $pre[1].","; break; }
		
		//Grava os atributos
		$this->titulo = $titulo;
		$this->pagina = $pagina.".php";
		$this->ext    = $ext;
		$this->keywords = $keys;		
		if($pagina!=$inicial) $this->descricao =  $titulo." -";
	
	}	
	//CRIA LINKS ACEITOS DE ACORDO COM UMA STRING href([string],<extensao>)
	public function href($url){
		$url = strtolower($this->base.$url.$this->ext);
		return $url;
	}
	//DEFINE ACESSO RESTRITO, restrito([array_paginas_restritas],[chave_session])
	public function restrito($restritas,$chave){
		$pagina = str_replace(".php","",$this->pagina);
		$server = $this->server;
		if(in_array($pagina,$restritas) and empty($chave))
			header("location:$server");
	}
	//MARCA O MENU JUNTO COM A CLASSE OU COM CLASSE SEPARADA
	public function link($menu){
		if ($menu.".php"==$this->pagina) echo " active ";
		echo ' " href="'.$this->base.$menu.($this->ext);
	}
	public function linkid($menu){		
		if ($menu.".php"==$this->pagina) echo 'class="active"';
		echo ' href="'.$this->base.$menu.($this->ext).'"';
	}
	public function cache($tempo = "-40d"){
		header("Expires: ".date("D, d M Y H:i:s", strtotime($tempo))." GMT");	
	}
}

//2. *******************    CONSULTA AO BANCO DE DADOS COM PAGINAÇÃO   ****************
// new paginada(<string $sqlquery> , [int $pageAtual] , [int $maxListagem])
class paginada{
	var $query;
	var $pag;
	var $qtd_pag;
	var $linha;
	var $total;
	var $inicio;
	
	function __construct($query,$pag=1,$max = 5){	
		if ($max==0) $max = 1;	
		$this->query = $query;
		//CONSULTA E CRIA VARIAVEIS	
		$conta  = preg_replace("/^(SELECT) .* FROM/i","SELECT count(*) as total FROM",$query);
		$sql    = mysql_query($conta);								
		$sql    = mysql_fetch_array($sql);
		$total  = $sql['total']; 
		$tot_pag= ceil($total/$max); //Qtd de paginas
		$this->qtd_pag = $tot_pag;				
		if ($pag < 1 or $pag > $tot_pag or empty($pag) ) $pag = 1; //Valida a pagina
		$inicio = $max * ($pag - 1); //Inicio		
		//CONSULTA LIMITANDO DE ACORDO COM A PAGINACAO
		$this->linha = mysql_query($query." LIMIT $inicio , $max ");	
		$this->pag   = $pag;
		$this->total = $total;
		$this->inicio = $inicio;
	}
	//MOSTRA O RESULTADO, uso: while($l = $noticia->resultado()) ...
	function resultado(){
		return mysql_fetch_array($this->linha);
	}
	//CRIA OS LINKS PARA PAGINACAO, uso: $obj->paginacao([string $prefixUrl],[string $sufixUrl])
	function paginacao($prefix="?pagina=",$sufix=""){		
		echo "<div class='paginacao'>";		
		for($i=1;$i<=$this->qtd_pag;$i++){
			$href = $prefix.$i.$sufix;
			echo "<a href='".$href."'";			
			if ($i==$this->pag) echo " class='active' ";
			echo ">".$i."</a> ";			
		}
		echo "</div>";
	}
}

//3. *******************    FORMULÁRIO COM VALIDAÇÃO E ENVIO POR EMAIL   ****************
// new form (<string NomeFormulario>,<string NomeDoSite>,<string UrlSite>,<array TodosOsCampos>)
class form{
	var $formulario;
	var $campos; //Array (<string Nome>, <string id> , <array mascaras>)
	var $url;
	var $smtp;
	var $assunto;
	var $emaildestino;
	var $emailenvio;
	var $redireciona;
	var $tempo = "5";
	var $banco_captcha;
	var $cap;
	
	function __construct($nome, $url, $campos){
		$this->nome = $nome;
		$this->url  = $url;
		$this->campos = $campos;
		$this->emaildestino = $GLOBALS['site']->email;
		$this->emailenvia   = $GLOBALS['site']->automail;
	}
	//--------------|| VALIDACOES ||----------
	function validacao($valores){
		$campos = $this->campos;
		//Inicia a validacao
		foreach($campos as $campo){
			$value = $valores[$campo[1]];
			$nome  = $campo[0];
			//echo $nome." = ".$value."{ <br />";
			//VERIFICA TODOS OS CAMPOS
			foreach($campo[2] as $validacao){
				//echo " &nbsp; &nbsp; - ".$validacao."<br />";				
				switch ($validacao){
				// * Campo Obrigatorio *
				case ("obrigatorio"):
					if (empty($value)) $return = "O campo $nome não foi preenchido.";
				break;
				// * Campo com pelo menos 5 Caracteres*
				case ("5char"):
					if (strlen($value)<5) $return = "O campo $nome deve ter no mínimo 5 caracteres.";
				break;
				// * Campo com pelo menos 1 Letra*/
				case ("1letra"):
					if (!preg_match("/[[:alpha:]]/",$value)) $return = "O campo $nome deve ter pelo menos uma letra.";
				break;
				// * Campo com e-mail válido*/
				case ("email"):
					if (!filter_var($value, FILTER_VALIDATE_EMAIL)) $return = "Informe um E-mail válido. (ex: nome@exemplo.com)";
				break;
				// * Campo com telefone válido*/
				case ("telefone"):
					if (strlen($value)<14) $return = "Informe um Telefone válido. (ex: (00) 0000-0000 )";
				break;
				// * Campo com data válida*/
				case ("data"):
					if (!valida_data($value)) $return = "Informe uma data válida. (ex: 20/10/2010)";
				break;
				/* Campo com data futura */
				case("data_futura"):
					if(diferenca_data($value." 00:00",date("d/m/Y H:i:s"))>0) $return = "Informe uma data futura no campo $nome.";
				break;
				// * Campo com nascimento válido*/
				case ("nascimento"):
					if (!valida_data($value,true)) $return = "Informe um nascimento válido. (ex: 20/10/1990)";
				break;
				// * Campo Classe Captcha */
				case ("captcha"):	
					$cap = $_SESSION['captcha_valor'];				
					if(strtolower($value) != strtolower($cap)) $return = 'Informe os caracteres da Imagem coretamente.';
					$_SESSION['captcha_valor'] = "";
				break;
				// * Campo apenas com letras*/
				case ("somente_letras"):
					if (ereg("[0-9]+",$value)) $return = "O campo $nome não deve conter números.";
				break;
				// * Campo apenas com números*/
				case ("somente_numeros"):
					if (!ereg("[0-9]+",$value)) $return = "O campo $nome deve conter apenas números.";
				break;
				// * Campo com CEP*/
				case ("cep"):
					if (!ereg("[0-9]{5}-[0-9]{3}",$value)) $return = "O campo $nome deve ser válido. (ex: 11000-111)";
				break;
				// * Campo com ESTADO*/
				case ("uf"):
					if (!ereg("[a-z|A-Z]{2}",$value)) $return = "O campo $nome deve conter exatamente duas letras.";
				break;
				// .... Add outros filtros aqui
				}
				if ($return) return '<span class="no">'.$return."</span>";
			}
			//echo "}<br />";			
		}
		return true;
	}
	
	function enviar($valores,$nome_envia){
		$_SESSION['captcha_valor'] = "";
		//INCLUI CLASSE PHPMAILER		
		require("includes/phpmailer/class.phpmailer.php");
		$mail = new PHPMailer();
		$mail->IsSMTP();
		$mail->Host = $this->smtp;
		$data   = date("d/m/y");
		$hora   = date("H:i");               
		$ip     = $_SERVER['REMOTE_ADDR']; 
		$host   = gethostbyaddr($_SERVER['REMOTE_ADDR']);
		$browser= $_SERVER['HTTP_USER_AGENT'];
		
		//ENVIA O FORMULARIO
		$mensagem = "<font face='Verdana' size='2' color='#222'><b>".$this->assunto."</b><br /><br />";
		
		//CAMPOS
		$len = count($this->campos);
		reset($valores);
		for($i=0;$i<$len;$i++){
			if (!in_array("nosend",$this->campos[$i][2])) //Não adiciona campos "nosend" no Email
			$mensagem .= "<b>".$this->campos[$i][0].":</b></font><span style='color:#555'> \t".current($valores)."</span><br /><br />";			
			next($valores);
		}
		
	   //ADICIONAIS
		$mensagem .= "<hr><br /><b>Data - Hora:</b><span style='color:#555'> \t$data - \t$hora</span><br />";
		$mensagem .= "<b>IP:</b><span style='color:#555'> \t$ip</span><br />";
		$mensagem .= "<b>Host:</b><span style='color:#555'> \t$host</span><br />";
		$mensagem .= "<b>Navegador:</b><span style='color:#555'> \t$browser<br />";	 
				
		//REMETENTE E DESTINATARIO
		$mail->From = $this->emailenvio;
		$mail->FromName = ucwords(utf8_decode($nome_envia));
		$mail->AddAddress($this->emaildestino, $this->nomedestino);
		$mail->AddAddress($this->emaildestino);
		
		//MENSAGEM
		$mail->Subject = utf8_decode($this->assunto);
		$mail->Body    = utf8_decode($mensagem);
		$mail->AltBody = utf8_decode($mensagem);
		
		//ENVIA O EMAIL, LIMPA INFORMACOES E REDIRECIONA
		$enviado = $mail->Send();	
		$mail->ClearAllRecipients();
		$mail->ClearAttachments();
		if ($this->redireciona and $enviado) echo "<meta http-equiv='refresh' content='".$this->tempo."; URL=".$this->redireciona."'>";
		//RETORNA A MENSAGEM
		if($enviado)
			return '<span class="yes double">Mensagem Enviada! Em breve entraremos em contato.<br />
			Aguarde o redirecionamento ou <a href="'.$this->redireciona.'" style="color:#333">clique aqui</a>.</span>';	
		else
			return '<span class="no double">Houve um erro ao enviar a Mensagem, tente novamente ou entre em contato.</span>';
	}
	
	//FUNCÃO QUE CRIA AS MESMAS VALIDAÇÕES EM JAVASCRIPT (Monta os Vetores em Javascript)
	function valida_javascript(){
		$campos = $this->campos;
		$str = "var todos_campos = Array( ";
		foreach ($campos as $i => $campo):
			if ($i > 0) $str .= " , ";
			$str .= 'Array("'.$campo[0].'" , "'.$campo[1].'" , Array( ';
			foreach( $campo[2] as $j => $valid) {if ($j > 0) $str .= ","; $str .= '"'.$valid.'"'; }
			$str .=  " ) )";
		endforeach;
		$str .= ");";
		?><script type="text/javascript"><?=$str?></script><?
	}
	//VALIDA NOVAMENTE CASO TENHA ENVIADO FORMULARIO
	function valida_novamente(){
		?> <script type="text/javascript"> valida_todos();</script><?
	}
}

?>