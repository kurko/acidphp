<?php
/**
 * Arquivo que representa a estrutura MODEL de um MCV
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
     *
     * @var array Indica que este Model tem outros sub-Models
     */
    public $hasOne = array();
    public $hasMany = array();
    public $modelsLoaded = array();
    public $tableAlias = array();

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


    /**
     * __CONSTRUCT()
     *
     * @param array $params
     *      "conn" object : conexão com o db;
     */
    function  __construct($params = "") {
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
        if( !array_key_exists($this->useTable, $describedTables) ){
            $this->describeTable();
        } else {
            $this->tableDescribed = $describedTables[$this->useTable];
        }

        /**
         * CRIA RELAÇÕES
         */
        /**
         * hasOne
         */
        if( !empty($this->hasOne) ){
            foreach( $this->hasOne as $model=>$propriedades ){
                $this->{$model} = new $model($params);
                $this->modelsLoaded[] = $model;
            }
        }
        /**
         * hasMany
         */
        if( !empty($this->hasMany) ){
            foreach( $this->hasMany as $model=>$propriedades ){
                $this->{$model} = new $model($params);
                $this->modelsLoaded[] = $model;
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
         * SQLObject
         */
        $this->sqlObject = new SQLObject();
    }

    /**
     * MÉTODOS DE SUPORTE
     */
    /**
     * SAVEALL()
     *
     * @param array $data Dados enviados para salvar no DB
     * @param array $options
     * @return bool Se salvou ou não
     */
    public function saveAll($data, $options = array()){
        if( is_array($data) ){
            /**
             * Loop por cada tabela com valores enviados
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
                    $this->conn->exec($instrucao);
                    $lastInsertId = $this->conn->lastInsertId();

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

        return false;
    } // FIM SAVEALL()

    /**
     *
     * @param array $options Contém opçoes de carregamento
     *      - '' : 
     * @param string $mode Modo de retorno
     *      - 'all' (padrão) : Listagem completa
     * @return array
     */
    public function find($options, $mode = "all"){

        /**
         * CONFIGURAÇÕES DE RELACIONAMENTO
         */
        $options["tableAlias"] = $this->tableAlias;
        foreach( $options["tableAlias"] as $model=>$valor ){
            $options["models"][$model] = $this->{$model};
        }
        /**
         * Dfine model principal
         */
        $options["mainModel"] = $this;

        /**
         * GERA SQL
         *
         * Gera SQL com SQLObject
         */
        $sqlGerado = $this->sqlObject->find($options);
        $query = $this->conn->query( $sqlGerado, ASSOC );
        
        /**
         * Trata os dados para retornarem no formato adequado
         */
        $return = array();
        $registro = array();
        $i = 0;
        foreach($query as $chave=>$dados){
            /**
             * ANALISA RESULTADO DO DB
             */
            /*
             * Transforma o resultado em uma array legível. O resultado do
             * DB vem desformatado do SQL Object.
             */
            foreach( $dados as $campo=>$valor){
                $underlinePos = strpos($campo, "__" );
                if( $underlinePos !== false ){
                    /**
                     * Model e Campo
                     */
                    $modelReturned = substr( $campo, 0, $underlinePos );
                    $campoReturned = substr( $campo, $underlinePos+2, 100 );

                    /**
                     * Codigo
                     */
                    $tempResult[$i][$modelReturned][$campoReturned] = $valor;
                }
            }
            $i++;

        }

        /**
         * Monta estruturas de saída de acordo como o modo pedido
         *
         * O modo padrão é ALL conforme configurado nos parâmetros da função
         */
        foreach( $tempResult as $index ){
            // ID principal do model principal
            $mainId = $index[ get_class($this) ]["id"];

            $hasManyResult = array();

            
            foreach($index as $model=>$dados){

                /**
                 * Ajusta retorno da array de dados
                 */
                /**
                 * Se o model hasMany
                 */
                if( array_key_exists( $model , $this->hasMany) ){
                    $hasManyResult = $dados;
                    $registro[ $index[ get_class($this) ]["id"] ][$model][] = $hasManyResult;
                }
                /**
                 * Senão, simplesmente salva na array o resultado do model
                 */
                else {
                    $registro[ $index[ get_class($this) ]["id"] ][$model] = $dados;
                }
            }
            unset($hasManyResult);
            
        }

        $return = $registro;
        return $return;
















            //if(  )
            /**
             * ALL
             */
            if($mode == 'all'){
                //pr($dados);
                //array_push($return, $registro);
                //array_push($return, $dados);
            }
            /**
             * FIRST
             */
            elseif($mode == 'first' and (count($fields) == 1 or is_string($fields))){
                if(is_array($fields)){
                    $return[] = $dados[$fields[0]];
                } else {
                    $return[] = $dados[$fields];
                }
            }

    }// fim find()

    /**
     * MÉTODOS INTERNOS (PRIVATE)
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
            $describedTables[$this->useTable][$info['Field']] = $info;
        }
    }



}

?>