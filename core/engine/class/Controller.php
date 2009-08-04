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
    protected $webroot;
    /**
     * HELPERS
     *
     * @var array Helpers são objetos que auxiliam em tarefas de View, como
     * Formulários, Javascript, entre outros.
     */
    protected $helpers = array("Html", "Form");

    /**
     * USES (MODELS)
     *
     * Indica quais models devem ser carregados
     *
     * @var array Contém o nome dos models a serem usados
     */
    protected $uses = array();
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
     * CONFIGURAÇÃO DE AMBIENTE
     */
    protected $layout = "default";
    protected $autoRender = true;
    protected $isRendered = false;

    /**
     *
     * @param <type> $param
     */
    protected $actionCallback;

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
        /**
         * Ajusta $_POST
         */
        if( !empty($_POST) ){
            $this->params["post"] = $_POST;
            if( !empty($_POST["data"]) ){
                $this->data = $_POST["data"];
            }
        }

        $this->webroot = $this->engine->webroot;
        //$this->set("web", $varValue)

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
                
                /**
                 * Monta parâmetros para criar os models
                 */
                 //pr($this->engine->dbTables);
                $modelParams = array(
                    'conn' => $this->engine->conn,
                    'dbTables' => $this->engine->dbTables,
                    'modelName' => $className,
                    'recursive' => $this->recursive,
                );

                if( !class_exists($className) ){
                    include(APP_MODEL_DIR.$className.".php");
                }
                $this->{$className} = new $className($modelParams);
            }
        }

        /**
         * HELPERS, COMPONENTS, BEHAVIORS
         */
        /**
         * HELPERS
         * 
         * Cria helpers solicitados
         */
        if( count($this->helpers) ){
            /**
             * Loop por cada helper requisitado.
             *
             * Carrega classe do Helper, instancia e envia para o View
             */
            foreach($this->helpers as $valor){
                include_once( CORE_HELPERS_DIR.$valor.".php" );
                $helperName = $valor.HELPER_CLASSNAME_SUFFIX;
                $$valor = new $helperName();
                $this->set( strtolower($valor), $$valor);
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
         * trigger() é responsável por engatilhar todos os métodos
         * automáticos a serem rodados, como beforeFilter, render, etc.
         */
        $this->trigger( array( 'action' => $this->action ) );
    }

    /**
     * MÉTODOS DE SUPORTE
     *
     * Todos os métodos que dão suporte ao funcionamento do sistema.
     *      ex.: render, set, beforeFilter, afterFilter, trigger, ect
     */
    /**
     * TRIGGER()
     *
     * É o responsável por chamar as funções:
     *      1. beforeFilter
     *      2. o método do action
     *      3. render
     *      4. afterFilter
     *
     * @method TRIGGER()
     * @param array $param
     *      'ation': qual método deve ser chamado
     */
    protected function trigger($param){
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
             * $this->beforeFilter() é chamado sempre antes de qualquer açãoo
             */
            $this->beforeFilter();
            /**
             * Chama a action requerida com seus respectivos argumentos.
             */
            call_user_func_array( array($this, $param['action'] ), $this->params["args"] );

            /**
             * Se não foi renderizado ainda, renderiza automaticamente
             */
            if( !$this->isRendered AND $this->autoRender )
                $this->render( $this->action );
            /**
             * $this->afterFilter() é chamado sempre depois de qualquer ação
             */
            $this->afterFilter();
        }
    }

    /**
     * Renderiza a view
     *
     * @param string $path Indica qual o view deve ser carregado.
     */
    protected function render($path, $includeType = ''){

        /**
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

        ob_start();
        include(APP_VIEW_DIR."".$this->engine->callController."/".$path.".php");
        $content_for_layout = ob_get_contents();
        ob_end_clean();

        if( is_file(APP_LAYOUT_DIR.$this->layout.".php") ){
            include(APP_LAYOUT_DIR.$this->layout.".php");
        } else {
            include(CORE_LAYOUT_DIR.$this->layout.".php");
        }
        /**
         * Confirma que renderização foi feita para que não haja duplicação
         * da view
         */
        $this->isRendered = true;
        return true;
    }

    protected function set($varName, $varValue){
        $this->globalVars[$varName] = $varValue;
    }


    protected function beforeFilter(){

        return true;
    }


    protected function afterFilter(){

        return true;
    }

    /**
     * Tenta chamar alguma action não declarada de forma automática.
     *
     * @param string $function Que método foi chamado.
     * @param string $args Que argumentos foram passados.
     */
    private function __call($function, $args){

        //pr($args);

    }
}

?>