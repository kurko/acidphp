<?php
/**
 * Arquivo que representa a estrutura MODEL de um MVC
 *
 * @package MVC
 * @name Model
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 19/07/2009
 */
class Model
{
    /**
     * CONFIGURAÇÕES
     */
        /**
         * Contém a tabela a ser usada por este model
         *
         * @var string Tabela a ser usada
         */
         
        protected $useTable;
        /**
         * Contém o id (ou mais de um em array) do campo objeto de ação.
         *
         * @var mixed
         */
        public $id;


    /**
     * RELACIONAMENTOS DE MODELS
     */
        /**
         *
         * @var array Indica que este Model tem outros sub-Models
         */
        public $hasOne = array();
        public $hasMany = array();
        public $belongsTo = array();

    /**
     * CONEXÃO
     *
     * @var object Contém a conexão com a base de dados
     */
    private $conn;
    
    /**
     *
     * @var array Tabela descrita
     */
    public $tableDescribed;


    /**
     * CONFIGURAÇÕES INTERNAS (PRIVATE)
     */
        /**
         * Array contendo as tabelas existentes na Base de Dados
         *
         * @var array
         */
        private $dbTables;

        public $sqlObject;

        public $modelsLoaded = array();
        public $tableAlias = array();

        public $validation = array();

        protected $params;
        


    /**
     * __CONSTRUCT()
     *
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     * @since v0.1
     * @param array $params
     *      "conn" object : conexão com o db;
     */

    function  __construct($params) {

        /**
         * RECURSIVE
         */
        $this->recursive = $params["recursive"];
        if( empty($params["currentRecursive"]) ){
            $this->currentRecursive = 0;
        } else {
            $this->currentRecursive = $params["currentRecursive"];
        }

        //echo "<strong>". get_class($this) . " - " . $currentRecursive . "</strong><br />";

        /**
         * CONFIGURAÇÃO DE AMBIENTE
         */
        $this->params = &$params["params"];

        /**
         * CONEXÃO
         *
         * Configura a conexão com a base de dados
         */
        $this->conn = ( empty($params["conn"]) ) ? '' : $params["conn"];
        $this->dbTables = ( empty($params["dbTables"]) ) ? array() : $params["dbTables"];
        
        /**
         * DEFINE A TABELA A SER USADA
         */
        $this->useTable = ( empty($this->useTable) ) ? $params["modelName"] : $this->useTable;

        /**
         * DESCRIBE NA TABELA
         *
         * Com global $describedTables, sabemos quais já foram descritas e não
         * repetimos o processo.
         */
        global $describedTables;
        if( !array_key_exists( get_class($this), $describedTables) ){
            $this->describeTable();
        } else {
            $this->tableDescribed = $describedTables[ get_class($this) ];
        }
        
        /**
         * CRIA RELACIONAMENTOS
         */
        /*
         * Prepara Recursive + 1
         */
        $params["currentRecursive"] = $this->currentRecursive+1;
        /**
         * hasOne
         */
        if( !empty($this->hasOne) ){
            foreach( $this->hasOne as $model=>$propriedades ){

                if( $params["currentRecursive"] <= $params["recursive"] ){
                    $this->{$model} = new $model($params);
                    $this->modelsLoaded[] = $model;
                }

            }
        }
        /**
         * hasMany
         */
        if( !empty($this->hasMany) ){
            foreach( $this->hasMany as $model=>$propriedades ){
                if( $params["currentRecursive"] <= $params["recursive"] ){
                    $this->{$model} = new $model($params);
                    $this->modelsLoaded[] = $model;
                }
            }
        }

        /**
         * belongsTo
         */
        if( !empty($this->belongsTo) ){
            foreach( $this->belongsTo as $model=>$propriedades ){
                if( $params["currentRecursive"] <= $params["recursive"] ){
                    $this->{$model} = new $model($params);
                    $this->modelsLoaded[] = $model;
                }
            }
        }

        /**
         * Carrega as tabelas de cada model
         */
        $this->tableAlias[ get_class($this) ] = $this->useTable;
        foreach( $this->modelsLoaded as $chave=>$valor ){
            $this->tableAlias[$valor] = $this->{$valor}->useTable;
        }

        /**
         * DATA ABSTRACTOR
         */
        /**
         * Instancia DatabaseAbstractor
         *
         * Responsável pela abstração de bancos de dados.
         */
        $this->databaseAbstractor = new DatabaseAbstractor(array(
                'conn' => $this->conn,
            )
        );
    } // fim __construct()

