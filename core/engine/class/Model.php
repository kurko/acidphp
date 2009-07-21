<?php
/**
 * Arquivo que representa a estrutura controller de um MV
 *
 * @package MVC
 * @name Controller
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.1
 * @since v0.1.5, 22/06/2009
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
    protected $hasOne = array();
    protected $hasMany = array();

    /**
     * CONEXÃO
     *
     * @var object Contém a conexão com a base de dados
     */
    private $conn;
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
         * TABELA A SER USADA
         */
        $this->useTable = ( empty($this->useTable) ) ? $params["modelName"] : $this->useTable;

        /**
         * DESCRIBE NA TABELA
         */
        $this->describeTable();
        //pr( $this->tableDescribed);

        /**
         * CRIA RELAÇÕES
         */
        /**
         * hasOne
         */
        if( !empty($this->hasOne) ){
            foreach( $this->hasOne as $model=>$propriedades){
                $this->{$model} = new $model($params);
            }
        }
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
        pr($data);
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
    }


    /**
     * MÉTODOS INTERNOS (PRIVATE)
     */

    private function describeTable($params = ""){
        $conn = ( empty($params["conn"]) ) ? $this->conn : $params["conn"];

        /**
         * Retorna todos os campos das tabelas
         */
        $describeSql = 'DESCRIBE '.$this->useTable;

        foreach($conn->query($describeSql, "ASSOC") as $tabela=>$info){
            $this->tableDescribed[$info['Field']] = $info;
        }
    }



}

?>