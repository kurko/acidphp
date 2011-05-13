<?php

class App {

	var $appDirs = array(
	);
	
	var $appDir = "";
	
	public $url = '';

	function __construct(){
		
		if (!defined('THIS_PATH_TO_ROOT')) {
		    define('THIS_PATH_TO_ROOT', '');
		}
		
		if( empty($_SERVER['REQUEST_URI']) )
			return false;
		
		if( !empty($_GET['url']) )
			$this->url = $_GET['url'];

		$this->initialize();
	}
	
	/**
	 * initialize()
	 * 
	 * Este método é responsável pela inicialização da aplicação.
	 * 
	 * Primeiro ajusta as variáveis de sistema verificando qual é a aplicação
	 * atual. Depois consolida a aplicação atual.
	 * 
	 * Depois verifica na app atual o Routing. Uma app pode redirecionar para outra.
	 * 
	 * 
	 */
	function initialize(){
		
		/* Define endereços iniciais */
		$this->setWEBROOT();

		/* App padrão */
		$this->getAppDir();
		$_GET['url'] = $this->url;

	    include(CORE_CLASS_DIR."Dispatcher.php");
		$dispatcher = new Dispatcher;
		$dispatcher->app = $this->appDir;
		$dispatcher->appPublicDir = $this->_getSystemPublicDir();
		$dispatcher->initialize();

		if( defined('CORE_ENGINE_DIR') ){
			include(CORE_ENGINE_DIR.'app_engine.php');
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
            $instance[0] = new App;
        }

        return $instance[0];
    }

	function isAppDir($appDirs = ''){
		if( empty($appDirs) )
			return false;
			
		$pos = strpos($appDirs, '/');
		if( $pos == true ){
			$appDirs = reset( explode('/', $appDirs));
		}

		if( in_array($appDirs, $this->appDirs) ){
			return true;
		} else if( is_dir(THIS_PATH_TO_ROOT.$appDirs) ){
			if( is_dir(THIS_PATH_TO_ROOT.$appDirs.'/controller') &&
				is_dir(THIS_PATH_TO_ROOT.$appDirs.'/config')
				)
			{
				return true;
			}
		}
			
	}
	
	function _getSystemPublicDir(){
		$str = str_replace("index.php", "", getcwd() )."/";
		$str.= "../../".$this->appDir."/public/"; 
		return $str;
	}

	function _getScriptName(){
		$str = str_replace("/public/index.php", "", getcwd() );
		return $str;
	}

	function getCurrentAppDir(){
	    $scriptName = $this->_getScriptName();
	    $scriptNameDivided = array_reverse( explode("/", $scriptName ) );

	    $app = $scriptNameDivided[0];
		return $app;
	}

	function getRootDir(){
	    $root = str_replace('public/index.php', "", $this->_getScriptName() );
		$root.= '/'; // foi retirado $app de scriptname com barra. Aqui reinsere.
		DEFINE("ROOT", $root);
		return $root;
	}

	function setWEBROOT(){

	    if( empty($_GET["url"]) ){
	        $webRoot = $_SERVER["REQUEST_URI"];
	    } else {

	        if( is_string($_GET["url"]) ){
	            $url = $_GET["url"];
	        }

			
			$supposedlyApp = reset( explode("/", $url ) );
			if( $this->isAppDir( $supposedlyApp ) ){
		        $url = str_replace( $supposedlyApp.'/', "", $url );
				$this->appDir = $supposedlyApp;
			} else {
				$this->appDir = "app";
			}

			
			$this->url = $url;
			$webRootLength = strrpos($_SERVER["REQUEST_URI"], $url);
			$webRoot = substr_replace($_SERVER["REQUEST_URI"],'', $webRootLength, strlen($_SERVER["REQUEST_URI"]));

	    }
		
		if (!defined('WEBROOT')) {
	    	define("WEBROOT", $webRoot);
		}
		
		return $webRoot;
	}
	
	function getAppDir(){

		if( empty($this->appDir) ){
			$this->setWEBROOT();
		}

		if( empty($this->appDir) )
			$this->appDir = 'app';

       	$appDir = THIS_PATH_TO_ROOT .$this->appDir.'/';

		return $appDir;
	}

}
?>