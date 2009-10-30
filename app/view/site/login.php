<?php
echo $form->create("Usuario", array(
        "controller" => "site",
        "action" => "index",
    ));

echo $form->statusMessage();

echo $form->input("email");
echo $form->input("senha");
echo $form->end("Enviar");

?>