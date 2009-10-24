<?php
/**
 * Arquivo que representa a estrutura controller de uma arquitetura MVC.
 *
 *
 * @package MVC
 * @name Controller
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 16/07/2009
 */
class Controller
{
    /**
     *
     * @var TEngine Objeto responsável pela inicialização de todo o sistema
     */
    private $engine;
    /**
     * VARIÁVEIS DE SISTEMA
     */
        /**
         *
         * @var array Contém todos os parâmetros de ambiente do sistema, como
         * variáveis $_POST e $_GET tratadas, bem como URL atual, controllers e
         * actions
         */
        protected $params;
        /**
         *
         * @var array Contém todos os dados organizados provenientes de forms
         */
        protected $data;
        /**
         *
         * @var string Endereço do root da aplicação
         */
        protected $webroot;
        /*
         * Variáveis que serão enviadas para o view
         */
        public $globalVars = array();
    /**
     * HELPERS/COMPONENTS/BEHAVIORS
     */
        /**
         * HELPERS
         */
        /**
         *
         * @var array Helpers são objetos que auxiliam em tarefas de View, como
         * Formulários, Javascript, entre outros.
         */
        protected $helpers = array("Html","Form");
        /**
         *
         * @var Object Todos os helpers carregados ficam aqui.
         */
        public $_loadedHelpers;
        /**
         * COMPONENTS
         */
        /**
         *
         * @var array Components são objetos que automatizam processos de nível
         * de Controller, tais como autenticação e login
         */
        protected $components = array();
        /**
         *
         * @var array Componentes já carregados (evita retrabalho)
         */
        protected $loadedComponents = array();

    /**
     * MODELS
     */
        /**
         * USES
         *
         * Indica quais models devem ser carregados
         *
         * @var array Contém o nome dos models a serem usados
         */
        protected $uses = array();
        /**
         *
         * @var array Models já usados (evita retrabalho)
         */
        protected $usedModels = array();
        /**
         *
         * @var int Models que se interrelacionam precisam de um limite de
         * carregamento recursivo. Por exemplo:
         *
         *      1) Usuario hasMany Tarefa;
         *      2) Tarefa belongsTo Usuario;
         *
         * O usuário Model carregaria Usuario e então os Models filhos (Tarefa).
         * Tendo carregado Tarefa, veria que ele pertence a Usuario, e carregaria
         * Usuario dentro de Tarefa. Isto aconteceria infinitamente sem
         * Model::recursive setado.
         *
         * O padrão é 1, mas pode-se setar recursividade na profundidade desejada.
         */
        public $recursive = 1;

    /**
     * VIEW
     */
        /**
         *
         * @var string Título do site
         */
        protected $siteTitle = "Site Title (set controller::siteTitle)";
        /**
         *
         * @var string Título da página acessada
         */
        protected $pageTitle = "My Page (set controller:pageTitle)";


        protected $metaTags = array();
        /**
         * Layout
         *
         * @var string Indica qual é o layout usado
         */
        protected $layout = "default";

        /**
         * Se o sistema deve renderizar as views automaticamente.
         *
         * @var bool
         */
        protected $autoRender = true;
        /**
         *
         * @var bool Indica se já houve renderização (evita retrabalho)
         */
        protected $isRendered = false;

    /**
     * CONFIGURAÇÃO DE AMBIENTE
     */
        /**
         *
         * @var array Contém informações sobre o ambiente da aplicação
         */
        public $environment = array();


