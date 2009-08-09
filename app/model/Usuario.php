<?php
class Usuario extends AppModel {
    public $useTable = "usuarios";

    /**
     * @todo - fazendo validação
     */
    var $validation = array(
        "nome" => array(
            "rule" => "notEmpty",
            "message" => "Este campo não pode ser vazio",
        ),
        "email" => array(
            array(
                "rule" => "notEmpty",
                "m" => "Email não pode ser vazio"
            ),
            array(
                "rule" => "email",
                "m" => "digite um email válido"
            ),
        ),
        "senha" => array(
            "rule" => "notEmpty",
            "m" => "digite um email válido"
        )
    );


    var $hasMany = array(
        'Tarefa' => array(
            'foreignKey' => 'usuario_id',
        ),
    );

    var $hasOne = array(
        'Idade' => array(
            'foreignKey' => 'usuario_id',
        ),
    );


}

?>