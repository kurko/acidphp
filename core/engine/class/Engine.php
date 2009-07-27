<?php
/**
 * CLASSE ENGINE
 *
 * Responsável por inicializar a configuração de todo
 * o sistema, carregando URLs, indicando Controller
 * e Actions e serem chamados.
 *
 * @package Core
 * @name Engine
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 15/07/2009
 */
class Engine
{
    /**
     * CONTROLLERS
     */
    /**
     *
     * @var string Qual Controller deve ser chamado
     */
    public $callController;
    /**
     *
     * @var string Qual o nome da classe de controller que deve ser chamada
     */
    public $callControllerClass;
    /**
     *
     * @var string Qual Action deverá ser chamada
     */
    public $callAction;
    /**
     *
     * @var array Contém os routes atuais
     */
    protected $routes;
    public $arguments;
    public $webroot;


    public $conn;
    public $dbTables;

    function __construct($params = ""){

        /**
         * Carrega os routes do sistema
         */
        $this->routes = Config::read("routes");
        /**
         * URL
         *
         * Verifica URL e decifra que controller
         * e action deve ser aberto.
         */
        $this->translateUrl();


        /**
         * AJUSTA CONEXÃO SE EXISTIR
         */
        $this->conn = ( empty($params["conn"]) ) ? NULL : $params["conn"];
        /**
         * Verifica tabelas
         */
        $this->checkTables( array(
                'conn' => $this->conn
            )
        );
    }

    /**
     * Traduz a URL para que seja possível carregar os controllers e actions
     * certos.
     */
    private function translateUrl(){
        
        if( !empty($_GET["url"]) ){
            $url = explode("/", $_GET["url"]);

            $this->defineRoutes($url);
        }
        /**
         * Se a URL está vazia, carrega o Routes e
         * analisa o que (controller, action) deve
         * ser carregado
         */
        else {
            /**
             * URL PADRÃO
             *
             * Analisa arquivo routes.php e verifica qual
             * é a url padrão a ser aberta.
             */
            if( array_key_exists("/", $this->routes) ){

                /**
                 * Ajusta Controllers e Actions a serem carregados
                 */
                $url[0] = (empty($this->routes["/"]["controller"])) ? "site" : $this->routes["/"]["controller"];
                $url[1] = (empty($this->routes["/"]["action"])) ? "index" : $this->routes["/"]["action"];

                if( !empty($url[0]) AND !empty($url[1]) ){
                    $this->defineRoutes($url);
                }
                
            }
        }
    }

    /**
     * Verifica quais as tabelas existentes na base de dados.
     *
     * É usado SHOW_TABLES
     *
     * @param array $params Configurações adicionais
     *      - 'conn' [opcional] : objeto conexão
     */
    protected function checkTables($params = ""){
        /**
         * Admite uma conexão (configurada ou padrão)
         */
        $conn = ( empty($params["conn"]) ) ? $this->conn : $params["conn"];

        /**
         * Carrega todas as tabelas do DB
         */
        $mysql = $conn->query('SHOW TABLES');
        /**
         * Salva as tabelas encontradas
         * 
         * Loop por todas as tabelas do DB, salvando as informações sobre as
         * tabelas de forma organizada
         */
        foreach($mysql as $chave=>$dados){
            $this->dbTables[] = $dados[0];
        }
    }

    /**
     * Define o controller e action a ser carregado a partir de uma URL
     *
     * @param array $url URL atual para definir qual controller carregar
     */
    private function defineRoutes($url){
        /**
         * Controller
         */
        if( !empty($url[0]) ){
            $this->callController = $url[0];
            $this->callControllerClass = ucwords($this->callController);
        } else {
            $this->callController = "site";
        }
        /**
         * Action
         */
        if( !empty($url[1]) ){
            $this->callAction = $url[1];
        }
        /**
         * Se não há actions chamado, pede por "index"
         */
        else {
            $this->callAction = "index";
        }

        /**
         * Verifica o resto da URL por argumentos $_GET
         */
        //echo '0'.implode("/", $url).'0';
        $this->webroot = str_replace( implode("/", $url), "", $_SERVER["REQUEST_URI"]);
        define("WEBROOT", $this->webroot);
        array_shift($url);
        array_shift($url);
        $this->arguments = $url;

    }
}

?>