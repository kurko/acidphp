<?php
/**
 * Formulário deste módulo
 *
 * @package ModView
 * @name form
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.6 09/07/2009
 */
?>
<h1>Formulário</h1>
<p>-</p>

<form method="post" action="<?php echo $_SERVER['PHP_SELF']?>?<?php echo $_SERVER['QUERY_STRING'];?>&action=gravar">
<input type="hidden" name="metodo" value="<?php echo $_GET[action];?>">
<input type="hidden" name="frmadddate" value="<?php echo date("Y-m-d H:i:s"); ?>">
<input type="hidden" name="frmautor" value="<?php echo $administrador->LeRegistro('id');?>">
<input type="hidden" name="w" value="<?php ifisset($_GET['w']);?>">


<?php
/**
 * MOSTRA FORMULÁRIO DINÂMICO
 */
/**
 * Campos
 */

foreach( $camposForm as $chave=>$valor ){
    
    echo $form->input( $chave, array(
            "label" => $valor["label"],
        )
    );

}

?>


<?php
/**
 * Mostra <input> de módulos embed
 */
/**
$embed = $modulo->LeModulosEmbed();
if( count($embed) ){
    ?>
    <tr>
        <td colspan="2"><h1>Outras opções</h1></td>
    </tr>
    <?php
    foreach($embed AS $chave=>$valor){
        foreach($valor AS $chave2=>$valor2){
            if($chave2 == 'pasta'){
                if(is_file($valor2.'/embed/usuarios_form.php')){
                    include($valor2.'/embed/usuarios_form.php');
                    for($i = 0; $i < count($embed_form); $i++){
                        ?>
                        <tr>
                            <td valign="top"><label><?php echo $embed_form[$i]['propriedade']?>:</label></td>
                            <td>
                            <? if(!empty($embed_form[$i]['intro'])){ echo '<p class="explanation">'.$embed_form[$i]['intro'].'</p>'; } ?>
                            <?php echo $embed_form[$i]['input'];?>
                            <? if(!empty($embed_form[$i]['explanation'])){ echo '<p class="explanation">'.$embed_form[$i]['explanation'].'</p>'; } ?>
                            </td>
                        </tr>
                        <?
                    }
                }
            }
        }
    }
}
 * 
 */
?>

<center><INPUT TYPE="submit" VALUE="Enviar!" name="submit" class="submit"></center>


</form>
