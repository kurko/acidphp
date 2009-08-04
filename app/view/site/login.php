
<?php

?>
<h2>Login</h2>
<?php
echo $form->create("Usuario");

echo $form->input('email', array("value" => "123@123.com"));

echo $form->input('senha',array("value" => "123"));

echo $form->end("Login");

?>

