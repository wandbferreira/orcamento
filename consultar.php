<?php
//VERIFICA SE DEVE EXCLUIR
if (isset($_GET['excluir'])) {
    $id = $_GET['excluir'];
    //EXCLUI
    $sql = mysql_query("DELETE FROM pagamentos WHERE id = $id");
}


 
$dinheiro_final = 0;
$conta_final = 0;
$guardado = 0;

//DEFINE DATA ESCOLHIDA OU DATA ATUAL
$data = $_GET['data'];
$data_atual = date("m-Y");
if (empty($data))
    $data = $data_atual;

//NOME DOS MESES
$n_meses = array(01 => "Janeiro", "Fevereiro", "Março", "Abril", "Maio", "Junho", "Julho", "Agosto", "Setembro", "Outubro", "Novembro", "Dezembro");

//funcao volta/avança tempo em mes
function tempo($qtd, $data) {
    $d = explode("-", $data);
    $mes = $d[0];
    $ano = $d[1];

    //se avança
    if ($qtd > 0) {
        $mes = $mes + $qtd;
        if ($mes > 12) {
            $mes = $mes - 12;
            $ano = $ano + 1;
        } //avança ano se necessario
    } else {//se volta
        $mes = $mes + $qtd; //( está em negativo )
        if ($mes < 1) {
            $mes = $mes + 12;
            $ano = $ano - 1;
        } //volta ano se necessario
    }
    if ($mes < 10)
        $mes = "0" . $mes; //2digitos
        
//retorna resultado
    return $mes . "-" . $ano;
}

//DEFINE OS ULTIMOS 4 MESES , ATUAL , PROXIMOS 4 MESES
$c = 0;
$lista = array();
for ($i = -4; $i <= 4; $i++) {
    $c++;
    $lista[$c] = tempo($i, $data);
}
?>
<script language="javascript">
    $(document).ready(function() {
        $("#consulta_calendario").change(function() {
            window.location = "index.php?p=consulta&data=" + ($(this).attr("value"));
        });
    });
//FUNCAO PERGUNTA SE QUER EXCLUIR
    function excluir(obj) {
        //SE CLICAR SIM: CONTINUA O LINK
        if (confirm("Deseja excluir este pagamento?"))
            return true;
        //SE CLICAR EM NAO: PARA O LINK
        else
            return false;
    }