    /**
     * MÉTODOS
     */
    /**
     * __construct();
     *
     * @param array $param Parâmetros de inicialização
     */
    function __construct($param = ''){

        /**
         * VARIÁVEIS DE SISTEMA
         *
         * Inicialização de variáveis
         */
        /**
         * Toma todas as configurações do objeto Engine, responsável pela
         * de inicialização do sistema, como análise de URLs, entre outros.
         */
        $this->engine = $param["engine"];
        /**
         * $THIS->PARAMS
         *
         * Configura os parâmetros de sistema
         */
        $this->params["controller"] = $this->engine->callController;
        $this->params["action"] = $this->engine->callAction;
        $this->params["args"] = $this->engine->arguments;
        $this->params["webroot"] = $this->engine->webroot;

        $args = array();
        foreach( $this->params["args"] as $chave=>$valor ){
            if( is_int($chave) )
                $args[$chave] = $valor;
            else
                $args[$chave] = $chave.":".$valor;
        }

        $this->params["url"] = $this->engine->webroot.$this->params["controller"]."/".$this->params["action"]."/".implode("/", $args ) ;
        /*
         *
         * $THIS->DATA
         *
         * Ajusta $_POST e $_FILES, inserindo os dados organizadamente em
         * $this->data.
         */
            /*
             * $_FILES
             *
             * Guarda tudo de $_FILES em $this->params["files"] e $this->data
             */
            if( !empty($_FILES) ){
                $formattedFilesData = array();
                if( !empty($_FILES["data"]) ){
                    $data = $_FILES["data"];

                    /*
                     * O resultado que vem em $_FILES são bagunçados. O código
                     * abaixo faz um loop por este comando e organiza as
                     * informações.
                     */
                    /*
                     * Loop pelos tipos de dados do arquivo
                     */
                    foreach( $data as $infoName=>$model ){

                        /*
                         * Loop por cada model
                         */
                        foreach( $model as $modelName=>$fields ){
                            if( is_array($fields) ){

                                /*
                                 * Loop pelos arquivos enviados
                                 */
                                foreach( $fields as $fieldName=>$fieldData ){
                                    $formattedFilesData[$modelName][$fieldName][$infoName] = $fieldData;
                                }

                            }
                        }
                    }
                    $this->params["files"] = $formattedFilesData;
                } else {
                    /**
                     * @todo -
                     */
                    $this->params["files"] = $_FILES;
                }
            }

            /*
             * $_POST
             */
            if( !empty($_POST) ){
                $this->params["post"] = $_POST;
                if( !empty($_POST["data"]) ){
                    $this->data = $_POST["data"];
                }
            }

            if( !empty($formattedFilesData) ){
                if( !empty($this->data) ){
                    $this->data = array_merge_recursive($this->data, $formattedFilesData);
                } else {
                    $this->data = $formattedFilesData;
                }
            }

            if( !empty($this->data) )
                $this->params["data"] = $this->data;

        /*
         * Soma em $this->data os dados necessários
         * 
         * Os dados que estiverem na Session no seguinte endereço serão
         * acrescentados em $this->data.
         * 
         * $_SESSION["Sys"]
         *              ["addToThisData"]
         *                  [$modelName]
         *                      [$campo] = $valor;
         */
        /**
         * Cada form tem um id. Se foi enviado um $_POST[formId], vai adiante
         * para inserir dados em $this->data.
         */
        //pr($this->params);
        if( !empty($this->params["post"]["formId"]) ){

            /**
             * Pega o valor a ser incrementado em $this->data e guarda em $toAdd
             */
            if( !empty($_SESSION["Sys"]["addToThisData"][ $this->params["post"]["formId"] ]) )
                $toAdd = $_SESSION["Sys"]["addToThisData"][ $this->params["post"]["formId"] ];

            /**
             * Se $this->data existe e ha algo a ser inserido
             */
            if( !empty($this->data) AND !empty($toAdd) ){
                $this->data = array_merge_recursive_distinct($toAdd, $this->data );
            }
            /**
             * Nao ha $this->data, mas ha algo a ser inserido
             */
            else if( !empty($toAdd) ) {

                if( $this->params["url"] !== $_SESSION["Sys"]["options"]["addToThisData"][ $this->params["post"]["formId"] ]["destLocation"] ){
                    unset( $_SESSION["Sys"]["addToThisData"][ $this->params["post"]["formId"] ] );
                } else {
                    $this->data = $toAdd;
                }

            }
        }
        /**
         * Redirecionamentos sem post, mas com uso de $_SESSION
         */
        else {

            if( !empty($_SESSION["Sys"]["addToThisData"]) )
                $toAdd = $_SESSION["Sys"]["addToThisData"];

            /**
             * Se $this->data existe e ha algo a ser inserido
             */
            if( !empty($this->data) AND !empty($toAdd) ){
                $this->data = array_merge_recursive_distinct($toAdd, $this->data );
            }
            /**
             * Nao ha $this->data, mas ha algo a ser inserido
             */
            else if( !empty($toAdd) ) {
                //echo $this->params["post"]["formId"]."";
                /*
                if( $this->params["url"] !== $_SESSION["Sys"]["options"]["addToThisData"]["destLocation"] ){
                    unset( $_SESSION["Sys"]["addToThisData"] );
                } else {
                    $this->data = $toAdd;
                }
                 * 
                 */

            }

        }

        $this->webroot = $this->engine->webroot;

        /**
         * VARIÁVEIS DE AMBIENTE
         */
        /**
         * Variáveis de ambiente são ajustadas no método controller::_trigger();
         */

        /**
         * MODELS
         *
         * Carrega models que estão descritos em $this->uses
         */
        if( !empty($this->uses) ){
            /**
             * Loop por cada model requisitado e carrega cada um.
             *
             * Ele estão acessívels através de $this->modelName
             */
            foreach($this->uses as $valor){
                $className = $valor;
                
                $this->loadModel($className);
            }
        }

        /**
         * HELPERS, COMPONENTS, BEHAVIORS
         *
         * Inicialização destes automatizadores de processos.
         */
        /**
         * HELPERS
         * 
         * Cria helpers solicitados
         */
            /**
             * Helpers são criados no método _TRIGGER(), após os actions
             * terem sido rodados.
             *
             * Ver Controller::_trigger()
             */

        /**
         * COMPONENTS
         */
        if( count($this->components) ){
            /**
             * Loop por cada component requisitado.
             *
             * Carrega classe do Component, instancia e envia para o Controller
             */
            foreach($this->components as $valor){
                include_once( CORE_COMPONENTS_DIR.$valor.".php" );
                $componentName = $valor.COMPONENT_CLASSNAME_SUFFIX;
                /**
                 * Instancia compoment
                 */
                $componentParams = array(
                    "params" => $this->params,
                    "data" => $this->data,
                    "models" => $this->usedModels
                );
                $$valor = new $componentName($componentParams);
                /**
                 * Envia o Component para a Action do Controller
                 */
                $loadedComponentName = StrTreament::firstToLower($valor);
                $this->{$loadedComponentName} = $$valor;
                $this->loadedComponents[] = $loadedComponentName;
            }
        }


        /**
         * VARIÁVEIS GLOBAIS
         *
         * Agrega ao objeto atual as variáveis globais necessárias.
         */
        /**
         * $action: que ação será chamada neste módulo
         */
        $this->action = (empty( $this->engine->callAction )) ? 'index' : $this->engine->callAction;

        /**
         * EXECUTA MVC
         *
         * Começa execução de métodos necessários.
         */
        /**
         * _trigger() é responsável por engatilhar todos os métodos
         * automáticos a serem rodados, como beforeFilter, render, etc.
         */
        $this->_trigger( array( 'action' => $this->action ) );
    }

