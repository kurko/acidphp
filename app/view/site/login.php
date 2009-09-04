
<?php

?>
<h2>Login</h2>
<?php
echo $form->create("Usuario", "login");

echo $form->statusMessage();

echo $form->input('email', array("value" => "123@123.com"));

echo $form->input('senha', array("value" => "123"));

echo $form->end("Login");

?>