    /**
     * MÉTODOS CRUD
     */
    /**
     * SAVE()
     *
     * $data deve ter o seguinte formato:
     *      [model]=>array([campo]=>valor), [modelFilho]=>array([campo]=>valor)
     *
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     * @since v0.1
     * @param array $data Dados enviados para salvar no DB
     * @param array $options
     * @return bool Se salvou ou não
     */
    public function save(array $data, $options = array()){
        if( is_array($data) ){
            $data = Security::Sanitize($data);

            /**
             * VALIDATE
             */
            if( $this->validate($data) ){
                /**
                 * Loop por cada tabela dos valores enviados em $data
                 */
                foreach($data as $model=>$campos){

                    /**
                     * Verifica se o Model requisitado é o próprio ou são filhos
                     */
                    if( get_class($this) == $model ){
                        $tabela = $this->useTable;
                        $modelPai = true;

                        /**
                         * Verifica se este Model pertence a outro
                         */
                        if( !empty($this->belongsTo) ){
                            foreach( $this->belongsTo as $model=>$propriedades ){
                                if( array_key_exists($model, $data) ){
                                    $campos[ $propriedades["foreignKey"] ] = $data[$model]["id"];
                                }
                            }
                        }

                    } else {
                        $tabela = $this->{$model}->useTable;
                        $modelPai = false;
                        if( array_key_exists($model, $this->hasOne) ){
                            $modelsFilhos[$model] = $campos;
                        } else if( array_key_exists($model, $this->hasMany) ){
                            $modelsFilhos[$model] = $campos;
                        }
                    }

                    $doUpdate = false;
                    if( $modelPai ){

                        if( in_array($tabela, $this->dbTables) ){
                            /**
                             * Loop por cada campo e seus valores
                             */
                            foreach( $campos as $campo=>$valor ){

                                if( array_key_exists($campo, $this->tableDescribed) ){

                                    if( $campo == "id" ){
                                        $doUpdate = true;
                                        $campoId = $valor;
                                    } else {

                                        $camposStr[] = $campo;

                                        /**
                                         * Checkbox? Tinyint?
                                         *
                                         * Verifica se o campo é do tipo checkbox.
                                         */
                                        $type = StrTreament::getNameSubStr($this->tableDescribed[ $campo ]["Type"], "(");
                                        if( in_array($type, array("tinyint","bool") )){
                                            if( !empty($valor) )
                                                $valor = '1';
                                        }

                                        $valorStr[] = $valor;
                                    }
                                    
                                } else {
                                    showWarning("Campo inexistente configurado no formulário.");
                                }
                            }


                            if( !empty($camposStr) ){
                                /**
                                 * @todo - comentar
                                 */

                                if( !$doUpdate ){
                                    $tempSql = "INSERT INTO
                                                    ".$tabela."
                                                        (".implode(",", $camposStr).")
                                                VALUES
                                                    ('".implode("','", $valorStr)."')
                                                ";
                                } else {

                                    for($i = 0; $i < count($camposStr); $i++){
                                        $camposUpdate[] = $camposStr[$i]."='".$valorStr[$i]."'";
                                    }

                                    $tempSql = "UPDATE
                                                    ".$tabela."
                                                SET
                                                    ".implode(",", $camposUpdate)."
                                                WHERE
                                                    id='".$campoId."'
                                                ";
                                }
                                /**
                                 * SQL deste campo
                                 */
                                $sql[] = $tempSql;
                            }
                            unset( $camposStr );
                            unset( $valorStr );
                        }
                        /**
                         * Ops.. Alguma tabela não existe
                         */
                        else {
                            //showWarning("Alguma tabela especificada não existe");
                        }
                    } // fim modelFather==true

                }
                //return $sql;

                /**
                 * SALVA SQL CRIADO
                 *
                 * Se houverem dados de tabelas relacionadas, envia dados para seus
                 * respectivos Models para serem salvas
                 */
                if( count($sql) > 0 ){
                    foreach( $sql as $instrucao ){
                        /**
                         * Salva dados na tabela deste Model
                         */
                        //pr($instrucao. get_class($this));
                        $this->conn->exec($instrucao);
                        $lastInsertId = $this->conn->lastInsertId();

                        $modelsFilhos = array();
                        /**
                         * Se houverem Models filhos relacionados,
                         * envia dados para serem salvos
                         */
                        if( !empty($modelsFilhos) ){
                            foreach($modelsFilhos as $model=>$campos){
                                $dataTemp[$model] = $campos;

                                /**
                                 * Pega o ForeignKey
                                 */
                                if( array_key_exists($model, $this->hasOne) ){
                                    $foreignKey = $this->hasOne[$model]["foreignKey"];
                                } else if( array_key_exists($model, $this->hasMany) ){
                                    $foreignKey = $this->hasMany[$model]["foreignKey"];
                                } else if( array_key_exists($model, $this->belongsTo) ){
                                    $foreignKey = $this->belongsTo[$model]["foreignKey"];
                                } else if( array_key_exists($model, $this->hasAndBelongsToMany) ){
                                    $foreignKey = $this->hasAndBelongsToMany[$model]["foreignKey"];
                                }

                                /**
                                 * Envia dados para Models relacionados salvarem
                                 */
                                $dataTemp[$model][ $foreignKey ] = $lastInsertId;
                                $this->$model->save( $dataTemp );

                                unset($dataTemp);
                            }
                        }
                    }

                    return true;
                }
            }
            /**
             * Não validou
             */
            else {
                $_SESSION["Sys"]["addToThisData"] = $data;
                if( !empty($this->params["post"]["formUrl"]) ){
                    $_SESSION["Sys"]["options"]["addToThisData"]["destLocation"] = $this->params["post"]["formUrl"];
                }
                redirect($this->params["post"]["formUrl"]);
            }
        }

        return false;
    } // FIM SAVE()

