<?php
/**
 * HELPER
 *
 * Classe que contém funções nativas a todos os Helpers.
 *
 * @package Helper
 * @name Helper
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 03/08/2009
 */
/**
 * SESSIONS
 *
 * A estrutura das sessions para reter informações de Helpers segue o padrão:
 *      $_SESSION["Sys"][$nomeDoHelper."Helper"][$informacao] = $valor;
 * 
 */
class Helper
{
    /**
     *
     * @var array Parâmetros do sistema como $_POST, $_GET, Controller, Action
     */
    protected $params;

    public $controller;

    /**
     * Models inicializados pelo controller principal, de forma a poderem ser
     * usados por helpers
     *
     * @var Object
     */
    protected $models;
    /**
     *
     * @var array Dados vindos de um formulário
     */
    protected $data = array();

    /**
     *
     * @var array Contém informações sobre o ambiente da aplicação.
     */
    protected $environment = array();

    /**
     * Cada classe contém um versão. Esta versão é descrita nesta variável.
     *
     * @var string Versão desta classe
     */
    protected $version;
    /**
     *
     * @var Array Array com objetos dos Helpers instanciados
     */
    public $_loadedHelpers;


    function __construct(&$params = ""){

        /**
         * Inicialização de variáveis
         */
     	$this->controller = ( empty($params["controller"]) ) ? array() : $params["controller"];
        /**
         * $this->params
         */
        $this->params = ( empty($params["params"]) ) ? array() : $params["params"];
        /**
         * $this->conn
         */
        $this->conn = ( empty($params["conn"]) ) ? array() : $params["conn"];

        /**
         * $this->models
         */
        $this->models = ( empty($params["models"]) ) ? array() : $params["models"];
        /**
         * $this->data
         */
        $this->data = ( empty($params["data"]) ) ? array() : $params["data"];
        /**
         * $this->environment
         */
        $this->environment = ( empty($params["environment"]) ) ? array() : $params["environment"];

        /*
         * Carrega todos os Helpers criados, tendo acesso a eles via referência
         */
        $this->_loadedHelpers = ( empty($params["_loadedHelpers"]) ) ? array() : $params["_loadedHelpers"];
    }

    function setGlobalProperties($param = array()){

    }

    function afterFilter(){
        
    }

    function afterRenderFilter(){
        
    }

    /**
     * MÉTODOS DE INFORMAÇÕES SOBRE CLASSE
     */
    /**
     * Cada classe contém um versão. Este método retorna a versão desta
     * classe.
     *
     * @return string Versão desta classe
     */
    public function version(){
        return $version;
    }
}

?>