<?php
/**
 * Arquivo responsável pela Conexão com Bases de Dados
 *
 * Integra PDO (se presente) ou conexão normal.
 *
 * ATENÇÃO:
 *      - Se você encontrar erro de 'mysql unbuffered', erro 2014, isto significa
 *        que você precisa liberar a memória após as querys que você fez.
 *        Faça isto através do método PDOStatement::closeCursor();
 *
 * @package DB
 * @name Conexao
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 15/07/2009
 */
class Connection {
    /**
     *
     * @var bool Conectado?
     */
    public $connected = false;
    /**
     *
     * @var bool Se a base de dados existe. Serve para verificação simples.
     */
    public $DBExiste;
    /**
     *
     * @var Resource Possui a conexão com o DB
     */
	public $conn;
    /**
     *
     * @var <type> ????????????????????????
     */
    private $db;

	public $usingPdo;

    /**
     *
     * @var <array> Contém toda a configuração de acesso à base de dados
     */
    protected $dbConfig;

    private $debugLevel = 0;

	public $describedTables = array();


    /**
     * Cria conexão com o DB. Faz integração de conexões se PDO existe ou não.
     *
     * @param array $conexao Contém parâmetros de conexão ao DB
     */
    function __construct($dbConfig = ''){
            
        $this->dbConfig = DATABASE_CONFIG::$default;

        if( !empty($this->dbConfig) ){
            /**
             * Se a extensão PDO, usada para conexão com vários tipos de bases de dados
             */
            if($this->PdoExtension()){
                $this->PdoInit($this->dbConfig);
                if($this->debugLevel == 1){
                    $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                }
            }
            /**
             * conexão comum
             */
            else {
                $this->DbConnect($this->dbConfig);
            }
        }
    }

    /**
     * getInstance()
     *
     * @staticvar <object> $instance
     * @return <object>
     */
    public function getInstance(){
        static $instance;

        if( empty($instance[0]) ){
            $instance[0] = new Connection;
        }

        return $instance[0];
    }

    /**
     * Efetua conexão via PDO.
     * 
     * Esta função é executada somente se a extensão 'PDO' estiver ativada.
     *
     * @param array $dbConfig Possui dados para conexão no DB
     *      driver: tipo de driver/db (mysql, postgresql, mssql, oracle, etc)
     *      database: nome da base de dados
     *      server: endereço da base de dados
     *      username: nome de usuário para acesso ao db
     *      password: senha para acesso ao db
     */
    protected function PdoInit($dbConfig){

        $dbConfig['driver'] = (empty($dbConfig['driver'])) ? 'mysql' : $dbonfig['driver'];
        $charset = ( empty($dbConfig["encoding"])) ? "" : ";charset=".$dbConfig["encoding"];
		
		$this->usingPdo = true;
		
        try {
            $this->conn = new PDO(
                            $dbConfig['driver'].':host='.$dbConfig['server'].';'
                            .   'dbname='.$dbConfig['database']
                            .   $charset,

                            $dbConfig['username'], $dbConfig['password'],
                            array(PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true)
                                );
        } catch (Exception $e){
            if( isDebugMode() ){
                echo $e->getMessage();
            }
            exit ();
        }

        $this->connected = true;

        /**
         * Ajusta charset no Banco de dados
         */
        if( !empty($dbConfig["encoding"]) ){
            $this->conn->exec("SET NAMES '".$dbConfig["encoding"]."'");
            $this->conn->exec("SET character_set_connection=".$dbConfig["encoding"]);
            $this->conn->exec("SET character_set_client=".$dbConfig["encoding"]);
            $this->conn->exec("SET character_set_results=".$dbConfig["encoding"]);
        }
        if( $this->conn){
            $this->DBExiste = true;
        }

        //$this->con = $dbConfig[]':host=localhost;dbname=test';
    }
    /**
     * conexão comum ao DB. Padrão MySQL.
     *
     * @param array $dbConfig Possui dados para conexão no DB
     *      driver: tipo de driver/db (mysql, postgresql, mssql, oracle, etc)
     *      database: nome da base de dados
     *      server: endereço da base de dados
     *      username: nome de usuário para acesso ao db
     *      password: senha para acesso ao db
     */
    protected function DbConnect($dbConfig){
        $conexao = $dbConfig;
		$this->usingPdo = false;

        $conn = mysql_connect($conexao['server'], $conexao['username'], $conexao['password']) or die('Erro ao encontrar servidor');
        if(mysql_select_db($conexao['database'], $conn)){
            $this->DBExiste = TRUE;
            $this->db = $db;
            $this->conn = $conn;
            $this->connected = true;
        } else {
            $this->DBExiste = FALSE;
        }
    }