    /**
     * UPDATE()
     *
     * Executa a instrução UPDATE no banco de dados.
     *
     * @param array $toUpdate
     * @param mixed $conditions ID do registro a ser atualizado ou array com
     * valores, ex. array("campo"=>"valor")
     * @return bool
     */
    public function update(array $toUpdate, $conditions){

        if( !empty($toUpdate) ){

            /**
             * toUpdate
             *
             * Analisa o que deve ser atualizado e onde (análise de models
             * relacionais).
             */
            foreach( $toUpdate as $campo=>$valor ){

                /**
                 * Define os models e campos a serem atualizados
                 */
                $underlinePos = strpos($campo, "." );
                if( $underlinePos !== false ){
                    /**
                     * Model e Campo
                     */
                    $modelReturned = substr( $campo, 0, $underlinePos );
                    $campoReturned = substr( $campo, $underlinePos+1, 100 );
                } else {
                    $modelReturned = get_class($this);
                    $campoReturned = $campo;
                }

                $rule[$modelReturned][] = $modelReturned.".".$campoReturned."='".$valor."'";
            }

            /**
             * conditions
             *
             * Ajusta as condições para se realizar update
             */

            if( !empty($conditions) ){

                //echo $conditions["id"];

                if( is_array($conditions) AND !empty($conditions["id"]) ){
                    $idToUpdate = $conditions["id"];
                }

                else if( is_string($conditions) OR is_int($conditions) ){
                    $idToUpdate = $conditions;
                    if( !is_array($conditions) ){
                        $conditions = array("id" => $conditions);
                    } else {
                    }

                }



                if( is_array($conditions) ){
                    foreach( $conditions as $campo=>$valor ){
                        /**
                         * Define os models e campos como condições
                         */
                        $underlinePos = strpos($campo, "." );
                        if( $underlinePos !== false ){
                            /**
                             * Model e Campo
                             */
                            $modelReturned = substr( $campo, 0, $underlinePos );
                            $campoReturned = substr( $campo, $underlinePos+1, 100 );
                        } else {
                            $modelReturned = get_class($this);
                            $campoReturned = $campo;
                        }
                        $where[$modelReturned][] = $modelReturned.".".$campoReturned."='".$valor."'";
                    }
                }


            }
            
            /**
             * Para update em models relacionados, idToUpdate precisa estar
             * especificado
             */
            /**
             * Cria SQLs
             */
            foreach( $rule as $model=>$valor ){

                /**
                 * Models relacionados?
                 */
                if( (
                        (
                            array_key_exists($model, $this->hasMany)
                            OR array_key_exists($model, $this->hasOne)
                        )
                        AND !empty($idToUpdate)
                    )
                    OR ($model == get_class($this))

                ){

                    $has = array_merge($this->hasMany, $this->hasOne);

                    if( $model == get_class($this) ){
                        $modelUseTable = $this->useTable;
                        $relation = "";
                    } else {
                        $modelUseTable = $this->{$model}->useTable;
                        $relation = $model.".".$has[$model]["foreignKey"]."='".$idToUpdate."'";
                    }

                    $sqlWhere = "";
                    if( !empty($where[$model]) ){
                        $sqlWhere[] = implode(" AND ", $where[$model]);
                    }

                    if( !empty($relation) )
                        $sqlWhere[] = $relation;

                    if( !empty($sqlWhere) ){

                        $sqlWhere = "WHERE ". implode(" AND ", $sqlWhere);
                    }
                    

                    $sql[] =
                        "UPDATE
                            ".$modelUseTable." AS ".$model."
                        SET
                            ".implode(",", $valor)."
                            ".$sqlWhere."
                            ";

                    unset($sqlWhere);

                }
            }

            /**
             * EXECUTA INSTRUÇÕES SQL
             */
            foreach( $sql as $instrucao ){
                $this->query($instrucao);
            }

            return true;

        }

        else {
            return false;
        }

    }