    /*
     * MÉTODOS DE SUPORTE
     *
     * Todos os métodos que dão suporte ao funcionamento do sistema.
     *      ex.: render, set, redirect, beforeFilter, afterFilter, _trigger, ect
     */
    /*
     * CARREGAMENTO
     */
    /**
     * loadModel()
     * 
     * Carrega o model especificado.
     * 
     * @param string $modelName
     * @return bool
     */
    public function loadModel($modelName){

        /**
         * Monta parâmetros para criar os models
         */
        $modelParams = array(
            'conn' => $this->engine->conn,
            'dbTables' => $this->engine->dbTables,
            'modelName' => $modelName,
            'recursive' => $this->recursive,
            'params' => &$this->params,
        );

        include_once(APP_MODEL_DIR.$modelName.".php");

        $this->{$modelName} = new $modelName($modelParams);
        $this->usedModels[$modelName] = &$this->{$modelName};
        return true;
    }

    /*
     * MÉTODOS EXTERNOS DE SUPORTE
     */
    /**
     * Renderiza a view
     *
     * @param string $path Indica qual o view deve ser carregado.
     */
    public function render($path = "", $includeType = ''){


            /**
             * VARIÁVEIS DE AMBIENTE
             */
                $this->environment["pageTitle"] = $this->pageTitle;
                $this->environment["siteTitle"] = $this->siteTitle;
                $this->environment["metaTags"] = $this->metaTags;

            /**
             * ENVIA DADOS PARA O VIEW
             */
                $this->set("siteTitle", $this->siteTitle);
                $this->set("pageTitle", $this->pageTitle);

            /**
             * HELPERS
             *
             * Cria helpers solicitados
             */
                if( !empty($this->helpers) ){
                    /**
                     * Loop por cada helper requisitado.
                     *
                     * Carrega classe do Helper, instancia e envia para o View
                     */
                    foreach($this->helpers as $valor){
                        include_once( CORE_HELPERS_DIR.$valor.".php" );
                        $helperName = $valor.HELPER_CLASSNAME_SUFFIX;

                        $helperParams = array(
                            "params" => &$this->params,
                            "data" => $this->data,
                            "models" => $this->usedModels,
                            "environment" => $this->environment,
                            // todos helpers têm acesso aos demais helpers
                            "_loadedHelpers" => &$this->_loadedHelpers,
                        );
                        $$valor = new $helperName($helperParams);

                        /*
                         * Salva a instância do Helper atual
                         */
                        $this->_loadedHelpers[$valor] = &$$valor;
                        /**
                         * Envia Helper para o view
                         */
                        $this->set( strtolower($valor), $$valor);
                    }
                }

        /**
         *
         * DEFINE VARIÁVEIS PARA AS VIEWS
         *
         * Cria todas as variáveis para serem acessadas pela view diretamente.
         *
         * Ex.: $variavel estará disponível em vez de $this->variavel.
         */
        foreach( $this->globalVars as $chave=>$valor ){
            $$chave = $valor;
            /**
             * Agora as variáveis são locais a este método, sendo acessadas
             * pelo view, pois o view é acessado via include a seguir ainda
             * neste método.
             */
        }
        
        /**
         * Há arquivos padrães que podem substituir funcionalidades de um módulo
         * quando estes estão ausentes.
         *
         * Inclui a view correspondente deste action
         */

        $content_for_layout = "";

        if( $path != false ){
            ob_start();
            include(APP_VIEW_DIR."".$this->engine->callController."/".$path.".php");
            $content_for_layout = ob_get_contents();
            ob_end_clean();

            if( is_file(APP_LAYOUT_DIR.$this->layout.".php") ){
                include(APP_LAYOUT_DIR.$this->layout.".php");
            } else {
                include(CORE_LAYOUT_DIR.$this->layout.".php");
            }

        }

        /**
         * Confirma que renderização foi feita para que não haja duplicação
         * da view
         */
        $this->isRendered = true;
        return true;
    }

