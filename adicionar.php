<?php
	$nome_tipo = array("Dinheiro","Conta");

	//CADASTRA PAGAMENTO
	if (isset($_POST['submit']))
	{
		$nome  = $_POST['nome'];
		$mes   = $_POST['mes'];	
		$calc  = $_POST['calculo'];
		$tipo  = $_POST['tipo'];
		$valor = str_replace(",",".",$_POST['valor']);
		$data  = $data_default = converte_data($_POST['data'],EN_D);
		$guardar= $_POST['guardar'];
		$banco = 0;		
		
		//VERIFICA SE VAI GUARDAR 
		if ($guardar==true){
			$nome = "Guardar no Banco";
			$calc = 0;
			$tipo = 1;
			$guardar_tipo = 1;
		}
		//VALIDA CAMPOS		
		if(!empty($nome) and !empty($data) and !empty($valor))
		{
			//-----CALCULA VALOR DISPONIVEL----
			$hoje = date("Y-m-d");
			
			
			$sql  = mysql_query("SELECT SUM(valor) as positivo FROM pagamentos WHERE data <= '".$data."' AND calculo = 1 AND tipo='".$tipo."' ");
			$val  = mysql_fetch_array($sql);
			$pos  = $val['positivo'];

			//-----CALCULA VALOR INDISPONIVEL----
			if ($tipo==1) $cond = " OR tipo = 2 ";
			$sql  = mysql_query("SELECT SUM(valor) as negativo FROM pagamentos WHERE data <= '".$data."' AND calculo = 0 
			AND (tipo='".$tipo."' $cond ) ");//Conta os valores guardados cofrinho
			$val  = mysql_fetch_array($sql);
			$neg  = $val['negativo'];
			$disponivel = $pos-$neg;
				
			//VALIDA DISPONIVEL SOMENTE SE CALC FOR NEGATIVO
			if ($disponivel >= $valor or $calc==1)
			{
				$tipo += $guardar_tipo; //adiciona +1 caso seja guardar no banco
				$sql = mysql_query("INSERT INTO pagamentos(tipo,nome,calculo,data,valor)
									VALUES ('$tipo','$nome','$calc','$data','$valor')");
				if($sql){ $msg = "<span class='yes'>Pagamento '$nome' inserido com sucesso!</span>"; go_home();	}
				else $msg = "<span class='no'>Erro ao inserir pagamento!</span>";
			}
			//NAO DISPONIVEL
			else
			{
				$msg = "<span class='no'>Valor Indisponível!
				(".$nome_tipo[$tipo]." : R$ ".number_format($disponivel,2,","," ")." )</span>";
			}
		}
		else
		{
			$msg = "<span class='no'>Preencha todos os campos corretamente!</span>";
		}
	}
	
	//DATA DEFAULT
	if (empty($data_default) or strlen($data_default)<8) $data_default = date("d/m/Y");
	elseif (isset($_POST['submit']) and !empty($data_default)) $data_default = converte_data($data_default,BR_D);
 
?>
<script type="text/javascript">
	function marc(){
		//VERIFICA SE VAI GUARDAR		
		if (document.inserir.guardar.checked == true){			
			document.inserir.nome.value    = "Guardar no Banco";
			document.inserir.nome.disabled = true;
			document.inserir.calculo.value = 0;
			document.inserir.calculo.disabled = true;
			document.inserir.tipo.value = 1;
			document.inserir.tipo.disabled = true;
		}
		else{
			if (document.inserir.nome.value=="Guardar no Banco")
				document.inserir.nome.value = "";
			document.inserir.nome.disabled = false;
			document.inserir.calculo.disabled = false;
			document.inserir.tipo.disabled = false;
		}			
	
	}
	$(document).ready(function(){
		marc();
	})
</script>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<div id="conteudo">
	<h1>Adicionar Pagamento</h1>
    <form name="inserir" method="post" class="formulario">
    <table width="383" height="181" border="0" align="center" cellpadding="3" cellspacing="0" class="tabela">
      <tr>
        <td height="40" colspan="2"><?php echo$msg?></td>
      </tr>
      <tr>
        <td>Nome:</td>
        <td>
          <input name="nome" type="text" id="nome" value="<?php echo$_POST['nome']?>" size="30" maxlength="40" class="input" />
        </td>
      </tr>
      <tr>
        <td width="63">Data:</td>
        <td width="308"><input name="data" type="text" id="data" value="<?php echo $data_default?>" size="20" maxlength="10" class="input date" />
        <em class="min" title="Informe uma data válida no formato: dd/mm/aaaa">(dd//mm/aaaa)</em></td>
      </tr>
      <tr>
        <td>Calculo:</td>
        <td>
        <select name="calculo" class="input">
        	<option value="0" <?php if($_POST['calculo']==0) echo "selected";?> >Negativo</option>
            <option value="1" <?php if($_POST['calculo']==1) echo "selected";?> >Positivo</option>
        </select>        </td>
      </tr>
      <tr>
        <td>Tipo:</td>
        <td><select name="tipo" class="input">
          <option value="0" <?php if($_POST['tipo']==0) echo "selected";?>>Dinheiro</option>
          <option value="1" <?php if($_POST['tipo']==1) echo "selected";?>>Conta</option>         
        </select></td>
      </tr>
      <tr>
        <td>Valor:</td>
        <?php if(!$_POST['valor']) $_POST['valor'] = 00; ?>
        <td><input name="valor" type="text" id="valor" style="text-align:right; width:100px;letter-spacing:2px"
         onFocus="if(this.value==0) this.value='';" value="<?php echo$_POST['valor']?>" size="7" maxlength="7" class="input" />
        <em class="min" title="Somente Numeros, Não use virgulas nem pontos, somente valores inteiros">(somente num.)</em></td>
      </tr>
      <tr>
        <td><input name="submit" type="hidden" id="submit" value="true"></td>
        <td>Guardar no banco
          <label>
          <input <?php if($_POST['guardar']==true) echo " checked ";?> title="Marque este campo para travar o dinheiro no banco" 
          name="guardar" type="checkbox" id="guardar" value="true" onchange="marc();" />
        </label></td>
      </tr>
      <tr>
        <td colspan="2"><label>
          <input type="submit" name="button" id="button" value="Inserir" class="button" />
        </label></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table>
    </form>
</div>