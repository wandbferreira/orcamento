<?php

	//REALIZA SAQUE OU DEPOSITO
	$nome_trans = array("Saque / Retirar Conta","Depósito / Adicionar na Conta");
	if (isset($_POST['submit'])):
		
		//PEGA OS DADOS
		$trans  = $_POST['trans']; //Transferencia
		$valor = str_replace(",",".",$_POST['valor']);
		$data  = $data_default = converte_data($_POST['data'],EN_D);
		
		//SAQUE
		if ($trans==0){
			$tipo = 1;
			$cond = " OR tipo ='2' ";
		}
		else{//DEPOSITO
			$tipo = 0;
		}
		
		if (!empty($valor) and !empty($data)){
			//-----CALCULA VALOR POSITIVO----

			$sql  = mysql_query("SELECT SUM(valor) as positivo FROM pagamentos WHERE data <= '".$data."' AND calculo = 1 AND tipo='".$tipo."' ");
			$val  = mysql_fetch_array($sql);
			$pos  = $val['positivo'];

			//-----CALCULA VALOR NEGATIVO----
			$sql  = mysql_query("SELECT SUM(valor) as negativo FROM pagamentos WHERE data <= '".$data."' AND calculo = 0 
			AND (tipo='".$tipo."' $cond ) "); //Conta os valores guardados cofrinho
			$val  = mysql_fetch_array($sql);
			$neg  = $val['negativo'];
			$disponivel = $pos-$neg;
			
			
			//VALIDA DISPONIVEL SOMENTE SE CALC FOR NEGATIVO
			if ($disponivel >= $valor){
							
				//Retira de um
				$sql = mysql_query("INSERT INTO pagamentos (nome,tipo,calculo,valor,data,transferencia)
								VALUES ('".$nome_trans[$trans]."',".$tipo.",0,".$valor.",'".$data."',1)");
				//Coloca no outro
				$sql = mysql_query("INSERT INTO pagamentos (nome,tipo,calculo,valor,data,transferencia)
								VALUES ('".$nome_trans[$trans]."',".$trans.",1,".$valor.",'".$data."',1)");
				
				$msg = "<span class='yes'>Transferência executada com sucesso!</span>";
			
			
			}else{
				//Valor indisponivel;
				$msg = "<span class='no'>Valor indisponível para esta Data ( Disponivel: R$ ".moeda($disponivel).")</span>";
			}
			
		
		
		}

	endif;
	
	//DATA DEFAULT
	if (empty($data_default) or strlen($data_default)<8) $data_default = date("d/m/Y");
	elseif (isset($_POST['submit']) and !empty($data_default)) $data_default = converte_data($data_default,BR_D);
?>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<div id="conteudo">
	<h1>Saque / Depósito</h1>
    <form name="inserir" method="post" class="formulario">
    <table width="383" height="181" border="0" align="center" cellpadding="3" cellspacing="0" class="tabela">
      <tr>
        <td height="40" colspan="2"><?php echo$msg?></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td width="63">Data:</td>
        <td width="308"><input name="data" type="text" id="data" value="<?php echo $data_default?>" size="20" maxlength="10" class="input date" />
        <em class="min" title="Informe uma data válida no formato: dd/mm/aaaa">(dd//mm/aaaa)</em></td>
      </tr>
      <tr>
        <td>Tipo:</td>
        <td>
        <select name="trans" class="input">
        	<option value="0" <?php if($_POST['trans']==0) echo "selected";?> >Saque</option>
            <option value="1" <?php if($_POST['trans']==1) echo "selected";?> >Depósito</option>
        </select>        </td>
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
        <td>&nbsp;</td>
      </tr>
      <tr>
        <td colspan="2"><label>
          <input type="submit" name="button" id="button" value="Executar" class="button" />
        </label></td>
      </tr>
      <tr>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
      </tr>
    </table>
    </form>
</div>