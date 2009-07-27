<?php
class Tarefa extends AppModel {
    
    public $useTable = "tarefas";

    var $belongsTo = array(
        "Usuario" => array(
            "foreignKey" => "usuario_id"
        ),
    );

    
}

?>