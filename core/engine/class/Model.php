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
        $this->params = $params["params"];

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
    }

    /**
     * MÉTODOS CRUD
     */
    /**
     * SAVEALL()
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
    public function saveAll($data, $options = array()){
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

                    if( $modelPai ){

                        if( in_array($tabela, $this->dbTables) ){
                            /**
                             * Loop por cada campo e seus valores
                             */
                            foreach( $campos as $campo=>$valor ){

                                if( array_key_exists($campo, $this->tableDescribed) ){
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
                                } else {
                                    showWarning("Campo inexistente configurado no formulário.");
                                }
                            }

                            if( !empty($camposStr) ){
                                $tempSql = "INSERT INTO
                                                ".$tabela."
                                                    (".implode(",", $camposStr).")
                                            VALUES
                                                ('".implode("','", $valorStr)."')
                                            ";
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
                        //$this->conn->exec($instrucao);
                        //$lastInsertId = $this->conn->lastInsertId();

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
                                $this->$model->SaveAll( $dataTemp );

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
                //pr($_SESSION);
                redirect($this->params["post"]["formUrl"]);
            }
        }

        return false;
    } // FIM SAVEALL()

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
         * CONFIGURAÇÕES DE RELACIONAMENTO
         */
        /**
         * Informações sobre tabelas dos models
         */
        $options["tableAlias"] = $this->tableAlias;
        //pr($options["tableAlias"]);
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
        $querysGerados = $this->databaseAbstractor->find($options);
        return $querysGerados;

    }// fim find()

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
     * MÉTODOS DE SUPORTE
     */
    /**
     * VALIDATE()
     *
     * Valida dados enviados em formulários automaticamente de acordo com regras
     * descritas nos respectivos Models.
     *
     * @param mixed $mixed Contém os dados para validação
     * @param bool $sub Para verificar Models recursivamente, não deve ser usado
     * externamente.
     * @return bool Se validar, retorna verdadeiro
     */
    public function validate($mixed, $sub = false){

        /**
         * Limpa Session
         */
        if( !$sub )
            unset($_SESSION["Sys"]["FormHelper"]["notValidated"]);


        $validationRules = $this->validation;
        $vE = array();

        if( is_array($mixed) ){


            foreach($mixed as $model=>$campos){

                if( $model == get_class($this) ){

                    if( !empty($validationRules) ){

                        foreach( $campos as $campo=>$valor ){

                            /**
                             * Se o campo possui regras de validação
                             */
                            if( array_key_exists($campo, $validationRules ) ){

                                //echo $model.'.'.$campo.$validationRules[$campo]["rule"];
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
     * MÉTODOS INTERNOS (PRIVATE)
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