	/**
	 * destroy()
	 *
	 * Destrói uma conexão atual
	 */
	public function destroy(){
		
		if( $this->usingPdo ){
			$this->conn = null;
		} else {
			
		}
		
		return true;
	}

    /**
     * CRUD
     *
     * Funções de leitura e escrito no banco de dados
     */
    /**
     * crud()
     *
     * Função integradora para Query's. Verifica se deve ser rodado PDO::query()
     * ou PDO::exec()
     *
     * @param mixed $sql
     * @param array $options
     *      Contém opções de execução CRUD
     *          - 'type': Tipo de dados retornados(ex.:PDO::FETCH_ASSOC)
     * 
     * @return mixed Dados retornados do banco de dados
     */
    public function crud($sql, $options = array("type"=>PDO::FETCH_ASSOC) ){

        /**
         * Padrão de amostragem é FETCH_ASSOC
         */
        if( empty($options["type"]) ){
            $options["type"] = PDO::FETCH_ASSOC;
        }
        /**
         * SQL == string
         */
        if( is_string($sql) ){

            /**
             * SELECT, INSERT, UPDATE, DELETE?
             *
             * Verifica que tipo de instrução SQL é esta
             */
                $instructionType = strpos($sql, " ");
                $sqlType = trim( substr( $sql, 0, $instructionType ) );
            /**
             * Tipo de instrução SQL, se deve retornar dados ou não
             */
                if( in_array( strtoupper($sqlType), array(
                                                    "SELECT",
                                                    "SHOW",
                                                )
                )){

                    return $this->query($sql, $options["type"]);

                } else {
                    return $this->exec($sql);
                }

        }
        /**
         * @todo - implementar query de sql array
         */
        else if( is_array($sql) ) {
            
        }


    } // fim crud()

    /**
     * QUERY()
     *
     * Executa comandos no banco de dados. Usando PDO, serve para SQLs que devem
     * retornar dados, como SELECT.
     *
     * @param string $sql
     * @return array Resultado em formato array
     */
    public function query($sql, $type = ""){
        /**
         * Timer init
         */
        $sT = microtime(true);

        /**
         * PDO
         *
         * Se a extensão PDO está ativada
         */
        if( $this->PdoExtension() ){
            /**
             * Se o resultado deve ser num formato diferente
             */

            $query = $this->conn->prepare($sql);

            /**
             * Se há resultados carregados das tabelas
             */
            $query->execute();

            if( $type == "" OR strtolower($type) == "assoc" ){
                $type = PDO::FETCH_ASSOC;
            } else if( $type == "BOTH" ) {
                $type = PDO::FETCH_BOTH;
            } else {
                $type = $type;
            }
            $result = $query->fetchAll($type);

            /**
             * Fecha cursor do PDO
             */
            if( !empty($query) AND empty($result) ){

                foreach($query as $valor){
                    $result[] = $valor;
                }
                $query->closeCursor();
            }
            $eT = microtime(true);
            
        } else {
            $mysql = mysql_query($sql);
            
            while($dados = mysql_fetch_array($mysql)){
                $result[] = $dados;
            }
        }

        /**
         * Timer Init
         */
        $eT = ( empty($eT) ) ? microtime(true) : $eT;

        Config::add("SQLs", array("sql" => $sql, "time" => $eT - $sT) );

        /**
         * Se não houverem resultados, instancia variável para evitar erros
         */
        if(empty($result)){
            $result = array();
        }
        return $result;
    }

