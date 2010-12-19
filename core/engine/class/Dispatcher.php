<?php
/**
 * DISPATCHER
 *
 * Responsável por inicializar a configuração de todo
 * o sistema, carregando URLs, indicando Controller
 * e Actions e serem chamados.
 *
 * @package Core
 * @name Dispatcher
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 15/07/2009
 */
class Dispatcher
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

    /**
     *
     * @var string URL atual
     */
    public $url;

    public $conn;
    public $dbTables;

    public function __construct($params = ""){

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
        $this->conn = Connection::getInstance();
        /**
         * Verifica tabelas
         */
        if( $this->conn ){
            $this->checkTables( array(
                    'conn' => $this->conn
                )
            );
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
            $instance[0] = new Dispatcher(array());
        }

        return $instance[0];
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
			$url = '';
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
                //if( empty() )
                $this->defineRoutes($url);
				return true;
                $url[0] = ( empty($this->routes["/"]["controller"])) ? "site" : $this->routes["/"]["controller"];
                $url[1] = ( empty($this->routes["/"]["action"])) ? "index" : $this->routes["/"]["action"];
				
                if( !empty($url[0]) AND !empty($url[1]) ){
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
    public function checkTables($params = ""){
        /**
         * Admite uma conexão (configurada ou padrão)
         */
        $conn = ( empty($params["conn"]) ) ? $this->conn : $params["conn"];

        /**
         * Carrega todas as tabelas do DB
         */
        $mysql = $conn->query('SHOW TABLES', "BOTH");
        /**
         * Salva as tabelas encontradas
         * 
         * Loop por todas as tabelas do DB, salvando as informações sobre as
         * tabelas de forma organizada
         */
        foreach($mysql as $chave=>$dados){
            $this->dbTables[] = $dados[0];
			$this->conn->dbTables[] = $dados[0];
        }
    }

    /**
     * Define o controller e action a ser carregado a partir de uma URL
     *
     * @param array $url URL atual para definir qual controller carregar
     */
    public function defineRoutes($url){
		$sliceFromUrl = 2;
		
		$urlStr = '';
		if( !empty($url) )
			$urlStr = implode('/', $url);
		
		$extendedUrl = '/'.$urlStr;
		foreach( $this->routes as $pattern=>$def ){
			
			unset($lastLookup);
			$lastLookup = $pattern;
			$subject = str_replace("\\","\\\\",$extendedUrl);
			$pattern = str_replace("/","\/",$pattern);
			$pattern = str_replace(":controller",'(?P<controller>\w+)',$pattern);
			$pattern = str_replace(":action",'(?P<action>\w+)',$pattern);
			$pattern = str_replace(":arg",'(?P<arg>\w+(.*))',$pattern);
			
			preg_match('/'.$pattern.'/i', $subject, $matches);
			
			if( !empty($matches) )
				break;
		}
		
		/*
		 * Matches Routing Pattern
		 */
		if( !empty($matches) ){

			// Adjusts 'Controller'
			if( $this->routes[$lastLookup]['controller'] == ':controller' ||
			 	empty($this->routes[$lastLookup]['controller']) )
			{
				$this->callController = $matches['controller'];
			} else
				$this->callController = $this->routes[$lastLookup]['controller'];
			
			$this->callControllerClass = ucwords($this->callController);
			
			// Adjusts 'Action'
			if( $this->routes[$lastLookup]['action'] == ':action' ||
			 	empty($this->routes[$lastLookup]['action']) )
			{
				$this->callAction = $matches['action'];
			} else
				$this->callAction = $this->routes[$lastLookup]['action'];
			
			// When matching a pattern, it will slice off the number
			// of the matching elements
			
		}

		/*
		 * No matching pattern
		 */
        /**
         * APP_DIR e Controller
         */
		if( empty($this->callController) ){
	        if( !empty($url[0]) ){
	            $this->callController = $url[0];
	            $this->callControllerClass = ucwords($this->callController);
	        } else {
	            $this->callController = "site";
	        }
		}
        /**
         * Action
         */
		if( empty($this->callAction) ){
	        if( !empty($url[1]) ){
	            $this->callAction = $url[1];
	        }
	        /**
	         * Se não há actions chamado, pede por "index"
	         */
	        else {
	            $this->callAction = "index";
	        }
		}

        /**
         * Verifica o resto da URL por argumentos $_GET
         */
		$webrootTrash = '';
		if( !empty($url) )
			$webrootTrash = implode("/", $url);
		
        $this->webroot = str_replace( $webrootTrash, "", $_SERVER["REQUEST_URI"] );
        if( !defined( "WEBROOT"))
            define("WEBROOT", $this->webroot);

        /*
         * ANÁLISE DE APP_DIR
         *
         * Verifica qual aplicação está sendo requisitada.
         */
        $webRoot = array_reverse( array_values(array_filter( explode("/", WEBROOT) )) );

		$url = array();
		if( !empty($matches['arg']) )
			$url = explode("/", $matches['arg']);
			
        /**
         * URLS com argumentos com :
         */
            foreach( $url as $chave=>$valor ){
                $tmpArg = explode(":", $valor);
                if( count($tmpArg) > 1 ){
                    $url[$tmpArg[0]] = $tmpArg[1];
                    unset( $url[$chave] );
                }
            }

        /**
         * Finaliza tratamento de argumentos
         */
        $this->arguments = $url;
        $this->url = $_SERVER['REQUEST_URI'];
		
    }
}
?>