    /**
     * FIND()
     * 
     * Função responsável por retornar dados de uma base de dados através de
     * classes DatabaseAbstractor
     *
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     * @since v0.1
     * @param array $options Contém opçoes de carregamento
     *      - '' : 
     * @param string $mode Modo de retorno
     *      - 'all' (padrão) : Listagem completa
     * @return array
     */
    public function find($options = array(), $mode = "all"){

        /**
         * Quando nenhum limit especificado (por questões de segurança)
         */
        if( empty($options["limit"]) )
            $options["limit"] = Config::read("modelAutoLimit");


        if( is_array($options) AND array_key_exists("page", $options) ){
            if( empty($options["page"]) )
                $options["page"] = 1;
        }
        /**
         * ID especificado?
         *
         * Verifica se foi especificado um id e carrega este
         */
        if( !empty($this->id) AND empty($options["conditions"]) ){
            $options["conditions"] = array(
                get_class($this).".id" => $this->id
            );
        }

        else if( !empty($options) AND !is_array($options) AND ((int) $options >= 0) ){

            $currentId = $options;
            unset($options);
            $options["conditions"] = array(
                get_class($this).".id" => $currentId
            );

        }

        /**
         * CONFIGURAÇÕES DE RELACIONAMENTO
         */
        /**
         * Informações sobre tabelas dos models
         */
        $options["tableAlias"] = $this->tableAlias;
        foreach( $options["tableAlias"] as $model=>$valor ){
            if( get_class($this) != $model ){
                $options["models"][$model] = $this->{$model};
            }
        }
        /**
         * Define model principal
         */
        $options["mainModel"] = $this;

        /**
         * GERA SQL
         *
         * Gera SQL com SQLObject
         */
        $querysGerados = $this->databaseAbstractor->find($options, $mode);
        return $querysGerados;

    }// fim find()