    /**
     * Envia um valor $varValue para um view em uma variável com nome $varNome.
     *
     * @param string $varName Nome da variável com valor dentro do view
     * @param mixed $varValue Valor da variável a ser passada para o view
     */
    public function set($varName, $varValue){
        $this->globalVars[$varName] = $varValue;
    }

    /**
     * redirect()
     * 
     * @todo - implementar
     *
     * @param <type> $url
     */
    public function redirect($url){
        if( is_array($url) ){
            if( !empty($url["controller"]) ){
                echo $this->webroot.$url["controller"].$url["action"];
            } else {
                echo $this->webroot."<---";
            }
        } else if( is_string($url) ){
            
        } else {
            
        }
    }

    /**
     * EVENTOS
     *
     * beforeFilter, afterFilter
     */

    public function beforeFilter(){

        return true;
    }


    public function afterFilter(){

        return true;
    }

    /*
     * MÉTODOS DE SUPORTE DE AMBIENTE
     */

    /**
     * isAjax()
     *
     * Verifica se este é um acesso HTTP via Ajax
     *
     * @return bool
     */
    public function isAjax(){
        return ( isset( $_SERVER["HTTP_X_REQUESTED_WITH"] )
            AND strtolower($_SERVER["HTTP_X_REQUESTED_WITH"]) == "xmlhttprequest" );
        
        return false;
    }

