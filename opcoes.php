<?php
	$data_default = date("d/m/Y");
	if (isset($_POST['submit'])){
		
		//RETIRA O VALOR E PRONTO
		$nome  = "Retirar do Banco";
		$valor = str_replace(",",".",$_POST['valor']);
		//DATA
		$data  = $data_default = converte_data($_POST['data'],EN_D);
		if (empty($data_default) or strlen($data_default)<8) $data_default = date("d/m/Y");
		elseif (isset($_POST['submit']) and !empty($data_default)) $data_default = converte_data($data_default,BR_D);
	
		//RETIRA O VALOR
		$sql   = mysql_query("INSERT INTO pagamentos (tipo,nome,calculo,valor,data,transferencia)
							VALUES (2,'".$nome."',1,$valor,'".$data."',0)");
		if ($sql){
			$msg = "<span class='yes'>Valor Retirado do Banco!</span>";	go_home(); }
		else
			$msg = "<span class='no'>Erro ao retirar do Banco!</span>";
		
	
	}

?>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<div id="conteudo">
	<h1>Opções Avançadas</h1>
    <form name="inserir" method="post" class="formulario">
    <strong>Retirar do Banco:</strong><br />
    <table width="383" height="181" border="0" align="center" cellpadding="3" cellspacing="0" class="tabela">
      <tr>
        <td height="40" colspan="2"><?php echo $msg?></td>
      </tr>
      <tr>
        <td width="63">Data:</td>
        <td width="308"><input name="data" type="text" id="data" value="<?php echo $data_default?>" size="20" maxlength="10" class="input date" />
        <em class="min" title="Informe uma data válida no formato: dd/mm/aaaa">(dd//mm/aaaa)</em></td>
      </tr>
      <tr>
        <td>Valor:</td>
        <?php if(!$_POST['valor']) $_POST['valor'] = 00; ?>
        <td><input name="valor" type="text" id="valor" style="text-align:right; width:100px;letter-spacing:2px"
         onFocus="if(this.value==0) this.value='';" value="<?php echo$_POST['valor']?>" size="7" maxlength="7" class="input" />
        <em class="min" title="Somente Numeros, Não use virgulas nem pontos, somente valores inteiros">(somente num.)</em>
        
        <input type="hidden" name="submit" id="submit" value="true" /></td>
      </tr>
      <tr>
        <td colspan="2"><label>
          <input type="submit" name="button" id="button" value="Retirar" class="button" />
        </label></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table>
    </form>
</div>