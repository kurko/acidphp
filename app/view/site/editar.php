
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */


$html->link( "logout", array("controller" => "site", "action" => "logout", "/arg1/arg2/arg3") );
echo "<br>";
$html->link( "Principal", array("controller" => "site", "action" => "index", "") );

//pr($user);

//pr($this->params);

?>
<h2>Editar Cadastro</h2>
<?php

echo $form->create("Usuario", array(
        "controller" => "site", "action" => "edit"
    )
);

echo $form->input('id', $id);
echo $form->input('nome', array( "label" => "Seu nome" ));
echo $form->input('email');
echo $form->input('senha');

echo $form->end("Enviar");

?>

