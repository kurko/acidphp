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
         
        public $useTable;
        /**
         * Contém o id (ou mais de um em array) do campo objeto de ação.
         *
         * @var mixed
         */
        public $id;
        /**
         * Contém informações sobre os Behaviors que devem ser carregados.
         *
         * @var array 
         */
        public $actsAs = array();
    /*
     * INFORMAÇÕES DE TRANSAÇÕES
     */
    /**
     * lastInsertId
     *
     * @var int Contém o id do último campo inserido
     */
    var $lastInsertId;

    /**
     * CONFIGURAÇÕES INTERNAS
     */
        /**
         *
         * @var <string> Contém o nome do model atual
         */
        protected $modelName;
        /**
         * Contém os behaviors a serem usados por este model
         *
         * @var array
         */
        public $Behaviors;


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

        public $params;
        
        /**
         *
         * @var array Contém todos os dados organizados provenientes de forms
         */
        public $data;


    /**
     * __CONSTRUCT()
     *
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     * @since v0.1
     * @param array $params
     *      "conn" object : conexão com o db;
     */
    function  __construct($params) {
        // Seta o nome deste model
        $this->modelName = get_class($this);
        
        /**
         * RECURSIVE
         */
        $this->recursive = $params["recursive"];
        if( empty($params["currentRecursive"]) ){
            $this->currentRecursive = 0;
        } else {
            $this->currentRecursive = $params["currentRecursive"];
        }


        /**
         * CONFIGURAÇÃO DE AMBIENTE
         */
             /*
              * params
              */
            $this->params = &$params["params"];
            /*
             * $data
             */
            $this->data = &$params["data"];

        /**
         * CONEXÃO
         *
         * Configura a conexão com a base de dados
         */
        $this->conn = Connection::getInstance();
        $this->dbTables = ( empty($params["dbTables"]) ) ? array() : $params["dbTables"];
        
        /**
         * DEFINE A TABELA A SER USADA
         */
        if( !empty($params['useTable']) )
            $this->useTable = $params['useTable'];
        else if( empty($this->useTable) )
            $this->useTable = $params["modelName"];

        if( empty($this->useTable) ){
            showError("<em>useTable</em> não configurada em <em>".get_class($this)."</em>.");
        }

        /**
         * DESCRIBE NA TABELA
         *
         */
        $this->_describeTable();
        /*
        if( empty($describedTables) )
            $describedTables = array();
        if( !array_key_exists( get_class($this), $describedTables) ){
            $this->_describeTable();
        } else {
            $this->tableDescribed = $describedTables[ get_class($this) ];
        }
         * 
         */
        
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
                    include_once(APP_MODEL_DIR.$model.".php");
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
                    include_once(APP_MODEL_DIR.$model.".php");
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
                    include_once(APP_MODEL_DIR.$model.".php");
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
         if( $this->conn){
            $this->databaseAbstractor = new DatabaseAbstractor(array(
                    'conn' => $this->conn,
                )
            );
         }

       /**
        * BEHAVIORS
        */
        $this->_initBehaviors();

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
    public function save($data, $options = array()){

        if( is_array($data) ){

            /**
             * Sanitize os dados
             */
            $data = Security::Sanitize($data);

            $updateInstruction = false;

            $doUpdate = ( empty($options["doUpdate"]) ) ? false : $options["doUpdate"];

            unset( $_SESSION["Sys"]["FormHelper"]["notValidated"] );

            /**
             * VALIDATE
             */
            if( $this->validate($data) ){
                /**
                 * LOOP EM $DATA
                 *
                 * Loop por cada model dos valores enviados em $data. O formato
                 * padrão é
                 *
                 *      array(
                 *          [model] => array(
                 *              [campo] => valor
                 *          )
                 *      )
                 *
                 */
                foreach($data as $model=>$campos){

                    /**
                     * Verifica possíveis relacionamentos entre o model $this
                     * atual e o model atual de $data
                     */
                    if( get_class($this) == $model ){
                        
                        $tabela = $this->useTable;
                        $modelPai = true;

                        /**
                         * Verifica se este Model pertence a outro
                         */
                        /**
                         * @todo - verificar integridade
                         */
                         //pr($campos);
                        if( !empty($this->belongsTo) ){

                            /*
                             * Percorre cada Model a qual o Model atual ($this)
                             * pertence, verificando se um campo no formulário
                             * foi especificado.
                             *
                             * Se foi especificado, cria um índice em $data
                             * com o ID do model relacionado.
                             *
                             * Ex:
                             *      - Idade belongsTo Usuario
                             *      |
                             *      | Se o formulário é do model Idade e há um
                             *      | campo chamado Usuario.id, substitui por
                             *      | Idade.usuario_id
                             *
                             */
                            foreach( $this->belongsTo as $relationalModel=>$propriedades ){
                                if( array_key_exists($relationalModel, $data) 
                                    AND !empty($data[$relationalModel]["id"]) )
                                {
                                    $campos[ $propriedades["foreignKey"] ] = $data[$relationalModel]["id"];
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

                    /**
                     * MODEL PAI
                     *
                     * $data[model] == model objeto atual ?
                     *
                     * Verifica se o Model atual da array $data (está dentro de
                     * um foreach) é o mesmo que get_class($this) ou se é um
                     * model filho.
                     *
                     * [Se for o próprio Model pai]
                     *      Gera SQL para INSERT ou UPDATE posteriormente no
                     *      código
                     *
                     */
                    if( $modelPai ){
                        
                        /**
                         * subModel
                         *
                         * Se esta é uma chamada a um subModel, ve qual o
                         * registro deve ser modificado.
                         */
                        if( $doUpdate AND !empty($options["foreignKey"]) ){
                            $campoId = $options["foreignKey"]["id"];


                            /**
                             * Se o tipo de relação é hasMany, o campo
                             * foreignKey não deve ser a referência.
                             */
                            if( !empty($options["relationType"]) AND $options["relationType"] !== "hasMany" ){
                                $updateConditionField = $options["foreignKey"]["field"];
                            }
                            /**
                             * Se esta é uma chamada a um model:
                             *
                             * -> parent hasMany $this
                             */
                            else if( !empty($options["relationType"]) AND $options["relationType"] == "hasMany" ){

                                /**
                                 * VERIFICA FORMATO $DATA PARA HASMANY
                                 */
                                $dataItems = array_keys( $data[ get_class($this) ]);

                            }
                            
                            else {

                            }
                        } // fim chamada subModel

                        /*
                         * Se a tabela atual existe de fato.
                         */
                        if( in_array($tabela, $this->dbTables) ){
                            /**
                             * Loop por cada campo e seus valores para gerar uma
                             * string com os campos a serem incluidos.
                             */
                            foreach( $campos as $campo=>$valor ){
                                /*
                                 * Se o campo existe de fato na tabela
                                 * especificada.
                                 */
                                if( array_key_exists($campo, $this->tableDescribed) ){
                                    /**
                                     * Atualizando subModel (model filho)
                                     */
                                    if( $campo == "id" ){
                                        $doUpdate = true;
                                        $campoId = $valor;
                                        $updateConditionField = "id";
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
                                    
                                }
                                /*
                                 * O campo não existe
                                 */
                                else {

                                    /*
                                     * O campo não existe e não é um arquivo.
                                     */
                                    if( !$this->_isFileField( array($campo=>$valor) ) ){
                                        //showWarning("Campo inexistente configurado no formulário.");
                                    }
                                }
                            }

                            if( !empty($camposStr) ){
                                /**
                                 * @todo - comentar
                                 */

                                /**
                                 * com $doUpdate=true, verifica se realmente
                                 * deve ser feito um update, ou um insert
                                 */
                                if( $doUpdate
                                    AND !empty($updateConditionField) )
                                {

                                    //pr($campoId);
                                    if( $this->countRows( array("conditions" => array( get_class($this).".".$updateConditionField => $campoId) ) ) == 0 )
                                        $doUpdate = false;
                                }
                                
                                else if( empty($updateConditionField) ){
                                    $doUpdate = false;
                                }

                                /*
                                 * Flag que indica se é insert ou update
                                 */
                                $insertInstruction = false;
                                $updateInstruction = false;

                                if( !$doUpdate ){
                                    $insertInstruction = true;
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

                                    /**
                                     * Instrução de atualização.
                                     */
                                    $updateInstruction = true;

                                    $tempSql = "UPDATE
                                                    ".$tabela."
                                                SET
                                                    ".implode(",", $camposUpdate)."
                                                WHERE
                                                    ".$updateConditionField."='".$campoId."'
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
						    $debugMode = Config::read("debug");
						    if( Config::read("debug") > 0 OR empty($debugMode) ){
						        trigger_error( "Alguma tabela especificada não existe" , E_USER_ERROR);
						    }
                        }
                    } // fim modelFather==true

                } // fim criação de SQLs
                /**
                 * SALVA SQL CRIADO
                 *
                 * Executa o SQL do model principal
                 *
                 * Gera lastInsertId ao final
                 */
                /*
                 * Se há uma SQL gerado nesta execução
                 */
                if( !empty($sql) AND count($sql) > 0 ){
                    foreach( $sql as $instrucao ){
                        /**
                         * Salva dados na tabela deste Model
                         */
                        $this->conn->exec($instrucao);

                        unset( $_SESSION["Sys"]["addToThisData"] );
                        unset( $_SESSION["Sys"]["options"]["addToThisData"] );

                        if( $updateInstruction ){
                            if( !empty($data[get_class($this)]["id"]) )
                                $lastInsertId = $data[get_class($this)]["id"];
                            else
                                $lastInsertId = $data[get_class($this)][$updateConditionField];
                        } else {
                            $lastInsertId = $this->conn->lastInsertId();
                        }

                        //$modelsFilhos = array();
                    }
                }
                /*
                 * Não há um SQL criado. $lastInsertId é o id do model em
                 * $this->data.
                 */
                else {
                    $lastInsertId = $data[get_class($this)]["id"];
                }

                /*
                 * Guarda lastInsertId.
                 *
                 * Esta variável é útil para se saber qual o id foi salvo e pode
                 * ser acessada através de Model::lastInsertId
                 */
                $this->lastInsertId = $lastInsertId;

                /**
                 * Se há dados de models filhas (relacionais), envia dados para
                 * seus respectivos objetos ($model) para serem salvos
                 *
                 * [Se há models filhos com dados && lastInsertId existe]
                 *
                 */
                if( !empty($modelsFilhos) AND !empty($lastInsertId) ){

                    /**
                     * Loop por models filhos
                     */

                    foreach($modelsFilhos as $model=>$campos){
                        $dataTemp[$model] = $campos;

                        /**
                         * FOREIGNKEY
                         *
                         * Pega as chaves estrangeiras (foreignKey) dos models
                         * relacionados.
                         */
                        if( array_key_exists($model, $this->hasOne) ){
                            $foreignKey = $this->hasOne[$model]["foreignKey"];
                            $relationType = "hasOne";
                        } else if( array_key_exists($model, $this->hasMany) ){
                            $foreignKey = $this->hasMany[$model]["foreignKey"];
                            $relationType = "hasMany";
                        } else if( array_key_exists($model, $this->belongsTo) ){
                            $foreignKey = $this->belongsTo[$model]["foreignKey"];
                            $relationType = "hasBelongsTo";
                        } else if( array_key_exists($model, $this->hasAndBelongsToMany) ){
                            $foreignKey = $this->hasAndBelongsToMany[$model]["foreignKey"];
                            $relationType = "hasAndBelongsToMany";
                        }

                        /*
                         * CHAMA MODEL::SAVE() FILHOS
                         */
                        /*
                         * hasMany
                         *
                         */
                        if( $relationType == "hasMany" ){

                            foreach( $dataTemp[$model] as $chave=>$valor ){
                                if( is_int($chave) ){

                                    $dataForHasMany[$model] = $valor;
                                    $dataForHasMany[$model][ $foreignKey ] = $lastInsertId;

                                    if( $insertInstruction
                                        AND !empty($dataForHasMany[$model]["id"]) )
                                    {
                                        unset($dataForHasMany[$model]["id"]);
                                    }


                                    /**
                                     * Chama os models filhos - save()
                                     *
                                     * Envia dados para Models relacionados salvarem. É
                                     * passado [foreignKey] para que o model filho saiba
                                     * qual é o registro que deve ser mexido, e qual
                                     * o relacionamento existe.
                                     *
                                     */
                                    $this->{$model}->save( $dataForHasMany, array(
                                            "doUpdate" => $doUpdate,
                                            "relationType" => $relationType,
                                            "foreignKey" => array(
                                                "field" => $foreignKey,
                                                "id" => $lastInsertId,
                                            )
                                        )
                                    );
                                    
                                }
                            }
                            
                        }
                        /**
                         * Chama filhos normalmente (não hasMany)
                         */
                        else {
                            $dataTemp[$model][ $foreignKey ] = $lastInsertId;
                            /**
                             * Chama os models filhos - save()
                             *
                             * Envia dados para Models relacionados salvarem. É
                             * passado [foreignKey] para que o model filho saiba
                             * qual é o registro que deve ser mexido, e qual
                             * o relacionamento existe.
                             *
                             */
                            $this->{$model}->save( $dataTemp, array(
                                    "doUpdate" => $doUpdate,
                                    "relationType" => $relationType,
                                    "foreignKey" => array(
                                        "field" => $foreignKey,
                                        "id" => $lastInsertId,
                                    )
                                )
                            );
                        }

                        unset($dataTemp);
                    }
                }
                return true;
            }
            /**
             * NÂO VALIDOU
             */
            else {
                $_SESSION["Sys"]["addToThisData"][ $this->params["post"]["formId"] ] = $data;
                if( !empty($this->params["post"]["formUrl"]) ){
                    $_SESSION["Sys"]["options"]["addToThisData"][ $this->params["post"]["formId"] ]["destLocation"] = $this->params["post"]["formUrl"];
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
             * Formato $this->data passado
             */
            if( is_array($toUpdate) ){

                $has = array_merge($this->hasOne, $this->hasMany, array( get_class($this)=>"" ) );
                $updateAll = false;
                
                foreach( $toUpdate as $chave=>$campos ){

                    /**
                     * $this->data?
                     * 
                     * Verifica o formato
                     */
                    if( array_key_exists($chave, $has) ){

                        /**
                         * Percorre os campos
                         */
                        foreach( $campos as $campo=>$valor ){
                            $updateAllData[ $chave.".".$campo ] = $valor;
                        }

                        $updateAll = true;

                        /**
                         * @todo - Implementar
                         *
                         * este método deve conseguir atualizar usando
                         * $this->data.
                         *
                         * Se o dado é $this->data agora, simplesmente
                         * retorna false
                         */
                         return false;
                    }
                }
                
            }

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
        //if( is_array($options) AND empty($options["limit"]) )
            //$options["limit"] = "50";// Config::read("modelAutoLimit");


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

        if( !empty($this->conn) ){
            $querysGerados = $this->databaseAbstractor->find($options, $mode);
            return $querysGerados;
        }

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
        if( is_array($options) AND empty($options["limit"]) )
            $options["limit"] = Config::read("modelAutoLimit");

        if( array_key_exists("page", $this->params["args"]) ) {
        /**
         * Segurança contra URL injection
         */
            if( ($this->params["args"]["page"] * 1) > 0 ) {
                $options["page"] = $this->params["args"]["page"];
            }
        }

        if( !array_key_exists("page", $options) ) {
            $options["page"] = 1;
        }

        if( $options["page"] < 1 )
            $options["page"] = 1;


        $options["mainModel"] = $this;
        $options["tableAlias"] = $this->tableAlias;
        foreach( $options["tableAlias"] as $model=>$valor ){
            if( get_class($this) != $model ){
                $options["models"][$model] = $this->{$model};
            }
        }
        //pr( );

        $totalRows = $this->countRows($options);

        $startLimit = $options["limit"] * ($options["page"] - 1);

        /**
         * Se a página for maior do que o possível de amostragem (segurança
         * contra usuários).
         */
        if( $startLimit > $totalRows AND $totalRows > 0 ) {
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

        return $this->find($options, $mode);

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
    public function countRows( $options = array() ){
        //pr($options);
        /**
         * Retorna a quantidade total de registros do Model
         */
        if( empty($options) ){
            $count = $this->query("SELECT COUNT(*) as count FROM ".$this->useTable." AS ".get_class($this));
            return $count[0]["count"];
        }
        /**
         * Se $options for INT, significa que e o id de um registro e serve
         * basicamente para saber se ele existe no DB
         */
        else if( is_int($options) OR is_string($options) ){
            if( is_int($options) )
                $where = "id='".$options."'";
            else if( is_string($options) )
                $where = $options;

            $count = $this->query(  "SELECT COUNT(*) as count ".
                                    "FROM ".$this->useTable." ".
                                    "WHERE ".$where
                                );
            return $count[0]["count"];
        }
        /**
         * @todo - implementar
         *
         * Já foi feito para que o countRows use o SQL que o model principal
         * usa. Entretanto, deve ser criado um padrão de uso do método.
         */
        else if( is_array($options) ) {

            /*
             * Se é uma query customizada
             */
            if( !empty($options["conditions"] )
                OR !empty($options["limit"] )
                OR !empty($options["order"] )
                OR !empty($options["fields"] )
                OR !empty($options["join"] )
                )
            {


                /*
                if( is_array($options["conditions"]) ){
                    foreach( $options["conditions"] as $chave=>$valor ){
                        $condition[] = $chave."='".$valor."'";
                    }
                    $where = implode('AND', $condition);
                }
                $count = $this->query(  "SELECT COUNT(*) as count ".
                                        "FROM ".$this->useTable." AS ".get_class($this)." ".
                                        "WHERE ".$where
                                    );
                */
                if( !empty($options["limit"]) )
                    unset($options["limit"]);
                $options["fields"] = array('COUNT(*) as count');

				$options['mainModel'] = $this;
                $count = $this->query( $this->databaseAbstractor->generateSql($options) );
                return $count[0]["count"];
            }
            /**
             * @todo - implementar
             */
            else {
                $options["fields"] = array("COUNT(*) AS count");
                //pr($options);
                $count = $this->find($options);
            }
            
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

        /**
         * Inicializa variáveis
         */
        $validationRules = $this->validation;
        $vE = array();

        if( is_array($data) ){
            foreach($data as $model=>$campos){

                /*
                 * Model principal
                 */
                if( $model == get_class($this)
                    AND !empty($validationRules) )
                {

                    /**
                     * Campos de um formulário enviado
                     */
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
                             * Uma regra somente
                             */
                            if( array_key_exists("rule", $vR) ){
                                $allRules[] = $vR;
                            }
                            /**
                             * Mais de uma regra
                             */
                            else if( is_array($vR) ) {

                                foreach( $vR as $subRule ){
                                    if( array_key_exists("rule", $subRule) ){
                                        $allRules[] = $subRule;
                                    }
                                }

                            }

                            $paramsToValidate = array(
                                "valor" => $valor,
                                "model" => $this,
                                "campo" => $campo,
                                "vR"    => $vR,
                            );

                            /**
                             * Com todas as regras, faz loop validando
                             */
                            if( !empty($allRules) AND is_array($allRules) ){
                                foreach( $allRules as $rule ){

                                    /**
                                     * VALIDA DE FATO
                                     *
                                     * Verifica se funções de validação existem
                                     */
                                    /**
                                     * Função de validação pré-existente
                                     */
                                    if( is_string($rule["rule"])
                                        AND method_exists("Validation", $rule["rule"]) )
                                    {
                                        $result = call_user_func("Validation::".$rule["rule"], $valor);
                                    }
                                    /**
                                     * REGRA PERSONALIZADA (sem argumentos)
                                     *
                                     * Se a regra está configurada no Model
                                     */
                                    elseif( is_string($rule["rule"])
                                            AND method_exists($this, $rule["rule"]) )
                                    {
                                        $result = $this->{$rule["rule"]}($valor);
                                    }

                                    elseif( is_array($rule["rule"])
                                            AND method_exists("Validation", reset(array_keys($rule["rule"])) ) )
                                    {
                                        $result = call_user_func("Validation::".reset(array_keys($rule["rule"])), $valor, reset(array_values($rule["rule"])) );
                                    }

                                    elseif( is_array($rule["rule"])
                                            AND method_exists($this, reset(array_keys($rule["rule"]))) )
                                    {
                                        $result = $this->{reset(array_keys($rule["rule"]))}( $valor, reset(array_values($rule["rule"])) );
                                    }
                                    /**
                                     * Se o usuário não configurou uma função com
                                     * uma regra, verifica regras de validação
                                     * do sistema
                                     */
                                    else {
                                        if( is_array($rule["rule"]) )
                                            $inexistentRule = reset(array_keys($rule["rule"]));
                                        elseif( is_string($rule["rule"]) )
                                            $inexistentRule = $rule["rule"];

                                        showError("Regra de validação <em>".$inexistentRule."</em> do model <em>".get_class($this)."</em> inexistente");
                                    }

                                    /**
                                     * [Não validou]
                                     */
                                    if( !$result ){

                                        /*
                                         * Session para formHelper
                                         */
                                        /*
                                         * Pega mensagem
                                         */
                                        if( !empty($rule["message"]) )
                                           $message = $rule["message"];
                                        else if( !empty($rule["m"]) )
                                            $message = $rule["m"];
                                        else {
                                            if( isDebugMode() )
                                                showWarning("Mensagem de validação do campo ".$campo." não especificada");
                                            $message = "Not validated!";
                                        }

                                        /**
                                        * Caso seja um model-filho no
                                        * relacionamento de models
                                        */
                                        if( $sub )
                                            $vE[$campo] = '1';
                                        else
                                            $vE[$model][$campo] = '1';
                                            
                                        $_SESSION["Sys"]["FormHelper"]["notValidated"][$model][$campo]["message"] = $message;
                                    } // fim [não validou]

                                }
                            }

                            unset($allRules);
                        }
                    } // fim foreach($campos)

                } // fim é o model atual
                /**
                 * Não há regras, tudo ok
                 */
                elseif( empty($validationRules) ) {
                    /*
                     * Não faz nada :)
                     */
                }
                /**
                 * Validação Model-Relacional
                 */
                else {
                    if( !$this->{$model}->validate(
                            array($model=> $campos), true ) ){
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
    public function _describeTable($params = ""){
        $conn = Connection::getInstance();
        
        if( !empty($conn->connected) ){
            //global $describedTables;
            /**
             * Retorna todos os campos das tabelas
             */
            $describeSql = 'DESCRIBE '.$this->useTable;
            foreach($conn->query($describeSql, "ASSOC") as $tabela=>$info){
                $this->tableDescribed[$info['Field']] = $info;
                //$describedTables[ get_class($this) ][$info['Field']] = $info;
            }
        }

        return $this->tableDescribed;
    }

    /*
     *
     */
    public function _isFileField($params){

        if( is_array(reset($params) ) ){
            if( in_array("name", array_keys(reset($params)))
                AND in_array("type", array_keys(reset($params)))
                AND in_array("size", array_keys(reset($params)))
            )
            {
                return true;
            }
        }

        return false;
    }
    /**
     *
     * BEHAVIORS
     *
     *
     */

    /**
     * _initBehaviors()
     *
     * Inicializa e carrega todos os Behaviors deste model que estão
     * especificados em $this->actAs.
     *
     * Os formatos aceitáveis em $this->actsAs são:
     *
     *      1) $this->actsAs = array( 'nome_do_behavior', array($options) );
     *      2) $this->actsAs = array( 'nome_do_behavior' );
     *
     * O segundo método não apresenta opções ou configurações extras.
     *
     */
    public function _initBehaviors(){
        if( is_array($this->actsAs) ){

            /*
             * Toma o nome de todos os behaviors anexados
             */
            $behaviorsToLoad = $this->actsAs;

            /*
             * Instancia todos os behaviors anexados
             */
            foreach( $behaviorsToLoad as $behaviorName=>$behaviorConfig ){

                /*
                 * Dependendo do formato que o usuário especificar o Behavior,
                 * chama $this->attach de uma forma diferente.
                 */
                if( !is_string($behaviorName) || is_int($behaviorName) ){
                    $behaviorName = $behaviorConfig;
                    $behaviorConfig = array();
                }

                $this->attach($behaviorName, $behaviorConfig);

            }
        }

        return true;
    }

    /**
     * attach()
     *
     * Anexa um Behavior ao Model
     *
     * @param string $behaviorName O nome do behavior
     * @param array [$config] As configurações do behavior
     */
    public function attach($behaviorName, $config = array()){

        /*
         * Inclui a classe do behavior
         */
        if( is_file(CORE_BEHAVIOR_DIR.$behaviorName.'.php') ){
            include_once CORE_BEHAVIOR_DIR.$behaviorName.'.php';
        } else if( is_file(APP_BEHAVIOR_DIR.$behaviorName.'.php') ){
            include_once APP_BEHAVIOR_DIR.$behaviorName.'.php';
        }

        /*
         * Ajusta nome para instanciação
         */
        $behaviorObjectName = $behaviorName . BEHAVIOR_CLASSNAME_SUFFIX;
        
        /*
         * Instancia Behavior
         */
        /**
         * @todo - $this->Behaviors existe no behavior instanciado abaixo,
         * sendo recursivo.
         */
        $this->Behaviors->{$behaviorName} = new $behaviorObjectName($this);
        $this->{$behaviorName} = &$this->Behaviors->{$behaviorName};

        if( is_string($behaviorName) && is_array($config) ){
            $this->actsAs[$behaviorName] = $config;
        }
    }

    /**
     * detach()
     *
     * Desanexa um behavior
     *
     * @param string $behaviorName Nome do Behavior que será desanexado
     * @return bool
     */
    public function detach($behaviorName){
        if( isset($this->actsAs[$behaviorName]) ){
             unset($this->actsAs[$behaviorName]);
             unset($this->{$behaviorName});
        }
        return true;
    }

    /**
     *
     *
     * ALIASES
     *
     *
     */
    /**
     * Alias para attach
     * 
     */
    public function loadBehavior($behaviorName , $config = array()){
        $this->attach($behaviorName, $config);
    }

}
?>