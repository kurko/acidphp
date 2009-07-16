<?php
/**
 *
 * @package Classes
 * @name SQLObject
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.2
 * @since v0.1.5, 30/05/2009
 */
class SQLObject {

    /**
     *
     * @var class Classe respons�vel pela conex�o com o banco de dados
     */
    protected $conexao;

    function __construct(){
        //$this->conexao = $conexaoClass;
    }

    function find($options, $modo = 'all'){
        /**
         * Configura��es gerais
         *
         * Ajusta os par�metros passados em vari�veis espec�ficas
         */
        /**
         * $fields -> Campos que devem ser carregados
         */
        $fields = (empty($options['fields'])) ? '*' : ( (is_array($options['fields'])) ? implode(',', $options['fields']) : $options['fields'] );
        /**
         * $table -> Tabela a ser usada
         */
        $table = (empty($options['table'])) ? '' : $options['table'];
        /**
         * $join -> LEFT JOIN, RIGHT JOIN, INNER JOIN, etc
         */
        $join = (empty($options['join'])) ? '' : ( (is_array($options['join'])) ? implode(' ', $options['join']) : $options['join'] );
        /**
         * $order
         */
        $order = (empty($options['order'])) ? '' : ( (is_array($options['order'])) ? 'ORDER BY '. implode(', ', $options['order']) : $options['order'] );

        /**
         * Verifica condi��es passadas, formatando o comando SQL de acordo
         */
        if(!empty($options['conditions'])){
            $conditions = $options['conditions'];
            /**
             * Chama conditions que monta a estrutura de regras SQL
             */
            foreach($conditions as $chave=>$valor){
                $tempRule = $this->conditions($chave, $conditions[$chave]);
                if(is_array($tempRule)){
                    $tempRule = implode(' AND ', $tempRule);
                }
                $rules[] = '('.$tempRule.')';
            }
        }

        /**
         * Quebra as regras dentro do WHERE para SQL
         */
        if(is_array($rules)){
            $rules = 'WHERE ' . implode(' AND ', $rules);
        }
        $sql = "SELECT
                    $fields
                FROM
                    $table
                    $join
                    $rules
                    $order
                ";

        /**
         * Retornar somente SQL
         *
         * Se modo==sql
         */
        if ( $modo == 'sql' ){
            return $sql;
        }

        /**
         * Debuggar
         *
         * Descomente a linha abaixo para debugar
         */
            //echo $sql . '<--<br />';

            //echo $this->conexao->testConexao();

        $query = $this->query($sql);
        $return = array();
        foreach($query as $chave=>$dados){
            /**
             * Monta estruturas de sa�da de acordo como o modo pedido
             *
             * O modo padr�o � ALL conforme configurado nos par�metros da fun��o
             */
            /**
             * ALL
             */
            if($modo == 'all'){
                array_push($return, $dados);
            /**
             * FIRST
             */
            } elseif($modo == 'first' and (count($fields) == 1 or is_string($fields))){
                if(is_array($fields)){
                    $return[] = $dados[$fields[0]];
                } else {
                    $return[] = $dados[$fields];
                }
            }
        }

        /**
         * Descomente a linha abaixo para debugar
         */
            //pr( $return);
        return $return;
    }

    /*
     * CONDITIONS
     *
     * Monta regras SQL para cada condition
     */

    function conditions($modo, $conditions){

        $rules = array();
        /**
         * NOT
         */
        if($modo == 'NOT'){
            foreach($conditions as $campo=>$valor){
                /**
                 * Se for uma array com v�rios valores
                 */
                if(is_array($valor)){
                    $rules[] = $campo .' NOT IN(\''. implode('\', \'', $valor) . '\')';
                } else {
                    $rules[] = $campo .' NOT IN(\''. $valor . '\')';
                }
            }
        /**
         * OR
         */
        } elseif($modo == 'OR'){
            foreach($conditions as $campo=>$valor){
                /**
                 * Se for uma array com v�rios valores
                 */
                //pr($conditions);
                if(is_array($valor)){
                    //echo 'oi';
                    $rules[] = $campo .' IN(\''. implode('\', \'', $valor) . '\')';
                } else {
                    //echo 'oi2';
                    $rules[] = $campo .' IN(\''. $valor . '\')';
                }

            }
            //if(is_array)
            $rules = implode(' OR ', $rules);
        /**
         * CAMPOS COMUNS
         */
        } else {

            /**
             * Ajusta o nome do campo
             */
            $campo = $modo;
            if(is_array($conditions)){
                foreach($conditions as $valor){
                    /**
                     * V�rios valores para este campo
                     */
                    if(is_array($valor)){
                        foreach($valor as $cadaValor){
                            $tempRules[] = $campo.'=\''. $cadaValor . '\'';
                        }
                    /**
                     * Um �nico valor para este campo
                     */
                    } else {
                        $tempRules[] = $campo.'=\''. $valor . '\'';
                    }
                }
                $rules[] = implode(' AND ', $tempRules);
            } else {
                //echo $conditions;

                $rules[] = $campo.'=\''. $conditions . '\'';
            }

        }
        //pr($rules);
        $return = $rules;
        return $return;
        
    }

}

?>
