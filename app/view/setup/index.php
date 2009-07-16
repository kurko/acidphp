<?php
/**
 * Controller principal deste m�dulo
 *
 * @package SetupController
 * @name nome
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.6 06/07/2009
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
<input type="hidden" name="setupAction" value="criarcampos" />

<p>Aqui n�s configuraremos esta estrutura.</p>
<div class="campo">
    <label>Quantos campos ter� seu cadastro?</label>
    <div class="input">
    <select name="qtd_campos" style="width: 70px;">
        <?php
        // cria um select com 20 n�meros
        for($i = 1; $i <= 40; $i++){
        ?>
            <option value="<?php echo $i;?>"><?php echo $i;?></option>
        <?php
        }
        ?>
    </select>
    </div>
</div>
<div class="campo">
    <label>Ser� necess�rio aprova��o para completar cadastro?</label>
    <div class="input">
        <input type="radio" name="aprovacao" value="1" /> Sim, ser� necess�ria aprova��o de um administrador ap�s cadastro<br />
        <input type="radio" checked="checked" name="aprovacao" value="0" /> N�o, qualquer usu�rio poder� se cadastrar
    </div>
</div>
<div class="campo">
    <label>Se for necess�rio que o usu�rio digite uma senha para poder se cadastrar, especifique:</label>
    <div class="input">
    <input type="text" name="pre_senha" value="" />
    </div>
</div>
<div class="campo">
    <label>Par�grafo introdut�rio ao formul�rio:</label>
    <div class="input">
    <textarea name="descricao" cols="50" rows="3"></textarea>
    </div>
</div>
<div class="campo">
    <input type="submit" value="Enviar!" class="submit" />
</div>

</form>