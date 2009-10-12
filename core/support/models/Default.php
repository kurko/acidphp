<?php
/**
 * MODEL NAME
 *
 * Use this line to comment your Model
 *
 * @package Model
 * @name Your Model Name
 * @author Your name here <youremail@here.com>
 * @since v0.x.x xx/xx/xxxx
 */
class Usuario extends AppModel {

    /*
     * Qual a tabela que este model acessará?
     */
    public $useTable = ""; // specify one

    /*
     * VALIDATION
     */
    var $validation = array(
        "field" => array( // change "field" value to a field name you wish
            "rule" => "notEmpty",
            "message" => "nome:Este campo não pode ser vazio",
        )
    );

    /*
     * RELACIONAMENTOS
     *
     * Basta dizer abaixo os relacionamentos das tabelas e você terá todo o
     * processo de INNER JOIN automatizado. Basta fazer uma procura e serão
     * retornados todos os dados relacionados a este model.
     */
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