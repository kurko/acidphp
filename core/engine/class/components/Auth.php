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
            "index", "login", "logout"
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
     * @var string Model principal relacionado com o login
     */
    protected $model = "";
    /**
     *
     * @var string Action padrão onde se localiza um login
     */
    protected $defaultLoginAction = "login";
    /**
     *
     * @var array Endereço para onde deve ser redirecionado o usuário após login
     */
    public $redirectTo;

    public $logged;

    protected $loginFields = array(
        "username" => "username",
        "password" => "password"
    );

    protected $incorrectLoginError = "Incorrect Login!";

    protected $deniedAccessError = "Denied Access!";

    function __construct($params = ""){
        parent::__construct($params);

        $this->forbidden = false;

        /**
         * Verifica se usuário está logado
         */
        if( !empty($_SESSION["Sys"]["Auth"]["logged"]) AND $_SESSION["Sys"]["Auth"]["logged"] ){
            $this->logged = true;
        }

    }

    /**
     * afterBeforeFilter()
     *
     * Acontece depois (after) de Controller::beforeFilter()
     *
     * Redireciona o usuário para a página de login se ele tiver acesso negado.
     *
     * @author Alexandre de Oliveira
     */
    public function afterBeforeFilter(){
        
        /**
         * Limpa statusMessage
         */
        if( !empty($_SESSION["Sys"]["FormHelper"]["statusMessage"]) ){
            $sM = $_SESSION["Sys"]["FormHelper"]["statusMessage"];
            if( $sM["class"] == "denied" ){
                $_SESSION["Sys"]["FormHelper"]["statusMessage"]["class"] = "denied2";
            } else {
                unset($_SESSION["Sys"]["FormHelper"]["statusMessage"]);
            }
        }


        /**
         * Ordem de Logout
         */
        if( $this->params["action"] == "logout" ){
            unset($_SESSION["Sys"]["Auth"]);
            unset($_SESSION["Sys"]["FormHelper"]["statusMessage"]);
            redirect( translateUrl( $this->loginPage() ) );
        }

        /**
         * Se usuário está logado e está no action login, redireciona
         */
        if( $this->logged AND $this->params["action"] == "login" ){
            redirect( $this->redirectTo() );
        }

        /**
         * TENTATIVA DE LOGIN
         *
         * Verifica se dados enviados estão corretos para Login
         */
        if( !$this->logged AND !empty($this->data) ){

            /**
             * Assegura os dados passados pelo usuário
             */
            $data = Security::Sanitize($this->data);
            
            /**
             * Se um model foi especificado
             */
            if( !empty($this->model) ){
                /**
                 * Se os dados enviados correspondem ao model do Auth
                 */
                if( array_key_exists($this->model(), $data)){

                    /**
                     * Carrega o $model verdadeiro relacionado ao Login
                     */
                    $model = $this->models[ $this->model() ];

                    $dataFields = $data[ $this->model() ];
                    /**
                     * Cria conditions
                     */
                    foreach( $dataFields as $campo=>$valor ){
                        $conditions[$this->model().'.'.$campo] = $valor;
                    }

                    $result = $model->find( array(
                                                "conditions" => $conditions
                                            )
                                        );
                    $tempResult = array_keys($result);

                    /**
                     * VERIFICAÇÃO DE DADOS PARA LOGIN
                     */
                    /**
                     * Usuário existe
                     */
                    if( !empty($result) AND count($result) == 1 ){
                        $this->logged = true;

                        $_SESSION["Sys"]["Auth"]["logged"] = true;
                        $_SESSION["Sys"]["Auth"][$this->model()] = $result[$tempResult[0]][$this->model()];
                        header("Location: ". translateUrl( $this->redirectTo() ) );
                    }
                    /**
                     * Usuário inexistente
                     */
                    else {
                        $_SESSION["Sys"]["FormHelper"]["statusMessage"] = array(
                            "class" => "incorrect",
                            "message" => $this->incorrectLoginMessage
                        );
                    }
                }
            }
        }
        

        /**
         * Usuário Logado
         */
        if( !$this->logged ){
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

            $_SESSION["Sys"]["FormHelper"]["statusMessage"] = array(
                "class" => "denied",
                "message" => $this->deniedAccessMessage
            );
            redirect($newUrl);
        } else {
            return true;
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
    public function loginPage($loginPage=""){
        if( !empty($loginPage) ){
            /**
             * Se o usuário não está logado
             */
            if( !$this->logged ){
                /**
                 * defaultLoginPage
                 *
                 * Para automatizar sistema de login, usa a variável global para
                 * criar formulário com FormHelper
                 */
                global $globalVars;
                $globalVars["defaultLoginPage"] = $loginPage;
                $this->loginPage = $loginPage;
            }
        } else {
            return $this->loginPage;
        }
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
    /**
     * redirectTo()
     *
     * @param array $redirectTo Contém endereço para onde o usuário será
     *                          redirecionado após efetuar login
     */
    public function redirectTo($redirectTo=""){
        if( !empty($redirectTo) ){
            $this->redirectTo = $redirectTo;
        } else {
            return $this->redirectTo;
        }
    }
    /**
     * loginFields()
     *
     * @param array $loginFields Indica quais os campos de login
     */
    public function loginFields($loginFields){
        $this->loginFields = $loginFields;
    }
    /**
     * model()
     *
     * @param array $model Qual o model relacionado ao login
     */
    public function model($model=""){
        if( !empty($model) ){
            $this->model = $model;
        } else {
            return $this->model;
        }
    }

    /**
     * Mensagem de erro de Login
     *
     * @param string $message Mensagem a ser mostrada ao usuário
     */
    public function errorMessage($message=""){
        if( !empty($message) ){
            $this->incorrectLoginMessage = $message;
        } else {
            return $this->incorrectLoginMessage;
        }
    }

    /**
     * Mensagem a ser mostrada relacionada a acesso negado
     *
     * @param string $message Mensagem a ser mostrada ao usuário
     */
    public function deniedMessage($message=""){
        if( !empty($message) ){
            $this->deniedAccessMessage = $message;
        } else {
            return $this->deniedAccessMessage;
        }
    }
}
?>