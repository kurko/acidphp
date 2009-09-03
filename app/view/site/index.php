
<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if( !empty($temp) ){
    //pr($temp);
}

echo $html->link( "logout", array("controller" => "site", "action" => "logout", "/arg1/arg2/arg3") );
echo "<br>";
echo $html->link( "Listar usuários", array("controller" => "site", "action" => "listar", "") );

//pr($user);


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
//echo $form->input('Idade.titulo');

echo $form->end("Enviar");

?>
<h2>Cadastro Idade</h2>
<?php

echo $form->create("Usuario", array(
        "controller" => "site",
        "class" => "teste",
        "edit" => true
    )
);

echo $form->input('id', array("value" => 2, "show" => true) );
echo $form->input('nome');
echo $form->input('Idade.titulo');

echo $form->end("Enviar");

?>

<h2>Login</h2>
<?php
echo $form->create("login");
echo $form->input('Usuario.id', array(
        "value" => 11,
    )
);

echo $form->input('Tarefa.nome');

echo $form->end("uhuhl");

?>

