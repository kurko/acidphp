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
     * VALIDAÇÃO
     *
     * É extremamente cansativo ter de verificar cada campo de um formulário.
     * Bem, o Acid faz isto para você automaticamente. Ele não deixará dados
     * incorretos serem cadastros e voltará a página para o formulário, até que
     * o usuário digite algo corretamente. Basta configurar as
     * validações.
     *
     * O código a seguir é um exemplo de como deve-se configurar a validação de
     * dados.
     */
    var $validation = array(
        "nome" => array( // nome do campo da tabela usuarios
            "rule" => "notEmpty",
            "message" => "nome:Este campo não pode ser vazio",
        ),

        "email" => array(
            array(
                "rule" => "email",
                "m" => "Email não pode ser vazio"
            ),
            array(
                "rule" => array(
                    "max" => "50",
                ),
                "m" => "max 50"
            ),
            array(
                "rule" => array(
                    "min" => "1",
                ),
                "m" => "min 1"
            ),
        ),
        "senha" => array(
            "rule" => "alphaNumeric",
            "m" => "Digite uma senha"
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