<?php
class Tarefa extends AppModel {
    
    public $useTable = "tarefas";

    var $validation = array(
        "nome" => array(
            "rule" => "notEmpty",
            "m" => "Nome tarefa não pode ser vaziaa"
        )
    );

    var $belongsTo = array(
        "Usuario" => array(
            "foreignKey" => "usuario_id"
        ),
    );

    
}

?>