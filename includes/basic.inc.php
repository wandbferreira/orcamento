<?php
/*
	Funções Basicas para criação de sistemas PHP
	Atualização: 30/05/2011
	1. Converte Data/Hora : converte_data()
	2. String to Url      : str_url()
	3. Recorta String     : str_truncate()
	4. Diferença de Datas : diferenca_data()
	5. Valida Data        : valida_data()
	6. Escapa String      : escape()
	7. Cria Icone PNG     : icone()


	Legenda:
		< tipo nome > : variavel obrigatoria
		[ tipo nome ] : variavel opcional
		
*/

//1. *************   CONVERTE A DATA EM HORA DE ACORDO COM O PARAMETRO INFORMADO   **********
// datas(<date/datehour $date>,[constant $tipo])

define('BR_DH',1); define('BR_D',2); define('EN_DH',3); define('EN_D',4);		
function converte_data($datahora,$tipo=BR_DH){		
	if ($tipo==BR_DH or $tipo==BR_D){
		$exp  = explode(" ",$datahora);
		$data = explode("-",$exp[0]);
		$var = $data[2]."/".$data[1]."/".$data[0];
		
		if ($tipo==BR_DH){
			$hora = explode(":",$exp[1]); 				
			$var .= " ".$hora[0].":".$hora[1];
		}						
	}elseif($tipo==EN_DH or $tipo==EN_D){
		$exp  = explode(" ",$datahora);
		$data = explode("/",$exp[0]);
		$var = $data[2]."-".$data[1]."-".$data[0];
		
		if ($tipo==EN_DH){
			$hora = explode(":",$exp[1]); 
			$var .= " ".$hora[0].":".$hora[1];
		}									
	}
	return $var;
}
	
//2. *********    CONVERTE UMA STRING PARA USAR COMO LINK VALIDO   ********
// str_url(<string $str>)
function str_url($url){
	 $url = str_replace(array(" - "," -","- "," "),"-",$url);
	 $url = preg_replace("/[^a-zA-Z0-9_-]/i", "", 
	 strtr(utf8_decode($url), "áàãâéêíóôõúüçÁÀÃÂÉÊÍÓÔÕÚÜÇ", "aaaaeeiooouucAAAAEEIOOOUUC"));
	 $url = strtolower($url);
	 return $url;
}

//3. **********    RECORTA STRING NO PROXIMO ESPAÇO    *****************
// str_truncate(<string $str> , [int $tamanho] , [constant $sentido])
define("BEFORE", 0);
define("AFTER", 1);
function str_truncate($str, $length, $rep=BEFORE)
{
		//corrigir um bug
		if(strlen($str)<=$length) return $str;

		if($rep == BEFORE) $oc = strrpos(substr($str,0,$length),' ');
		if($rep == AFTER)  $oc = strpos(substr($str,$length),' ') + $length;

		$string = substr($str, 0, $oc);
		if (strlen($str)>$length) $string = $string."...";
		return $string;
}

//4. ************    RETORNA A DIFERENÇA ENTRE DUAS DATAS   ***************
// diferenca_data(<date(dd/mm/aaaa HH:MM)> , [date(dd/mm/aaaa HH:MM) , <char $unidadeResult>) ###
function diferenca_data($data1, $data2="",$tipo=""){

	if(empty($data2))
	$data2 = date("d/m/Y H:i");

	if($tipo=="")
	$tipo = "h";

	for($i=1;$i<=2;$i++){
	${"dia".$i} = substr(${"data".$i},0,2);
	${"mes".$i} = substr(${"data".$i},3,2);
	${"ano".$i} = substr(${"data".$i},6,4);
	${"horas".$i} = substr(${"data".$i},11,2);
	${"minutos".$i} = substr(${"data".$i},14,2);
	}

	$segundos = mktime($horas2,$minutos2,0,$mes2,$dia2,$ano2) - mktime($horas1,$minutos1,0,$mes1,$dia1,$ano1);
	
	switch($tipo){
	 case "m": $difere = $segundos/60;    		break;
	 case "H": $difere = $segundos/3600;    	break;
	 case "h": $difere = round($segundos/3600);	break;
	 case "D": $difere = $segundos/86400;    	break;
	 case "d": $difere = round($segundos/86400);break;
	}
	
	return $difere;

}


//5. **************    RETORNA TRUE SE A DATA FOR VALIDA   *************
//valida_data(<date(dd/mm/aaaa)> , [ boolean $nacimento ] ) ###
function valida_data($dat,$nascimento = false){
	$data = explode("/",$dat);
	$d = $data[0];
	$m = $data[1];
	$y = $data[2];
	$data = $dat." 00:00";
	$res = checkdate($m,$d,$y);

	//NASCIMENTOS INVALIDOS
	if ($res and $nascimento){
		$idade = date("Y") - $y;
		if ( (diferenca_data($data)<0) or ($idade < 0) or ($idade>200) ) $res = false;
		else $res = true;		
	}
	
	return $res;
}

//6. ***********   RETORNA A STRING COM ESCAPE   ****************
// escape(<string $str>)
function escape($b) {
    //se magic_quotes não estiver ativado: escapa a string
    if (!get_magic_quotes_gpc()) {
        return mysql_escape_string($b);
    } else { //se ativado: retorna a string normal
        return $b;
    }
}
//7. ***********   CRIA UM ICONE .PNG COM 16x16   ****************
// echo icone(<string endereco>)
function icone($img,$alt = "-"){
	return '<img src="'.$img.'.png" alt="'.$alt.'" width="16" height="16" hspace="3" border="0" align="absmiddle" />';
}
?>