<?php
/* 
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

if( !empty($temp) ){
    //pr($temp);
}

echo $form->create("Usuario", array(
        "controller" => "site",
    )
);
echo $form->input('nome', array(
                            "after" => "Teste do after",
                            "before" => "Teste do before",
                            "between" => "Meu between"
                        ));
echo $form->input('biografia');
echo $form->input('checkbox', array(
                            "label" => "Você deseja marcar o checkbox?",
                            "value" => "1"
                        ));

echo $form->input('Tarefa.nome');
echo $form->input('Tarefa.descricao');

echo $form->end("uhuhl");



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

