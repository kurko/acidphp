<?php
/**
 * COMPONENT AUTH
 *
 * Automatiza sistema de login no sistema.
 *
 * @package Components
 * @name Auth
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 04/08/2009
 */
class AuthComponent extends Component
{
    protected $loginPage = array();
    /**
     * $allow
     *
     * Contém os controllers e actions permitidos. Há três formatos possíveis:
     *      - Libera um Controller inteiro -> Controller é um valor na array:
     *      ex.: array(
     *               "controllerA", "controllerB"
     *           );
     *
     *      - Libera Actions específicos -> Controller é um índice com subarray
     *        de Actions.
     *      ex.: array(
     *               "controllerA" => array(
     *                   "actionA", "actionB"
     *               ),
     *               "controllerB"
     *           );
     *
     *      - Libera todos os Controllers -> Um valor de array com asterísco (*)
     *      ex.: array("*");
     * 
     * Obs.: $this->allow sobrescreve $this->deny
     *
     * @var array Controllers e Actions permitidos
     */
    protected $allow = array(
        "site" => array(
            "index", "login", "outro"
        ),
    );

    /**
     * $deny
     *
     * Contém os controllers e actions proibidos. Há três formatos possíveis:
     *      - Proibde um Controller inteiro -> Controller é um valor na array:
     *      ex.: array(
     *               "controllerA", "controllerB"
     *           );
     *
     *      - Proibe Actions específicos -> Controller é um índice com subarray
     *        de Actions.
     *      ex.: array(
     *               "controllerA" => array(
     *                   "actionA", "actionB"
     *               ),
     *               "controllerB"
     *           );
     *
     *      - Proibe todos os Controllers -> Um valor de array com asterísco (*)
     *      ex.: array("*");
     *
     * Obs.: $this->allow sobrescreve $this->deny
     *
     * @var array Controllers e Actions probibidos
     */
    protected $deny = array("*");

    /**
     *
     * @var boolean Se o usuário possui autorização para acessar a página atual
     */
    protected $forbidden = false;

    /**
     * CONFIGURAÇÃO
     */
    /**
     *
     * @var string Action padrão onde se localiza um login
     */
    protected $defaultLoginAction = "login";

    function __construct($params = ""){
        parent::__construct($params);

        $this->forbidden = false;
    }

    public function afterBeforeFilter(){

        /**
         * Se está tudo bloqueado
         */
        if( in_array("*", $this->deny) ){

            $this->forbidden = true;
            /**
             * Verificar se há alguma configuração que sobrescreve a
             * proibição atual para liberar o acesso.
             *
             * Obs.: $this->allow sobrescreve $this->deny
             */
            if( !empty($this->allow) ){
                /**
                 * Se o controller atual está totalmente liberado
                 */
                if( in_array($this->params["controller"], $this->allow) ){
                    $this->forbidden = false;
                }
                /**
                 * Se há actions do controller atual liberados, verifica se o
                 * action atual está liberado
                 */
                else if( array_key_exists($this->params["controller"], $this->allow) ){
                    /**
                     * Verifica se o Action está liberado
                     */
                    if( in_array($this->params["action"], $this->allow[$this->params["controller"]]) ){
                        $this->forbidden = false;
                    }
                    /**
                     * Se não está, verifica ainda se o action é login. Login
                     * é universalmente liberado
                     */
                    else if( in_array($this->params["action"], array("login","logout")) ){
                        $this->forbidden = false;
                    }
                }
            }
        }

        /**
         * ACESSO NEGADO
         */
        if( $this->forbidden ){
            /**
             * loginPage
             *
             * Faz verificações de qual a página de login, há um controller e
             * action especificados.
             */
            $controller = $this->loginPage["controller"];
            $action = $this->loginPage["action"];
            
            /**
             * Controller e Action
             */
            if( !empty($controller) AND !empty($action) ){
                $newUrl = $this->params["webroot"].$controller.'/'.$action;
            }
            /**
             * Controller especificado, no action
             */
            else if( !empty($controller) ) {
                $newUrl = $this->params["webroot"].$controller.'/'.$this->defaultLoginAction;
            }
            /**
             * Action especificado, no controller
             */
            else if( !empty($action) ){
                $newUrl = $this->params["webroot"].$this->params["controller"].'/'.$action;
            }
        }

        /**
         * Se proibido e há uma $newUrl estipulada
         */
        if( !empty($newUrl) ){
            header("Location: ".$newUrl);
        }
    } // fim afterBeforeFilter()

    /**
     * MÉTODOS DE CONFIGURAÇÃO
     */
    /**
     * Define qual é a página de login padrão
     *
     * @param array $loginPage Qual é a página de login
     */
    public function loginPage($loginPage){
        $this->loginPage = $loginPage;
    }

    /**
     * allow()
     *
     * @param array $allow Quais Controllers/actions liberados (para sintaxe,
     *                      ver propriedade $this->allow)
     */
    public function allow($allow){
        $this->allow = $allow;
    }

    /**
     * deny()
     *
     * @param array $deny Quais Controllers/actions proibidos (para sintaxe,
     *                    ver propriedade $this->deny)
     */
    public function deny($deny){
        $this->deny = $deny;
    }

}
?>