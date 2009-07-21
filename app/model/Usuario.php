<?php
class Usuario extends AppModel {
    public $useTable = "usuarios";

    var $hasOne = array(
        'Tarefa' => array(
            'foreignKey' => 'usuario_id',
        ),
    );
}

?>