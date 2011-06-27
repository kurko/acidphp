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
 	public $app = '';
 	public $appPublicDir = '';
	 /**
	  *
	  * @var string Qual App deve ser chamada
	  */
	 public $callApp;
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
	public $routes = array();

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
	}
	
	function initialize(){
		include "../../".$this->app."/config/routes.php";
        $this->routes = $routes;
		
        /**
         * URL
         *
         * Verifica URL e decifra que controller
         * e action deve ser aberto.
         */
        $this->translateUrl();
		
#		print_r($this->callApp);

		if( empty($this->callApp) )
			$this->callApp = $this->app;
		// routing sends to a different app?

		
		if( $this->app != $this->callApp &&
			is_dir("../../".$this->callApp."/config/") )
		{
			include "../../".$this->callApp."/config/routes.php";
			
	        $this->routes = $routes;
			$this->app = $this->callApp;
	        $this->translateUrl();
		}
		
		if (!defined('APP_DIR')) {
        	define("APP_DIR", THIS_PATH_TO_ROOT.$this->app."/");
		}

		$this->setAbsoluteWebroot();
        
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
	
	public function setAbsoluteWebroot(){
	    /*
	     * WEBROOT_ABSOLUTE
	     *
	     * Esta url contém SEMPRE o app atual ao final. Assim, é possível salvar
	     * imagens com path absoluto, entre outras funcionalidades.
	     *
	     * Ex.:
	     *
	     *      /myfolder/anotherfolder/app/
	     */
	        /*
	         * O último diretório antes do controller é o mesmo da app
	         */
			$separatedWebroot = array_reverse( explode('/', WEBROOT) );
			$separatedWebroot = array_filter( $separatedWebroot );
			$webAppRoot = reset( $separatedWebroot );

	        if( !empty( $webAppRoot )
	            AND $webAppRoot == $this->app )
	        {
	            $absoluteWebroot = WEBROOT;
	        }
	        /*
	         * O último diretório antes do controller é diferente da app. Isto acontece
	         * quando não se digita o app atual. O padrão é carregar app/
	         */
	        else if( !empty( $webAppRoot )
	            AND $webAppRoot != $this->app )
	        {
	            $absoluteWebroot = WEBROOT.$this->app."/";
	        }
	        /*
	         * Se estamos no root do servidor (/), então vemos a app atual.
	         */
	        else if( WEBROOT == "/" ){
	            $absoluteWebroot = "/".$this->app."/";
	        }
	        /*
	         * Nenhuma alternativa acima
	         */
	        else {
	            $absoluteWebroot = WEBROOT;
	        }
			$absoluteWebroot = str_replace("//", "/", $absoluteWebroot);
			$absoluteWebroot = str_replace("//", "/", $absoluteWebroot);
            define( "WEBROOT_ABSOLUTE", $absoluteWebroot );
	}

    /**
     * Traduz a URL para que seja possível carregar os controllers e actions
     * certos.
     */
    public function translateUrl(){
        
        if( !empty($_GET["url"]) ){
			$url = $_GET["url"];
			$this->getUrl = $url;
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
     * Define o controller e action a ser carregado a partir de uma URL
     *
     * @param array $url URL atual para definir qual controller carregar
     */
    public function defineRoutes($url){
		$sliceFromUrl = 2;
		
		if( !is_array($url) ){
			$stringUrl = $url;
            $url = explode("/", $stringUrl);
		}
		
		if( is_file( $this->appPublicDir.$stringUrl ) ){
			$file = $this->appPublicDir.$stringUrl;
			$separaredFileString = explode(".", $file);
			$separaredFileString = array_reverse($separaredFileString);
			$ext = reset( $separaredFileString );
			
			if( $ext == 'css' ) header('Content-type: text/css');
			else if( $ext == 'js' ) header('Content-type: application/js');
			readfile($file);
			exit();
		}
		
		if( empty($this->routes) ){
			return false;
		}

		$urlStr = '';
		if( !empty($url) )
			$urlStr = implode('/', $url);
		
		$matches = array();
		
		$hasSlashAtTheBeginning = strpos($urlStr, '/');
		$extendedUrl = $urlStr;
		if( $hasSlashAtTheBeginning !== 0 ){
			$extendedUrl = '/'.$extendedUrl;
		}

		$extendedUrl = preg_replace('{/$}', '', $extendedUrl);
		
		$subject = str_replace("\\","\\\\",$extendedUrl);
		
		if( empty($subject) )
			$subject = "/";

		foreach( $this->routes as $pattern=>$def ){
			
			unset($lastLookup);
			$lastLookup = $pattern;
			$pattern = str_replace("/","\/",$pattern);
			$pattern = str_replace(":app",'(?P<app>\w+)',$pattern);
			$pattern = str_replace(":controller",'(?P<controller>\w+)',$pattern);
			$pattern = str_replace(":action",'(?P<action>\w+)',$pattern);
			$pattern = str_replace(":arg",'(?P<arg>\w+(.*))',$pattern);
			
			$ending = "$";
			$beginning = "^";
			
			if( $pattern == "\/" ){
				$ending = "";
				$beginning = "";
			}

			$pattern = $beginning.$pattern.$ending;
			preg_match('/'.$pattern.'/i', $subject, $match);

			if( !empty($match) ){
				$matches[$lastLookup] = $match;

				if( array_key_exists('stop', $def) ){
					if( $def['stop'] === true )
						break;
				}
			}
			
			unset($match);
			
		}

		$args = array();
		
		/*
		 * Matches Routing Pattern
		 */
		if( !empty($matches) ){

			foreach( $matches as $key=>$match ){
				
				// App
				if( (
						array_key_exists("app", $match) ||
						!empty($this->routes[$key]['app'])
					) &&
				 	empty($this->callApp) )
				{

					// Adjusts 'App', if any
					if( empty($this->routes[$key]['app']) ||
					 	$this->routes[$key]['app'] == ':app' )
					{
						$this->callApp = $match['app'];
					} else {
						$this->callApp = $this->routes[$key]['app'];
					}
			
					$this->callApp = strtolower($this->callApp);
				}

				// Controller
				if( ( 
						array_key_exists("controller", $match) || 
						!empty($this->routes[$key]['controller'])
					) &&
				 	empty($this->callController) )
				{

					if( empty($this->routes[$key]['controller']) ||
					 	$this->routes[$key]['controller'] == ':controller' )
					{
						$this->callController = $match['controller'];
					} else
						$this->callController = $this->routes[$key]['controller'];
			
					$this->callControllerClass = ucwords($this->callController);
				
				}
			
				// Action
				if( (
						array_key_exists("action", $match) ||
						!empty($this->routes[$key]['action'])
					) &&
				 	empty($this->callAction) )
				{

					if( empty($this->routes[$key]['action']) ||
					 	$this->routes[$key]['action'] == ':action' )
					{
						$this->callAction = $match['action'];
					} else
						$this->callAction = $this->routes[$key]['action'];
				}

				// Arguments
				if(
						array_key_exists("arg", $match) ||
						!empty($this->routes[$key]['arg'])
					)
				{

					if( empty($this->routes[$key]['arg']) ||
					 	$this->routes[$key]['arg'] == ':arg' )
					{
						$args[] = $match['arg'];
					} else
						$args[] = $this->routes[$key]['arg'];
				}
				// check for numeric key arguments
				foreach( $this->routes[$key] as $routeKey => $routeValue ){
					if( is_numeric($routeKey) ){
						$args[] = $routeValue;
					}
				}
				
				// When matching a pattern, it will slice off the number
				// of the matching elements
			}
		}
		/*
		 * No matching pattern
		 */
		
        /*
         * DEFAULT Controller
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
         * DEFAULT Action
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
		if( !empty($args) ){
			
			foreach( $args as $argString ){
				$tmpArgs[] = $this->getStringFromArray( $argString );
			}
			
			$args = implode("/", $tmpArgs);

			$url = explode("/", $args);
		}
			
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
	function getStringFromArray($array){
		if( is_string($array) )
			return $array;
		
		return implode('/', $array);
	}
	
}


?>