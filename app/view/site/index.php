
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if( !empty($temp) ){
    //pr($temp);
}

$html->link( "logout", array("controller" => "site", "action" => "logout", "/arg1/arg2/arg3") );
?>
<h2>Cadastro</h2>
<?php

echo $form->create("Usuario", array(
        "controller" => "site",
    )
);

echo $form->input('nome', array( "label" => "Seu nome" ));
echo $form->input('email');
echo $form->input('senha');
echo $form->input('Tarefa.nome');

echo $form->end("Enviar");

?>
<h2>Login</h2>
<?php
echo $form->create("Tarefa", array(
        "controller" => "site",
        "action" => "savetarefa"
    )
);
echo $form->input('Usuario.id', array(
        "value" => 11,
    )
);

echo $form->input('Tarefa.nome');

echo $form->end("uhuhl");

?>