</script>
<link rel="stylesheet" type="text/css" href="estilo.css" />
<div id="conteudo">
    <h1 style="float:left; margin-left:20px;">Consultar Pagamento</h1>    
    <form class="consulta" method="post" name="consulta" id="consulta">
        <select id="consulta_calendario" onchange="consulta();">
            <?php
            //ano anterior [ano_sel = ano selecionado]
            $ano_sel = substr($data, 3, 4);
            echo "<option value='01-" . ($ano_sel - 1) . "'> " . ($ano_sel - 1) . "</option>";

            //LISTA OS MESES
            foreach ($lista as $l) {
                //nome do mes
                $m = explode("-", $l);
                $mes = $m[0];
                if ($mes < 10)
                    $mes = "0" . $mes; //coloca 0 na frente
                $mes = $n_meses[intval($mes)];
                $ano = $m[1];

                //monta option
                $option = "<option value='" . $l . "' ";
                if ($l == $data)
                    $option .= 'selected ="selected" class="selected"';
                $option .= " > " . $mes . " / " . $ano . "</option>";

                //mostra option
                echo $option;
            }
            //proximo ano
            echo "<option value='01-" . ($ano_sel + 1) . "'> " . ($ano_sel + 1) . "</option>";
            ?>
        </select>
    </form>
    <?
    //DATA SELECIONADA
    $data_sel = explode("-", $data);
    $mes = $data_sel[0];
    $ano = $data_sel[1];

    //VALORES RESTANTES DINHEIRO
    //positivo
    $res = mysql_fetch_array(mysql_query("SELECT SUM(valor) as positivo FROM pagamentos 
			WHERE data < '" . $ano . "-" . $mes . "-01' AND tipo = 0 AND calculo = 1  "));
    $restante1 = $res['positivo'];
    //negativo
    $res = mysql_fetch_array(mysql_query("SELECT SUM(valor) as negativo FROM pagamentos 
			WHERE data < '" . $ano . "-" . $mes . "-01' AND tipo = 0 AND calculo = 0  "));
    $restante1 -= $res['negativo'];


    //VALORES RESTANTES CONTA ( calcula inclusive valores Guardados )
    //positivo
    $res = mysql_fetch_array(mysql_query("SELECT SUM(valor) as positivo FROM pagamentos 
			WHERE data < '" . $ano . "-" . $mes . "-01' AND tipo > 0 AND calculo = 1   "));
    $restante2 = $res['positivo'];
    //negativo
    $res = mysql_fetch_array(mysql_query("SELECT SUM(valor) as negativo FROM pagamentos 
			WHERE data < '" . $ano . "-" . $mes . "-01' AND tipo > 0 AND calculo = 0   "));
    $restante2 -= $res['negativo'];
    ?>
    <!--   TABELA POSITIVO   -->
    <br clear="all" /><br /><br />

    <table class="tabela" width="650" height="116" border="0" cellpadding="2" cellspacing="1">
        <tr class="th">
            <td colspan="4" align="center" bgcolor="#000099"><strong>POSITIVO</strong></td>
        </tr>
        <tr class="itens">
            <td width="283">Nome</td>
            <td width="137">Tipo</td>
            <td width="107">Data</td>
            <td width="102">Valor</td>
        </tr>
        <tr>
            <td>VALOR EM DINHEIRO RESTANTE</td>
            <td>Dinheiro</td>
            <td>----------------</td>
            <td align="right">R$ <?php echo moeda($restante1); ?></td>
        </tr>
        <tr>
            <td>VALOR EM CONTA RESTANTE</td>
            <td>Conta</td>
            <td>----------------</td>
            <td align="right">R$ <?php echo moeda($restante2); ?></td>
        </tr>
        <?php
        //LISTA TODOS OS PAGAMENTOS DO MES ATUAL
        $sql = mysql_query("SELECT * FROM pagamentos WHERE MONTH(data) = '" . $mes . "' AND YEAR(data) = '" . $ano . "' AND calculo = 1 ORDER BY data ");

        //NENHUM RESULTADO
        if (mysql_num_rows($sql) == 0)
            echo '<tr><td bgcolor="#DDD" colspan="4"><em>Nenhum resultado encontrado.</em></td></tr>';

        //TIPOS
        $tipos = array("Dinheiro", "Conta", "Retirar");

        $atual_pos[0] = 0; //ATÉ a data atual (ATUAL POSITIVO DINHEIRO)
        $atual_pos[1] = 0; //ATÉ a data atual (ATUAL POSITIVO CONTA)
        while ($pag = mysql_fetch_array($sql)):
            if ($pag['data'] <= date("Y-m-d"))
                $atual_pos[$pag['tipo']] += $pag['valor']; //soma pag até a data atual
            $prev_pos[$pag['tipo']] += $pag['valor']; //previsão positivo
            //link para excluir
            $link_del = '<a title="excluir" class="del" href="?p=consulta&data=' . $data . '&excluir=' . $pag['id'] . '" onclick="return excluir(this);"></a>';
            ?>
            <tr <?php if ($pag['tipo'] == 2) echo ' class="guardar" '; if ($pag['transferencia'] == 1) echo ' class="trans" '; ?>>
                <td style="position:relative"><?php echo $pag['nome']; ?> <?php echo $link_del; ?></td>
                <td><?php echo $tipos[$pag['tipo']]; ?></td>
                <td><?php echo converte_data($pag['data'], BR_D) ?></td>
                <td align="right">R$ <?php echo moeda($pag['valor']); ?></td>
            </tr>
            <?
        endwhile;
        //SOMA TOTAL POSITIVA
        $sql = mysql_query("SELECT SUM(valor) as positivo FROM pagamentos 
			WHERE MONTH(data) = '" . $mes . "' AND YEAR(data) = '" . $ano . "' AND calculo = 1 AND transferencia = 0 AND tipo < 2");
        $val = mysql_fetch_array($sql);
        $positivo = $val['positivo'];
        ?>
        <tr class="total">
            <td colspan="3" style="color:#006">Valor Total Positivo:</td>
            <td align="right">R$ <?php echo moeda($positivo + $restante1 + $restante2) ?></td>
        </tr>
    </table>

    <!--   TABELA NEGATIVO   -->
    <br clear="all" /><br /><br />
    <table class="tabela" width="650" height="103" border="0" cellpadding="2" cellspacing="1">
        <tr class="th">
            <td colspan="4" align="center" bgcolor="#990000"><strong>NEGATIVO</strong></td>
        </tr>
        <tr class="itens">
            <td width="283">Nome</td>
            <td width="136">Tipo</td>
            <td width="108">Data</td>
            <td width="102">Valor</td>
        </tr>
        <?php
        //LISTA TODOS OS PAGAMENTOS NEGATIVOS DO MES ATUAL
        $sql = mysql_query("SELECT * FROM pagamentos WHERE MONTH(data) = '" . $mes . "' AND YEAR(data) = '" . $ano . "' AND calculo = 0 ORDER BY data ");

        //NENHUM RESULTADO
        if (mysql_num_rows($sql) == 0)
            echo '<tr><td bgcolor="#DDD" colspan="4"><em>Nenhum resultado encontrado.</em></td></tr>';

        //TIPOS
        $tipos = array("Dinheiro", "Conta", "Guardar");

        $atual_neg[0] = 0; //ATÉ a data atual (ATUAL NEGATIVO DINHEIRO)
        $atual_neg[1] = 0; //ATÉ a data atual (ATUAL NETATIVO CONTA)
        while ($pag = mysql_fetch_array($sql)):
            if ($pag['data'] <= date("Y-m-d"))
                $atual_neg[$pag['tipo']] += $pag['valor']; //soma pag até a data atual
            $prev_neg[$pag['tipo']] += $pag['valor']; //previsão negativo
            //link para excluir
            $link_del = '<a title="excluir" class="del" href="?p=consulta&data=' . $data . '&excluir=' . $pag['id'] . '" onclick="return excluir(this);"></a>';
            ?>
            <tr <?php if ($pag['tipo'] == 2) echo ' class="guardar" '; if ($pag['transferencia'] == 1) echo ' class="trans" '; ?>>
                <td height="23" style="position:relative"><?php echo $pag['nome']; ?> <?php echo $link_del; ?></td>
                <td><?php echo $tipos[$pag['tipo']]; ?></td>
                <td><?php echo converte_data($pag['data'], BR_D) ?></td>
                <td align="right">R$ <?php echo moeda($pag['valor']); ?></td>
            </tr>
            <?
        endwhile;
        //SOMA TOTAL POSITIVA
        $sql = mysql_query("SELECT SUM(valor) as negativo FROM pagamentos 
			WHERE MONTH(data) = '" . $mes . "' AND YEAR(data) = '" . $ano . "' AND calculo = 0 AND transferencia=0 AND tipo < 2");
        $val = mysql_fetch_array($sql);
        $negativo = $val['negativo'];
        ?>
        <tr class="total">
            <td height="23" colspan="3" style="color:#900">Valor Total Negativo:</td>
            <td align="right">R$ <?php echo moeda($negativo) ?></td>
        </tr>
    </table>
    <?php
    //VALORES GUARDADOS/RETIRADOS
    $cofrinho = $atual_pos[2] - $atual_neg[2];
    $prev_cofrinho = $prev_pos[2] - $prev_neg[2];


    //VALORES FINAIS = Data atual
    $dinheiro = $atual_pos[0] - $atual_neg[0] + $restante1;
    $conta = $atual_pos[1] - $atual_neg[1] + $restante2 + $cofrinho;

    //VALORES FINAIS = Previsao Final do Mes
    $prev_dinheiro = moeda($prev_pos[0] - $prev_neg[0] + $restante1);
    $prev_conta = moeda($prev_pos[1] - $prev_neg[1] + $restante2 + $prev_cofrinho);


    //Define sinal subtração soma para cofrinho  //SINAL COFRE ; SINAL PREV. COFRE
    if ($cofrinho < 0)
        $sc = "-"; //inverte para positivo
    else
        $sc = "+";
    if ($prev_cofrinho < 0)
        $spc = "-"; //inverte para positivo
    else
        $spc = "+";
    ?>
    <br clear="all" />
    <table width="286" height="69" border="0" cellpadding="1" cellspacing="1" class="tabela valores_finais">
        <tr>
            <td width="282" height="33" colspan="2">
                <img src="img/icon-dinheiro.png" width="32" height="31" hspace="7" align="absmiddle" class="icon" />
                <i <?php red($dinheiro); ?>    >R$ <?php echo moeda($dinheiro); ?></i> 
                <b <?php red($prev_dinheiro); ?>>R$ <?php echo $prev_dinheiro; ?>#</b>
            </td>
        </tr>
        <tr>
            <td height="33" colspan="2">
                <img src="img/icon-conta.png" alt="" width="32" height="31" hspace="7" align="absmiddle" class="icon" />
                <i <?php red($conta); ?>    >R$ <?php echo moeda($conta); ?></i>
                <b <?php red($prev_conta); ?>>R$ <?php echo $prev_conta; ?>#</b>
            </td>
        </tr>
    </table>
    <div id="total">
        <span>VALOR TOTAL:</span><br />
<?
//VALORES TOTAIS
$total = ($atual_pos[0] + $atual_pos[1] - $atual_neg[0] - $atual_neg[1] + $restante1 + $restante2 + $cofrinho);
$prev_total = ($positivo + $restante1 + $restante2) - $negativo + $prev_cofrinho;
?>
        <i <?php red($total); ?> title="<?= moeda($atual_pos[0] + $atual_pos[1] + $restante1 + $restante2) ?> - <?= moeda($atual_neg[0] + $atual_neg[1]); ?> <?= $sc ?> <?= moeda(abs($cofrinho)) ?>*">
            R$ <?php echo moeda($total); ?></i>
        <b <?php red($prev_total); ?> title="<?php echo moeda($positivo + $restante1 + $restante2) ?> - <?php echo moeda($negativo) ?> <?= $spc ?> <?= moeda(abs($prev_cofrinho)) ?>*">
            R$ <?php echo moeda($prev_total); ?>#</b>
    </div>
    <br clear="all" />
    <br />
    <em class="obs">
        # = Previsão para o final do mês;<br />
        *  = Valor Guardado/Retirado do Banco;<br />
        OBS: Valores de Guardar/Retirar/Saque/Deposito não serão somados aos 'totais positivos' e 'totais negativos';</em>

    <br clear="all" />
    <br /><br /><br />







</div>

