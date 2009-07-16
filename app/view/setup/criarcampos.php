<?php
/**
 * Descri��o deste arquivo
 *
 * @package nome do pacote ou grupo de arquivos
 * @name nome
 * @author nome do autor <email>
 * @since v1.6 25/06/2009
 */
?>

<h1>Setup de cadastro: configura��o inicial</h1>
<form action="" method="post" class="normal">

<?php
/**
 * Escreve cada exPOST
 */
foreach($exPOST as $chave=>$valor){
    echo '<input type="hidden" name="'.$chave.'" value="'.$valor.'" />';
}
?>

    <input type="hidden" name="setupAction" value="setuppronto" />

	<p>Configurando esta estrutura...</p>
	<p>Seu cadastro ter� <?php echo '<strong>'.$_POST['qtd_campos'].'</strong>'; if($_POST['qtd_campos'] == 1) echo ' campo'; else echo ' campos';?>.
	Preencha abaixo informa��es sobre cada campo.</p>
    <h2>Cadastro "<?php echo $_SESSION['exPOST']['nome']?>"</h2>

	<table width="99%" border="0" class="listagem">
	<col width="15">
	<col width="160">
	<col width="400">
	<col>
	<tr class="titulo">
            <td></td>
            <td valign="top">Nome do campo</td>
            <td valign="top">Tipo de campo</td>
            <td valign="top">Descri��o<br /><span style="font-weight: normal">Servir� de ajuda aos usu�rios</span></td>
	</tr>
	<?php for ($i = 1; $i <= $_SESSION['exPOST']['qtd_campos']; $i++){ ?>
	<tr class="conteudo">
            <td align="center" style="font-weight: bold;" valign="top">
            <?php echo $i;?>
            </td>
            <td valign="top">
                    <input type="text" name="campo[]" />
            </td>
            <td valign="top">
                <select name="campo_tipo[]" onchange="javascript: SetupCampoRelacionalTabelas(this, '<?php echo 'campooption'.$i?>', '<?php echo $i?>')">
                    <option value="varchar(200)">Texto pequeno (ex: nome, idade)</option>
                    <option value="text">Texto m�dio ou grande (ex: descri��o, biografia)</option>
                    <option value="date">Data (ex: data de nascimento)</option>
                    <option value="pw">Senha</option>
                    <option value="arquivo">Arquivo</option>
                    <option value="relacional_umparaum">Relacional Um-para-um (tabela)</option>
                </select>
            <div class="campooptions" id="<?php echo 'campooption'.$i?>">
                <?
                /*
                 * Se <select campo_tipo> for relacional, ent�o cria dois campos <select>
                 *
                 * -<select relacionado_tabela_<n> onde n � igual a $i (sequencia num�rica dos campos)
                 * -<select relacionado_campo_<n> onde n � igual a $i (sequencia num�rica dos campos)
                 */
                ?>
                <div class="campooptions_tabela" id="<?php echo 'campooption'.$i; ?>_tabela"></div>
                <div class="campooptions_campo" id="<?php echo 'campooption'.$i; ?>_campo"></div>
            </div>

            </td>
            <td valign="top">
                <input type="text" name="campo_descricao[]" />
            </td>
	</tr>
	<?php } ?>
	</table>

	<input type='submit' value="Enviar!" name='setup_ready' />

</form>