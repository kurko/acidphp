<?php
/**
 * Tranforma pedidos CRUD relacionado a bases de dados em códigos SQL
 *
 * @package Classes
 * @name SQLObject
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 18/07/2009
 */
class SQLObject {

    /**
     *
     * @var class Classe responsável pela conexão com o banco de dados
     */
    protected $conexao;

    function __construct(){
        
    }

    /**
     * SELECT()
     *
     * Transforma um pedido em código SQL
     *
     * @param array $options
     * @return string Código SQL
     */
    public function select($options){

        /**
         * AJUSTA MODEL PRINCIPAL
         */
        $mainModel = $options["mainModel"];

        /**
         * CONFIGURAÇÕES GERAIS
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
        $leftJoin = (empty($leftJoinTemp)) ? '' : implode(' ', $leftJoinTemp);

        /**
         * usedModels definido
         */
        foreach($usedModels as $models){
            $options["models"][] = get_class($models);
        }


        /**
         * FIELDS
         *
         * $fields -> Campos que devem ser carregados
         */
        /**
         * Fields: Se nenhum campo foi indicado
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
         * Fields: Se algum campo foi indicado
         */
        else {
            /**
             * Se fields == array
             */
            if( is_array($options["fields"]) ){
                foreach( $options["fields"] as $campo ){
                    /**
                     * Verifica sintaxe: "Model.campo"
                     */
                    $underlinePos = strpos($campo, "." );
                    if( $underlinePos !== false ){
                        /**
                         * Model do campo usado
                         */
                        $modelReturned = substr( $campo, 0, $underlinePos );
                    }

                    if( in_array($modelReturned, $options["models"]) ){
                        $fieldModelUsed[$modelReturned] = $modelReturned;
                        $fields[] = $campo. " AS ".str_replace(".", "__", $campo);
                    }
                }
                /**
                 * Sempre carrega junto o id e o foreignKey das tabelas
                 * relacionadas
                 */
                foreach($fieldModelUsed as $model){
                    /**
                     * 'id' do registro
                     */
                    $fields[] = $model.".id AS ".$model."__id";
                    /**
                     * foreignKey da tabela relacionada
                     */
                    if( $model != get_class($mainModel) ){
                        if( array_key_exists($model, $mainModel->hasOne) ){
                            $fields[] = $model.".".$mainModel->hasOne[$model]["foreignKey"]." AS ".$model."__".$mainModel->hasOne[$model]["foreignKey"];
                        } else if( array_key_exists($model, $mainModel->hasMany) ){
                            $fields[] = $model.".".$mainModel->hasMany[$model]["foreignKey"]." AS ".$model."__".$mainModel->hasMany[$model]["foreignKey"];
                        }
                    }
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
         * Contém as condições para formação das regras SQL WHERE
         *
         * Sintaxe:
         *
         *  'conditions' => array(
         *      // diz que NÃO deve ser nenhum dos itens abaixo.
         *      'NOT' => array(
         *          'Usuario.id' => array('24', '25'),
         *          'OR' => array(
         *              'Usuario.id' => array('20', '21'),
         *              'Tarefa.id' => array('22', '23'),
         *          ),
         *      ),
         *      // valores serão 24 OU 25
         *      'OR' => array(
         *          'Usuario.id' => '24'
         *          'Usuario.id' => '25'
         *      ),
         *      // verificação simples, o campo deve ser igual a 29
         *      'Tarefa.id' => '29',
         *
         */
        /**
         * Analisa condições passadas, formatando o comando SQL de acordo
         */
        if(!empty($options['conditions'])){
            $conditions = $options['conditions'];

            /**
             * Models para Conditions
             *
             * Se models foram passados como parâmetro, envia-os para criação
             * de conditions em conformidade com os campos disponíveis.
             *
             * Esta é uma medida de segurança.
             */
            $options = array();
            if( !empty($usedModels) ){
                /**
                 * @todo - verificar se $options precisa estar aqui a seguir
                 */
                unset($options["models"]);
                foreach($usedModels as $models){
                    $options["models"][] = get_class($models);
                }
            }
            /**
             * Chama $this->conditions que monta a estrutura de regras SQL WHERE
             */
            $rules = $this->conditions($conditions, $options);
        }
        //pr($rules);
        
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

        /**
         * VERIFICA LIMIT
         *
         * Se 'limit' for setado, o DB limita a quantidade de registros
         * retornados, não importando os registros relacionados de outras
         * tabelas, como no caso de Left Join.
         */
        //if( empty($hit) $)
        $sql[] = "SELECT
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

    } // fim select()

    /**
     * CONDITIONS()
     *
     * Monta regras SQL para cada condition
     *
     * MODOS
     *      - 'NOT'  : Representa uma condição negativa;
     *      - 'OR'   : Representa uma condição de alternativa;
     *      - 'NOTOR': NOT (OR), representa "(not in) or (not in)"
     *
     * @param array $conditions
     * @param array $options Contém opções adicionais
     * @return string
     */
    function conditions($conditions, $options=""){
        //pr($options);
        /**
         * Passa cada condição e cria operações lógicas SQL
         */
        foreach($conditions as $modo=>$cond){

            /**
             * Configurações iniciais
             */
            /**
             * $modo: Se algum modo foi setado forçado, reconfigura
             */
            $modo = ( !empty($options["modo"]) ) ? $options["modo"] : $modo;
            /**
             * $cond: se não for array, certifica-se de se tornar uma
             */
            $cond = ( !is_array($cond) ) ? array($modo => $cond) : $cond;

            $rules = array();

            /**
             * Loop por cada campo passado como parâmetro
             */
            foreach($cond as $campo=>$valor){
                
                /**
                 * Ajusta modos
                 *
                 * $glue é o operador lógico da condição
                 */
                /**
                 * OR
                 */
                if( $modo == "OR" ){
                    $glue = "IN";
                }
                /**
                 * NOT
                 */
                else if( $modo == "NOT" ){
                    $glue = "NOT IN";
                }
                /**
                 * NOTOR
                 *
                 * Representa '(not in) or (not in). É feito novo loop e
                 * o parâmetro $modo é passado de forma forçada 'NOTOR'
                 */
                else if( $modo == "NOTOR" ){
                    $glue = "NOT IN";
                }
                /**
                 * Verificação normal
                 */
                else {
                    $glue = "IN";
                }

                /**
                 * Verifica se o campo é array ou não
                 */
                $loopQuery = false;
                if( !is_array($valor) ){
                    /**
                     * Para diminuir código, transforma $valor em array
                     */
                    $valor = array($valor);
                } else {
                    /**
                     * NOVO LOOP?
                     *
                     * Verifica se há necessidade de um novo loop e chama
                     * $this->conditions novamente.
                     */
                    foreach($valor as $subvalores){
                        if( is_array( $subvalores ) ){
                            $subOptions = $options;
                            $subOptions["modo"] = "NOTOR";
                            $valor = $this->conditions($cond, $subOptions );
                            unset($subOptions);
                            $loopQuery = true;
                        }
                    }
                }
                
                /**
                 * Ajusta regra SQL
                 *
                 * Pega $campo e valores necessário e mescla para gerar uma
                 * regra SQL em conformidade.
                 *
                 */
                if( !$loopQuery ){
                    /**
                     * Se models foram passados, verifica se o nome do campo
                     * digitado é condizente com o schema do DB
                     */
                    if( !empty($options["models"]) ){
                        $underlinePos = strpos($campo, "." );
                        if( $underlinePos !== false ){
                            /**
                             * Model e Campo
                             */
                            /**
                             * @todo - Se mandar carregar um model que não
                             * existe, não mostra erro. Isto não deve acontecer,
                             * somente quando for fazer um carregamento
                             * para o problema do SQL LIMIT com models
                             * relacionados. Meta:
                             *
                             * 1) Fazer aviso de model faltando
                             * 2) Enviar parâmetro através de $options indicando
                             * que este carregamento é com o argumento SQL LIMIT
                             *
                             */
                            $modelReturned = substr( $campo, 0, $underlinePos );
                            if( !in_array($modelReturned, $options["models"]) ){
                                $campoError = true;
                            }
                        }

                    }
                    /**
                     * Se está tudo OK com as verificações do campo
                     */
                    if( !$campoError )
                        $rules[] = $campo .' '.$glue.' (\''. implode('\', \'', $valor) . '\')';
                } else {
                    $rules[] = implode('\', \'', $valor);
                }

                unset($glue);

            }
            /**
             * Ajustes finais da operação atual
             */
            if( !empty($rules) AND is_array($rules) ){
                if( $modo == "OR" ){
                    $rules = implode(' OR ', $rules);
                } else if($modo == "NOTOR") {
                    $rules = implode(' OR ', $rules);
                } else {
                    $rules = implode(' AND ', $rules);
                }
            $finalRules[] = '('.$rules.')';
            }
            //pr($rules);
            unset($modo);
            unset($rules);
        }
        //pr($rules);
        $return = $finalRules;
        return $return;

    } // fim conditions()

}
?>