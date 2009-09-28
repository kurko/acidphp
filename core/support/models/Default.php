<?php
/**
 * USUARIO
 *
 * Esta classe é um exemplo de um Model do Acid.
 *
 * Basicamente, configuramos qual é a tabela do banco de dados que este model
 * vai cuidar, configuramos validações e também relacionamentos entre tabelas.
 */
class Usuario extends AppModel {

    /*
     * Qual a tabela que este model acessará?
     */
    public $useTable = "usuarios";

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