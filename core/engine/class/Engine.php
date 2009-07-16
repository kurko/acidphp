<?php
/**
 * CLASSE ENGINE
 *
 * 
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

    function __construct(){

        $this->translateUrl();
    }

    
    private function translateUrl(){
        
        if( !empty($_GET["url"]) ){
            $url = explode("/", $_GET["url"]);
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
        //pr($url);


    }
}

?>