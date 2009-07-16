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

    private $engine;

    protected $helpers = array('Form');

    /**
     * CONFIGURAÇÃO DE AMBIENTE
     */
    protected $layout = "default";
    protected $autoRender = true;

    function __construct($param = ''){

        /**
         * Inicialização
         */
        $this->engine = $param["engine"];

        /**
         * HELPERS | COMPONENTS | BEHAVIORS
         */
        /**
         * HELPERS
         * 
         * Cria helpers solicitados
         */
        if( count($this->helpers) ){
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
         * EXECUTA
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
     * ACTIONS DE APOIO
     *
     * Métodos que desempenham funções que podem substituir actions
     * inexistentes.
     */
    protected function actions(){
        $this->set('aust', $this->aust);
        $this->render('actions', 'content_trigger');
    }

    /**
     * MÉTODOS DE SUPORTE
     *
     * Todos os métodos que dão suporte ao funcionamento do sistema.
     *      ex.: render, set, beforeFilter, afterFilter, trigger, ect
     */
    /**
     * TRIGGER
     *
     * É o responsável por chamar as funções:
     *      1. beforeFilter
     *      2. o método do action
     *      3. render
     *      4. afterFilter
     *
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
         * $this->beforeFilter() é chamado sempre antes de qualquer açãoo
         */
        $this->beforeFilter();
        /**
         * Chama a action requerida.
         */
        $this->{$param['action']}();

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



    }
}

?>