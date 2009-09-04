<?php
class Usuario extends AppModel {
    public $useTable = "usuarios";

    /**
     * @todo - fazendo validação
     */
    var $validation = array(
        "nome" => array(
            "rule" => "notEmpty",
            "message" => "nome:Este campo não pode ser vazio",
        ),

        "email" => array(
            array(
                "rule" => "notEmpty",
                "m" => "email:Email não pode ser vazio"
            ),
            array(
                "rule" => array(
                    "max" => "50",
                ),
                "m" => "email:max 50"
            ),
            array(
                "rule" => array(
                    "min" => "1",
                ),
                "m" => "email:min 1"
            ),
        ),
        "senha" => array(
            "rule" => "cpf",
            "m" => "senha:cpf"
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