    /**
     * PAGINATE()
     *
     * Método para paginação de resultados. É um alias para o método find,
     * exceto que com model::paginate() há um relacionamento
     *
     * @param array $options Opções de busca, igual ao método model::find()
     * @param string $mode
     * @return array
     */
    public function paginate(array $options = array(), $mode = "all"){

        /**
         * Quando nenhum limit especificado (por questões de segurança)
         */
        if( empty($options["limit"]) )
            $options["limit"] = 50;//Config::read("modelAutoLimit");

        if( array_key_exists("page", $this->params["args"]) ){
            /**
             * Segurança contra URL injection
             */
            if( ($this->params["args"]["page"] * 1) > 0 ){
                $options["page"] = $this->params["args"]["page"];
            }
        }

        if( !array_key_exists("page", $options) ){
            $options["page"] = 1;
        }

        if( $options["page"] < 1 )
            $options["page"] = 1;

        $totalRows = $this->countRows();


        $startLimit = $options["limit"] * ($options["page"] - 1);

        /**
         * Se a página for maior do que o possível de amostragem (segurança
         * contra usuários).
         */
        if( $startLimit > $totalRows AND $totalRows > 0 ){
            $startLimit = $totalRows - $options["limit"];
        }

        if( empty($this->params["args"]["page"]) )
            $this->params["args"]["page"] = "";

        $this->params["paginator"][get_class($this)] = array(
            "class" => get_class($this),
            "totalRows" => $totalRows,
            "startLimit" => $startLimit,
            "limit" => $options["limit"],
            "page" => $options["page"],
            "urlGivenPage" => $this->params["args"]["page"],

        );

        $options["limit"] = $startLimit.",".$options["limit"];

        return $this->find($options, $mode) ;
    }
    /**
     * DELETE()
     *
     * Exclui um registro da tabela com o id especificado
     *
     * @param mixed $idOrConditions Integer com id a ser excluído ou array com
     *      vários ids.
     * @param array $options
     * @return bool
     */
    public function delete($idOrConditions = "", $options = array() ){
        /**
         * isDeleteAll?
         */
        $isDeleteAll = false;
        if( in_array("deleteAll", $options) )
            $isDeleteAll = true;

        /**
         * Se uma condição foi especificada
         */
        if( !empty($idOrConditions) ){
            /**
             * Se foi especificado um id
             */
            if( (is_int($idOrConditions) or is_string($idOrConditions)) and $idOrConditions > 0 ){
                $sql[] = array( "id='".$idOrConditions."'" );

                $currentId = $idOrConditions;
                /**
                 * deleteAll se necessário
                 */
                if( $isDeleteAll ){
                    $hasRelations = array_merge($this->hasOne, $this->hasMany);

                    if( !empty($hasRelations) ){
                        foreach( $hasRelations as $model=>$has ){
                            $this->{$model}->delete( array($has["foreignKey"] => $currentId), array("deleteAll") );
                        }
                    }
                } // fim deleteAll()

            }
            /**
             * Vários IDs em formato array
             */
            else if( is_array($idOrConditions)  ){

                foreach( $idOrConditions as $chave=>$valor ){
                    /**
                     * Se há um id em meio aos valores da array
                     */
                    if( is_int($chave) AND (is_int($valor) or is_string($valor)) AND $valor > 0 ){
                        $sql[] = array( "id='".$valor."'" );

                        $currentId = $valor;
                        /**
                         * deleteAll se necessário
                         */
                        if( $isDeleteAll ){
                            $hasRelations = array_merge($this->hasOne, $this->hasMany);

                            if( !empty($hasRelations) ){
                                foreach( $hasRelations as $model=>$has ){
                                    $this->{$model}->delete( array($has["foreignKey"] => $currentId), array("deleteAll") );
                                }
                            }
                        } // fim deleteAll()
                    }
                    /**
                     * Outras condições
                     */
                    else {
                        /**
                         * @todo - para deleteAll()
                         *
                         * Quando o usuário for deletar sem usar o id (ex.
                         * pais_id=50), é necessário fazer find() para descobrir
                         * o id do usuário.
                         */
                        $sql[] = array( $chave."='".$valor."'" );
                    }

                } // fim foreach array $idOrConditions
            }

        }
        /**
         * Um id objeto de ação foi especificado
         */
        else if( !empty($this->id) ){

            $sql[] = array( "id='".$this->id."'" );
            
        }
        /**
         * Nada foi especificado
         */
        else {
            return false;
        }

        /**
         * Executa todos os SQLs
         */
        if( !empty($sql) ){

            $erro = false;
            foreach( $sql as $instrucoes ){
                if( !$this->query("DELETE FROM ".$this->useTable." WHERE ".implode(" AND ", $instrucoes) ) ){
                    $erro = true;
                }
            }

            if( $erro ){
                return false;
            } else {
                unset($this->id);
                return true;
            }

        }
        /**
         * Não há SQLs a serem rodados
         */
        else {
            return false;
        }

    } // fim delete()