    /**
     * Comando específico para uso com PDO. Se PDO não está presente,
     * chama $this->query.
     *
     * @param string $sql
     * @return <type> Retorna resultado da operação
     */
    public function exec($sql, $mode = ''){

        /**
         * Timer init
         */
        $sT = microtime(true);
        /**
         * Se a extensão PDO está ativada
         */
        if($this->PdoExtension()){
            /**
             * Executa e retorna resultado
             */
            
            $result = $this->conn->exec($sql);
            
            /**
             * Quando executado CREATE TABLE, retorno com sucesso é 0 e
             * insucesso é false, não sendo possível diferenciar entre um e
             * outro. Este hack dá um jeitinho nisto.
             */
            if( in_array( $mode, array('CREATE_TABLE', 'CREATE TABLE') ) ){
                if($result == 0 AND !is_bool($result)){
                    $result = 1;
                } else {
                    $result = false;
                }
            }
            //return $result;
        } else {
            $return = mysql_query($sql);
        }

        /**
         * Timer Init
         */
        $eT = microtime(true);

        Config::add("SQLs", array("sql" => $sql, "time" => $eT - $sT) );

        return $result;

    }

    /**
     * @todo - comentar
     *
     * @param <type> $sql
     * @return <type>
     */
    public function count($sql){
        /**
         * Se a extensão PDO está ativada
         */
        if($this->PdoExtension()){
            /**
             * Executa e retorna resultado
             */
            
            $mysql = $this->conn->prepare($sql);
            $mysql->execute();

            $total = $mysql->rowCount();
            $mysql->closeCursor();

            return $total;
        } else {
            /**
             * @todo Usar $this->query
             */
            $mysql = mysql_query($sql);
            return mysql_num_rows($mysql);
        }
    }

    /**
     * @todo - comentar
     */
    public function lastInsertId(){
        return $this->conn->lastInsertId();
    }

    /**
     * Retorna a lista de tabelas existentes
     *
     * @param string $db Banco de dados
     * @return array Retorna a lista de tabelas
     */
    public function listaTabelasDoDBParaArray($db = '' ){

        /**
         * Ajusta o DB (se nenhum especificado, usa o padrão do sistema)
         */
        $db = ( empty($db) ) ? $this->dbConfig['db'] : $db;

        /**
         * SQL para mostrar as tabelas da base de dados selecionada
         */
        $sql = "SHOW TABLES";

        /**
         * Carrega as tabelas, verifica a quantidade e ajusta uma array com elas
         * para retornar.
         */
        $query = $this->query($sql);
        $qntd_tabelas = count($query);
        if($qntd_tabelas > 0){
            foreach ( $query as $chave=>$valor ){
                $arraytmp[] = $valor[0];
            }
        }
        return $arraytmp;

    }

    /**
     * Retorna array com os campos da tabela selecionada
     *
     * @param string $tabela
     * @return array Array contendo todos os campos da tabela escolhida,
     * juntamente com informações como tipo e chaves.
     */
    public function listaCampos( $tabela ){
        $sql = "DESCRIBE ". $tabela;

        $query = $this->query($sql);

        /**
         * Loop pelos campos ajustando para o português os índices da array
         * de retorno.
         */
        foreach($query as $chave=>$valor){
            $query[$chave]['campo'] = $valor['Field'];
            $query[$chave]['tipo'] = $valor['Type'];
        }
        
        return $query;

    }

    /**
     * VERIFICAÇÕES INTERNAS
     */
    /**
     *
     * @return bool A extensão PDO está ativa ou não
     */
    protected function PdoExtension(){
        /**
         * Se a extensão PDO está ativada ou não
         */
        //return false;
        return extension_loaded('PDO');
    }

    public function testarConexao(){
        return 'Funcionando!';
    }
}

?>