<?php
/**
 * Classe responsável pela abstração de bancos de dados
 *
 * @package Model
 * @name DatabaseAbstrator
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 19/07/2009
 */
class DatabaseAbstractor extends DataAbstractor
{
    /**
     * CONFIGURAÇÕES
     */

    /**
     * CONEXÃO
     *
     * @var object Contém a conexão com a base de dados
     */
    private $conn;
    

    /**
     * CONFIGURAÇÕES INTERNAS (PRIVATE)
     */
    /**
     * Array contendo as tabelas existentes na Base de Dados
     *
     * @var array
     */
    private $sqlObject;
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
        

        /**
         * Instancia sqlObject;
         */
        $this->sqlObject = new SQLObject();
    }

    /**
     * MÉTODOS DE SUPORTE
     */

    public function find($options){

        /**
         * Configuração inicial
         */
        /**
         * Models
         */
        $mainModel = $options["mainModel"];

        /**
         * VERIFICA $OPTIONS
         *
         * Faz verificações para saber que tipo de busca deve ser feita
         */
        /**
         * LIMIT
         * 
         * Verificação: Se SQL Limit definido
         */
        if( !empty($options["limit"]) ){
            /**
             * Desativa relacionamentos hasMany para buscá-los separadamente
             */
            $hasManyTemp = $mainModel->hasMany;
            $options["mainModel"]->hasMany = array();

            $sql = $this->sqlObject->select($options);

            /**
             * Carrega os dados da tabela principal
             */
            $query = array();
            if( is_array($sql) ){
                foreach( $sql as $sqlAtual ){
                    $result = $this->conn->query( $sqlAtual, ASSOC );
                    $query = array_merge( $query, $result );
                }
            }

            /**
             * Pega os ids dos resultados atuais
             */
            foreach($query as $campos){
                $mainIds[] = $campos[ get_class($mainModel)."__id" ];
            }

            /**
             * Recupera dados de relacionamento
             */

            $mainModel->hasMany = $hasManyTemp;
            foreach( $mainModel->hasMany as $model=>$properties ){
                $subOptions["mainModel"] = $mainModel->{$model};
                $subOptions["conditions"] = array(
                    'OR' => array(
                        $model.".".$properties["foreignKey"] => $mainIds,
                    )
                );
                $sql = array_merge($sql, $this->sqlObject->select($subOptions) );
            }

            //pr($query);
        }
        /**
         * LIMIT desligado
         */
        else {
            $sql = $this->sqlObject->select($options);
        }

        //return $sql;
        /**
         * RETURN
         * 
         * Retorna dados para Models
         */
        /**
         * Prepara $query se necessário
         */
        if( empty($query))
            $query = array();

        /**
         * Roda SQLs criado, verificando se são arrays
         */
        if( is_array($sql) ){
            foreach( $sql as $sqlAtual ){
                $result = $this->conn->query( $sqlAtual, ASSOC );
                $query = array_merge( $query, $result );
            }
        }
        //return $query;

        /**
         * FORMATA QUERY
         *
         * Trata os dados que vieram em formato cru do DB para um formato
         * mais tragável (adequado).
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
         * FORMATA VARIÁVEL LEGÍVEL E TRATÁVEL
         *
         * Monta estruturas de saída de acordo com o modo pedido
         *
         * O modo padrão é ALL conforme configurado nos parâmetros da função
         */
         $oo = 0;
        foreach( $tempResult as $index ){
            /**
             * ID principal do registro retornado do model principal
             */
            $mainId = $index[ get_class($mainModel) ]["id"];

            $hasManyResult = array();


            foreach($index as $model=>$dados){

                /**
                 * Ajusta retorno da array de dados para um formato legível
                 * e de melhor visualização para posterior tratamento.
                 */
                /**
                 * hasMANY
                 *
                 * Se o model pertence à categoria hasMany do Model pai
                 */
                if( array_key_exists( $model , $mainModel->hasMany) ){
                    $hasManyResult = $dados;
                    /**
                     * Se há valor na tabela filha
                     */
                    if( !empty($hasManyResult[ $mainModel->hasMany[$model]["foreignKey"] ]) )
                        $registro[ $hasManyResult[ $mainModel->hasMany[$model]["foreignKey"] ] ][$model][] = $hasManyResult;
                }
                /**
                 * Senão, simplesmente salva na array o resultado do model
                 */
                else if( array_key_exists( $model , $mainModel->hasOne) ) {
                    if( !empty($dados[ $mainModel->hasOne[$model]["foreignKey"] ]) )
                        $registro[ $index[ get_class($mainModel) ]["id"] ][$model] = $dados;
                } else {
                    $registro[ $index[ get_class($mainModel) ]["id"] ][$model] = $dados;
                }
                $oo++;
            }
            unset($hasManyResult);

        }
        echo $oo;
        return $registro;

    }
}
?>