    /**
     * DELETEALL()
     *
     * Exclui um registro e todos os outros dados relacionados em outras
     * tabelas.
     *
     * Para funcionamento correto, é necessário especificar o id do registro.
     *
     * @param mixed $idOrConditions
     * @param array $options
     * @return bool
     */
    public function deleteAll($idOrConditions, $options = array() ){

        /**
         * É necessário especificar o id do registro a ser excluído
         */
        if( !is_int($idOrConditions) and !is_string($idOrConditions) ){
            
            $hasId = false;
            if( is_array($idOrConditions) ){

                foreach( $idOrConditions as $chave=>$valor ){
                    if( (is_int($valor) or is_string($valor)) and $valor > 0 ){
                        $hasId = true;
                    }
                }
            }
            if( !$hasId )
                return false;
        }

        array_push($options, "deleteAll");
        return $this->delete($idOrConditions, $options);

    }

    /**
     * QUERY()
     *
     * Roda um comando SQL definido pelo usuário
     *
     * @param string $sql
     * @param array $options
     * @return array Resultado formatado com PDO::fetchAll()
     */
    public function query($sql = "", $options = array()){
        if( is_string($sql) )
            return $this->conn->crud($sql);
    }

    /**
     * COUNTROWS()
     *
     * Retorna a quantidade de registros de um model
     *
     * @param array $options
     * @return int
     */
    public function countRows( array $options = array() ){

        /**
         * Retorna a quantidade total de registros do Model
         */
        if( empty($options) ){
            $count = $this->query("SELECT COUNT(*) as count FROM ".$this->useTable." AS ".get_class($this));
            return $count[0]["count"];
        }
        /**
         * @todo - implementar
         */
        else {
            $options["fields"] = array("COUNT(*) AS count");
            $count = $this->find($options);
            pr($count);
        }
    }

