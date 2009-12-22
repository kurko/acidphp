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
         * LEFT JOIN
         *
         * Ajusta Left Joins de acordo com relacionamentos dos models
         */

        //echo "<br>".get_class($mainModel);
        $hasOne = $mainModel->hasOne;
        $hasMany = $mainModel->hasMany;
        $belongsTo = $mainModel->belongsTo;
        //pr($options);
        $has = array();
        $has = array_merge( $hasOne , $hasMany, $belongsTo );
        foreach( $has as $model=>$info ){

            /**
             * Verifica recursividade atual
             */
            if( $mainModel->currentRecursive < $mainModel->recursive
                AND !empty($options["models"][$model]) )
            {

                $usedModels[] = $options["models"][$model];
                $leftJoinSyntax = "LEFT JOIN ".$options["tableAlias"][$model] . " AS " . $model;

                /**
                 * Sintaxe SQL Left Join ON
                 */
                /**
                 * Se o Model atual está na regra belongsTo, ajusta a sintaxe ON
                 * da regra SQL Left Join de forma diferente
                 */
                /**
                 * LeftJoin ON:
                 *      - BelongsTo
                 */
                if( array_key_exists($model, $belongsTo) )
                    $leftJoinOnSyntax = "ON ". get_class( $mainModel ).".".$info["foreignKey"]."=".$model.".id";
                /**
                 * LeftJoin ON:
                 *      - hasOne
                 *      - hasMany
                 */
                else
                    $leftJoinOnSyntax = "ON ". get_class( $mainModel ).".id=".$model.".".$info["foreignKey"];

                /**
                 *
                 * @global array $GLOBALS['leftJoinTemp']
                 * @name $leftJoinTemp Contém frase Left Join para código SQL
                 */
                $leftJoinTemp[] = $leftJoinSyntax." ".$leftJoinOnSyntax;
            }
                        
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
         * Separador de model.campo
         */
        $separadorModelCampo = ".";
        /**
         * Fields: Se nenhum campo foi indicado
         */
        if( empty($options['fields']) ){
            foreach($usedModels as $model){
                /**
                 * Loop por cada campo da tabela para montar Fields
                 */
                if( is_array($model->tableDescribed) ){
                    foreach( $model->tableDescribed as $campo=>$info ){
                        $fields[] = get_class( $model ).".".$campo." AS '".get_class( $model ).$separadorModelCampo.$campo."'";
                    }
                }
            }
        }
        /**
         * Fields: Se algum campo foi indicado
         */
        else {

            if( is_string($options["fields"]) )
                $options["fields"] = array($options["fields"]);
            /**
             * Se fields == array
             */
            if( is_array($options["fields"]) ){
                foreach( $options["fields"] as $campo ){

                    /*
                     * 'Field' com espaço
                     *
                     * Quando field tem espaço é pq tem alguma regra especial,
                     * então não insere 'AS Model.campo1' no final.
                     */
                    $space = strpos($campo, " " );
                    if( $space !== false ){
                        $fields[] = $campo;
                    }
                    /*
                     * Field deve ser tratado
                     */
                    else {
                        
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
                            $fields[] = $campo. " AS '".str_replace(".", $separadorModelCampo, $campo)."'";
                        }
                    }
                }
                
                if(!empty($fieldModelUsed)){

                    /*
                     * Sempre carrega junto o id e o foreignKey das tabelas
                     * relacionadas
                     */
                    foreach($fieldModelUsed as $model){

                        $regex = "/('". $model.$separadorModelCampo."id')/";

                        $loadingIdAlready = false;
                        foreach( $fields as $soughtField ){
                            if( preg_match($regex, $soughtField) )
                                $loadingIdAlready = true;
                        }

                        if( !$loadingIdAlready ){

                            /**
                             * 'id' do registro
                             */
                            $fields[] = $model.".id AS '".$model.$separadorModelCampo."id'";
                        }

                        /**
                         * foreignKey da tabela relacionada
                         */
                        if( $model != get_class($mainModel) ){

                            /**
                             * hasOne
                             */
                            if( array_key_exists($model, $mainModel->hasOne) ){
                                $fields[] = $model.".".$mainModel->hasOne[$model]["foreignKey"]." AS '".$model.$separadorModelCampo.$mainModel->hasOne[$model]["foreignKey"]."'";
                            }
                            /**
                             * hasMany
                             */
                            else if( array_key_exists($model, $mainModel->hasMany) ){
                                $fields[] = $model.".".$mainModel->hasMany[$model]["foreignKey"]." AS '".$model.$separadorModelCampo.$mainModel->hasMany[$model]["foreignKey"]."'";
                            }
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
         *
         * Trata "order" requisitada
         */
        /**
         * Se order não especificada
         */
        if( empty($options['order']) ){
            $order = "";
        }
        /**
         * Se order foi passado em formato array
         */
        else if( is_array($options['order']) ) {
            /**
             * Verifica se o campo/Model pedido realmente existe
             */
            foreach( $options["order"] as $chave=>$currentOrder ){
                /**
                 * Verifica sintaxe: "Model.campo"
                 */
                $underlinePos = strpos($currentOrder, "." );
                if( $underlinePos !== false ){
                    /**
                     * Model do campo usado
                     */
                    $modelReturned = substr( $currentOrder, 0, $underlinePos );
                    $campoReturned = substr( $currentOrder, $underlinePos+1, 100 );
                }
                if( !in_array($modelReturned, $options["models"]) ){

                    //pr($options);
                    if( array_key_exists($modelReturned, $options["hiddenHasMany"]) )
                        unset($options["order"][$chave]);
                    else
                        trigger_error( "Specified Model <em>".$modelReturned."</em> is not a related model (hasMany, hasOne, etc)" , E_USER_ERROR);
                }
            }

            if( !empty($options["order"]) )
                $order = implode(', ', $options['order']);
        } else {
            $order = $options['order'];
        }
        /**
         * Formato final de ORDER BY
         */
        if( !empty($order) ) $order = "ORDER BY ".$order;
        else $order = "";

        /************************
         *
         * GROUP
         *
         * Trata "group" requisitada, que equivale ao GROUP BY de um SQL
         */
            /**
             * Se group não especificada
             */
            if( empty($options['group']) ){
                $group = "";
            }
            /**
             * Se group foi passado em formato array
             */
            else if( is_array($options['group']) ) {
                /**
                 * Verifica se o campo/Model pedido realmente existe
                 */
                foreach( $options["group"] as $chave=>$currentGroup ){
                    /**
                     * Verifica sintaxe: "Model.campo"
                     */
                    $underlinePos = strpos($currentGroup, "." );
                    if( $underlinePos !== false ){
                        /**
                         * Model do campo usado
                         */
                        $modelReturned = substr( $currentGroup, 0, $underlinePos );
                        $campoReturned = substr( $currentGroup, $underlinePos+1, 100 );
                    }
                    if( !in_array($modelReturned, $options["models"]) ){

                        //pr($options);
                        if( array_key_exists($modelReturned, $options["hiddenHasMany"]) )
                            unset($options["group"][$chave]);
                        else
                            trigger_error( "Specified Model <em>".$modelReturned."</em> is not a related model (hasMany, hasOne, etc)" , E_USER_ERROR);
                    }
                }

                if( !empty($options["group"]) )
                    $group = implode(', ', $options['group']);
            } else {
                $group = $options['group'];
            }
            /**
             * Formato final de GROUP BY
             */
            if( !empty($group) ) $group = "GROUP BY ".$group;
            else $group = "";
            
        /*
         * // FIM DE GROUP
         * 
         ************************/


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
        $rules = "";
        if( !empty($options['conditions']) ){
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
        if( !empty($rules) AND is_array($rules) ){
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
                    $group
                    $order
                    $limit
                ";
        /**
         * Retornar somente SQL
         *
         * Se modo==sql
         */
        //if ( $modo == 'sql' ){
            //return $sql;
        //}
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
        //pr($conditions);
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
            //pr($cond);
            $rules = array();

            /**
             * Loop por cada campo passado como parâmetro
             */
            foreach($cond as $campo=>$valor){

                $emptyCampo = false;
                if( empty($campo)
                    //OR is_int($campo)
                        )
                {
                    $campo = "";
                    $emptyCampo = true;
                }

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

                            if( $modo == 'NOTOR' )
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
                    if( !empty($options["models"]) 
                        AND !$emptyCampo)
                    {
                        $underlinePos = strpos($campo, "." );
                        if( $underlinePos !== false )
                        {
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
                    if( empty($campoError) OR !$campoError ){

                        $space = strpos($campo, " " );

                        /*
                         * Não há um field especificado, somente um valor na
                         * array, tipo array("Usuario.id='10'"), sem um índice.
                         */
                        if( $emptyCampo ){
                            $rules[] = $valor[0];
                        }
                        /*
                         * REGRAS EXCEÇÕES
                         *
                         * Verifica as regras:
                         *
                         *      LIKE, >, <, !=
                         */
                        else if( $space !== false ){
                            $rules[] = $campo .' \''. $valor[0] . '\'';
                        }
                        /*
                         * Não há exceções, criar condição normalmente
                         */
                        else {
                            $rules[] = $campo .' '.$glue.' (\''. implode('\', \'', $valor) . '\')';
                        }
                    }
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
            unset($modo);
            unset($rules);
        }
        $return = $finalRules;
        return $return;

    } // fim conditions()

}
?>