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

    public $callController;
    public $callControllerClass;
    public $callAction;

    protected $routes;

    function __construct(){

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
    }

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

            if( in_array("/", $this->routes) ){
                $url[0] = (empty($this->routes["/"]["controller"])) ? "main" : $this->routes["/"]["controller"];
                $url[1] = (empty($this->routes["/"]["action"])) ? "main" : $this->routes["/"]["action"];

                if( !empty($url[0]) AND !empty($url[1]) ){
                    $this->defineRoutes($url);
                }
                
            }
        }
        //pr($url);


    }

    private function defineRoutes($url){
        /**
         * Controller
         */
        if( !empty($url[0]) ){
            $this->callController = $url[0];
            $this->callControllerClass = ucwords($this->callController);
        }
        /**
         * Action
         */
        if( !empty($url[1]) )
            $this->callAction = $url[1];
    }
}

?>