    /**
     * MÉTODOS DE SUPORTE
     */
    /**
     * VALIDATE()
     *
     * Valida dados enviados em formulários automaticamente de acordo com regras
     * descritas nos respectivos Models.
     *
     * @param mixed $data Contém os dados para validação
     * @param bool $sub Para verificar Models recursivamente, não deve ser usado
     * externamente.
     * @return bool Se validar, retorna verdadeiro
     */
    public function validate($data, $sub = false){

        /**
         * Limpa Session
         */
        if( !$sub )
            unset($_SESSION["Sys"]["FormHelper"]["notValidated"]);


        $validationRules = $this->validation;
        $vE = array();

        if( is_array($data) ){

            foreach($data as $model=>$campos){

                if( $model == get_class($this) ){

                    if( !empty($validationRules) ){

                        foreach( $campos as $campo=>$valor ){

                            /**
                             * Se o campo possui regras de validação
                             */
                            if( array_key_exists($campo, $validationRules ) ){

                                /**
                                 * VALIDAÇÃO
                                 */
                                $vR = $validationRules[$campo];
                                /**
                                 * Cada campo pode ter mais de uma validação
                                 */
                                /**
                                 * O campo tem somente uma validação
                                 */
                                if( array_key_exists("rule", $vR) ){

                                    /**
                                     * REGRA PERSONALIZADA
                                     *
                                     * Se a regra está configurada no Model
                                     */
                                    if( method_exists($this, $vR["rule"]) ){

                                        /**
                                         * Se não validou
                                         */
                                        if( !$this->{$vR["rule"]}($valor) ){

                                            /**
                                             * Caso seja um model-filho no
                                             * relacionamento de models
                                             */
                                            if( $sub )
                                                $vE[$campo] = '1';
                                            else
                                                $vE[$model][$campo] = '1';

                                            /**
                                             * Session para formHelper
                                             */
                                            if( !empty($vR["message"]) )
                                                $message = $vR["message"];
                                            else if( !empty($vR["m"]) )
                                                $message = $vR["m"];
                                            else {
                                                if( isDebugMode() )
                                                    showWarning("Mensagem de validação do campo ".$campo." não especificada");
                                                $message = "Not validated!";
                                            }
                                                
                                            $_SESSION["Sys"]["FormHelper"]["notValidated"][$model][$campo]["message"] = $message;
                                        }
                                    }
                                    /**
                                     * Se o usuário não configurou uma função com
                                     * uma regra, verifica regras de validação
                                     * do sistema
                                     */
                                    else if( method_exists("Validation", $vR["rule"]) ) {

                                        /**
                                         * Se não validou
                                         */
                                        if( !call_user_func("Validation::notEmpty", $valor) ){

                                            /**
                                             * Caso seja um model-filho no
                                             * relacionamento de models
                                             */
                                            if( $sub )
                                                $vE[$campo] = '1';
                                            else
                                                $vE[$model][$campo] = '1';

                                            /**
                                             * Session para formHelper
                                             */
                                            if( !empty($vR["message"]) )
                                                $message = $vR["message"];
                                            else if( !empty($vR["m"]) )
                                                $message = $vR["m"];
                                            else {
                                                if( isDebugMode() )
                                                    showWarning("Mensagem de validação do campo ".$campo." não especificada");
                                                $message = "Not validated!";
                                            }

                                            $_SESSION["Sys"]["FormHelper"]["notValidated"][$model][$campo]["message"] = $message;

                                        }

                                    } else {
                                        showError("Regra de validação ".$vR["rule"]." inexistente");
                                    }
                                }

                            }
                        } // fim foreach($campos)
                    }
                } // fim é o model atual
                /**
                 * Validação Model-Relacional
                 */
                else {

                    if( !$this->{$model}->validate( array($model=> $campos), true ) ){
                        $vE[$model] = 0;
                    }

                }
                

            }
            
        }

        /**
         * $vE: Validations Errors
         */
        /**
         * Se validou, retorna true
         */
        if( !empty($vE) ){
            return false;
        } else {
            return true;
        }
    }

    /**
     * 
     * MÉTODOS INTERNOS (PRIVATE)
     *
     */
    /**
     * Descreve as tabelas
     *
     * @global array $describedTables
     * @param array $params
     */
    private function describeTable($params = ""){
        $conn = ( empty($params["conn"]) ) ? $this->conn : $params["conn"];

        global $describedTables;
        /**
         * Retorna todos os campos das tabelas
         */
        $describeSql = 'DESCRIBE '.$this->useTable;

        foreach($conn->query($describeSql, "ASSOC") as $tabela=>$info){
            $this->tableDescribed[$info['Field']] = $info;
            $describedTables[ get_class($this) ][$info['Field']] = $info;
        }
    }
}
?>