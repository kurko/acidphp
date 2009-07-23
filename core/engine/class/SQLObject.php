<?php
/**
 * Tranforma pedidos CRUD do DB em códigos SQL
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
     * @var class Classe responsável pela conexão com o banco de dados
     */
    protected $conexao;

    function __construct(){
        //$this->conexao = $conexaoClass;
    }

    /**
     * method FIND()
     *
     * Transforma um pedido em código SQL para ser executado
     *
     * @param array $options
     * @return string Código SQL
     */
    public function find($options){
        //pr($options);
        /**
         * AJUSTA MODEL PRINCIPAL
         */
        $mainModel = $options["mainModel"];
        /**
         * Configurações gerais
         *
         * Ajusta os parâmetros passados em variáveis específicas
         */

        /**
         * TABLE
         * 
         * $table -> Tabela a ser usada
         */
        $tableAlias = ( empty($options['tableAlias']) ) ? '' : $options['tableAlias'];
        
        $table = array(
            $mainModel->useTable." AS ".get_class($mainModel)
        );
        $usedModels[] = $mainModel;

        /**
         * JOIN
         */
        /**
         * Left Join
         *
         * Ajusta Left Joins de acordo com relacionamentos dos models
         */
        $hasOne = $mainModel->hasOne;
        $hasMany = $mainModel->hasMany;
        $has = array();
        $has = array_merge( $hasOne , $hasMany );
        foreach( $has as $model=>$info ){
            $usedModels[] = $options["models"][$model];
            $leftJoinTemp[] = "LEFT JOIN ".$options["tableAlias"][$model] . " AS " . $model
                        . " ON ". get_class( $mainModel ).".id=".$model.".".$info["foreignKey"];
        }
        /**
         * $join -> Left Join, Right Join, Inner Join, etc
         */
        $leftJoin = (empty($leftJoinTemp)) ? '' : implode(' ', $leftJoinTemp) ;

        /**
         * FIELDS
         *
         * $fields -> Campos que devem ser carregados
         */
        /**
         * Se nenhum campo foi indicado
         */
        if( empty($options['fields']) ){
            foreach($usedModels as $model){
                /**
                 * Loop por cada campo da tabela para montar Fields
                 */
                foreach( $model->tableDescribed as $campo=>$info ){
                    $fields[] = get_class( $model ).".".$campo." AS ".get_class( $model )."__".$campo;
                }
            }
        }
        /**
         * Se algum campo foi indicado
         */
        else {
            /**
             * Se fields == array
             */
            if( is_array($options["fields"]) ){
                foreach( $options["fields"] as $campo ){
                    $underlinePos = strpos($campo, "." );
                    if( $underlinePos !== false ){
                        /**
                         * Model do campo usado
                         */
                        $modelReturned = substr( $campo, 0, $underlinePos );
                    }
                    $fieldModelUsed[$modelReturned] = $modelReturned;
                    $fields[] = $campo. " AS ".str_replace(".", "__", $campo);
                }
                foreach($fieldModelUsed as $model){
                    $fields[] = $model.".id AS ".$model."__id";
                }

                
            }
            /**
             * Se fields == string
             */
            else if(is_string($options["fields"])) {
                $fields[] = $options["fields"];
            }
        } // fim fields


        /**
         * ORDER
         */
        $order = (empty($options['order'])) ? '' : ( (is_array($options['order'])) ? 'ORDER BY '. implode(', ', $options['order']) : "ORDER BY ". $options['order'] );

        /**
         * LIMIT
         */
        $limit = (empty($options['limit'])) ? '' : 'LIMIT '. $options['limit'];

        /**
         * CONDITIONS
         * 
         * Verifica condições passadas, formatando o comando SQL de acordo
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
         *
         * GERA SQL
         *
         *
         *
         */
        /*
         * Quebra as regras dentro do WHERE para SQL
         */
        if(is_array($rules)){
            $rules = 'WHERE ' . implode(' AND ', $rules);
        }
        $sql = "SELECT
                    ". implode(", ", $fields) ."
                FROM
                    ". implode(", ", $table) ."
                    $leftJoin
                    $rules
                    $order
                    $limit
                ";

        /**
         * Retornar somente SQL
         *
         * Se modo==sql
         */
        if ( $modo == 'sql' ){
            return $sql;
        }
        return $sql;

        /**
         * Debuggar
         *
         * Descomente a linha abaixo para debugar
         */
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
                 * Se for uma array com vários valores
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
                 * Se for uma array com vários valores
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
                     * Vários valores para este campo
                     */
                    if(is_array($valor)){
                        foreach($valor as $cadaValor){
                            $tempRules[] = $campo.'=\''. $cadaValor . '\'';
                        }
                    /**
                     * Um único valor para este campo
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