    /**
     * setAjax()
     *
     * Ajusta as configurações do action para ser do tipo ideal para retornos
     * a requisições Ajax.
     *
     * @param int $debug Por padrão, desativa o debug
     * @param string $layout Ajax por padrão
     */
    public function setAjax($debug = 0, $layout = "ajax"){
        Config::write("debug", $debug);
        $this->layout = $layout;
    }

    /*
     * MÉTODOS INTERNOS DE SUPORTE
     */
    /**
     * _TRIGGER()
     *
     * É o responsável por chamar as funções:
     *      1. beforeFilter
     *      2. o método do action
     *      3. render
     *      4. afterFilter
     *
     * @method _TRIGGER()
     * @param array $param
     *      'ation': qual método deve ser chamado
     */
    public function _trigger($param){
        /**
         * Se não há um action especificado, então assume-se index()
         */
        if( empty( $param['action'] ) ){
            $param['action'] = 'index';
        }

        /**
         * MÉTODO EXISTE?
         *
         * Se método não existe, pára tudo. Se existe, continua.
         */
        if( method_exists($this, $param['action']) ){
            $actionExists = true;
        } else {
            $actionExists = false;
        }
        if( Config::read("debug") > 0 )
            $actionExists = true;

        /**
         * Se o action existe
         */
        if( $actionExists ){
            /**
             * $this->beforeFilter() é chamado sempre antes de qualquer ação
             */
            $this->beforeFilter();
            /**
             * Components->afterBeforeFilter()
             *
             * Se há afterBeforeFilter() no component, carrega
             */
            foreach( $this->loadedComponents as $component ){
                if( method_exists($this->$component, "afterBeforeFilter") ){
                    $this->$component->afterBeforeFilter();
                }
            }

            /**
             * Chama a action requerida com seus respectivos argumentos.
             */
            call_user_func_array( array($this, $param['action'] ), $this->params["args"] );

            /**
             * Se não foi renderizado ainda, renderiza automaticamente
             */
            if( !$this->isRendered AND $this->autoRender )
                $this->render( $this->action );
            else if( !$this->isRendered )
                $this->render( false );



            /*
             * HELPERS AFTERFILTER()
             */
            $this->_helperAfterFilter();
            /**
             * $this->afterFilter() é chamado sempre depois de qualquer ação
             */
            $this->afterFilter();
        }
    }

    /*
     * MÉTODOS INTERNOS DO CONTROLLER
     */
    /**
     * Tenta chamar alguma action não declarada de forma automática.
     *
     * @todo - implementar
     *
     * @param string $function Que método foi chamado.
     * @param string $args Que argumentos foram passados.
     */
    public function __call($function, $args){
        return false;
    }

    /**
     * _helperAfterFilter()
     * 
     * Chama o Helper::afterFilter de todos os Helpers instanciados.
     * 
     * @return bool
     */
    public function _helperAfterFilter(){

        foreach( $this->_loadedHelpers as $helper=>$helperObject ){
            $helperObject->afterFilter();
        }

        return true;
    }

}

?>