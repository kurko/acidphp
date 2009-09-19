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
/**
 * Configurações básicas necessárias:
 *
 *      a) var $components = array("Auth") -> propriedade de AppController
 *
 *      Em beforeFilter(), dentro de AppController:
 *
 *      b) $this->auth->allow( array() ) -> qual controller/action será liberado
 *      c) $this->auth->redirectTo -> para onde o usuário é levado após login
 *      d) $this->auth->loginPage -> qual a página de login
 *      e) $this->auth->errorMessage -> mensagem de erro personalizada
 *      f) $this->auth->deniedMessage -> mensagem personalizada de acesso negado
 *      g) $this->auth->model -> model que contém os dados de login de usuário
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
     * Dica: use allow em vez de deny
     *
     * @var array Controllers e Actions permitidos
     */
    protected $allow = array();

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
     * Dica: use allow em vez de deny
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
     *
     * @var bool Indica se o usuário está logado ou não
     */
    public $logged;

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
         * @var bool Redirecionamento após login para última página acessada
         */
        protected $autoRedirect = true;
        /**
         *
         * @var string Quantos minutos até a sessão expirar
         */
        protected $expireTime = "";
        /**
         *
         * @var array Endereço para onde deve ser redirecionado o usuário após login
         */
        public $redirectTo;

        /**
         *
         * @var array Indica quais são os campos de login padrão
         */
        protected $loginFields = array(
            "username" => "username",
            "password" => "password"
        );

    /**
     * MENSAGENS DE STATUS
     */
    /**
     *
     * @var string Mensagem de login incorreto
     */
    protected $incorrectLoginMessage = "Incorrect information given! Please retry.";
    /**
     *
     * @var string Mensagem de acesso negado
     */
    protected $deniedAccessMessage = "Denied Access! Please login.";


    function __construct($params = ""){
        parent::__construct($params);

        $this->forbidden = false;

        $this->checkLogin();
    }

    /**
     * afterBeforeFilter()
     *
     * Acontece depois (after) de Controller::beforeFilter()
     *
     * Redireciona o usuário para a página de login se ele tiver acesso negado.
     *
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function afterBeforeFilter(){

        $actionCommand = "";

        /*
         * EXPIRE TIME
         */
        if( !empty($this->expireTime) ){

            if( !empty($_SESSION["Sys"]["Auth"]["startMicrotime"]) ){

                $expireTimeInSec = $this->expireTime * 60;
                $elapsedTime = microtime(true) - $_SESSION["Sys"]["Auth"]["startMicrotime"];

                if( $elapsedTime > $expireTimeInSec ){
                    $actionCommand = "logout";
                }
            }

            /*
             * Update actual microtime
             */
            $_SESSION["Sys"]["Auth"]["startMicrotime"] = microtime(true);

        }

        /**
         * LOGOUT
         *
         * Redireciona para $this->loginPage
         */
        if( $this->params["action"] == "logout" or $actionCommand == "logout" ){
            unset($_SESSION["Sys"]["Auth"]);
            unset($_SESSION["Sys"]["FormHelper"]["statusMessage"]);
            $this->checkLogin();
            redirect( translateUrl( $this->loginPage() ) );
        }

        /**
         * AUTOMATIZAÇÃO
         */
            /**
             * $this->loginPage não especificado
             */
            if( empty($this->loginPage["controller"]) AND empty($this->loginPage["action"]) ){
                $this->loginPage( array(
                    "controller" => $this->params["controller"],
                    "action" => "login"
                ));
            } else if( empty($this->loginPage["action"]) ){
                $this->loginPage( array(
                    "controller" => $this->loginPage["controller"],
                    "action" => "login"
                ));
            }

            /**
             * $this->redirectTo não especificado
             */
            if( empty($this->redirectTo["controller"]) AND empty($this->redirectTo["action"]) ){
                $this->redirectTo( array(
                    "controller" => $this->params["controller"],
                    "action" => "index"
                ));
            } else if( empty($this->redirectTo["action"]) ){
                $this->redirectTo( array(
                    "controller" => $this->redirectTo["controller"],
                    "action" => "index"
                ));
            }
            /**
             * @todo - redirectTo automático, sendo este igual à página que
             * o usuário estava tentando acessar.
             */

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
                    if( !empty($result) AND count($result) > 0 ){
                        $this->logged = true;

                        $_SESSION["Sys"]["Auth"]["logged"] = true;
                        $_SESSION["Sys"]["Auth"]["startMicrotime"] = microtime(true);
                        $_SESSION["Sys"]["Auth"][$this->model()] =
                            $result[$tempResult[0]][$this->model()];
                        
                        /*
                         * Verifica se deve-se fazer redirecionamento automático
                         */
                        if( !empty($_SESSION["Sys"]["Auth"]["autoRedirect"])
                            AND $this->autoRedirect() == true )
                        {
                            $newUrl = $_SESSION["Sys"]["Auth"]["autoRedirect"];
                            /*
                             * A limpeza de:
                             *
                             *      $_SESSION["Sys"]["Auth"]["autoRedirect"]);
                             *
                             * é feita após o usuário estar logado
                             */
                            /*
                             * Redireciona para a última página acessada
                             */
                            header("Location: ". translateUrl( $newUrl ) );
                        } else {
                            header("Location: ". translateUrl( $this->redirectTo() ) );
                        }
                        
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
        } // fim IF tentativa de login
        

        /**
         * NÃO LOGADO
         *
         * Verifica controllers/actions proibidos
         */
        if( !$this->logged ){

            /**
             * $THIS->ALLOW & $THIS->DENY
             *
             * Verifica as duas propriedades allow e deny, faz ajustes de
             * bloqueio e por fim verifica se o action atual não é de login ou
             * logout. Estes dois últimos são liberados por padrão.
             */
            /**
             * $THIS->ALLOW configurado
             *
             * Verificar se há alguma configuração que sobrescreve a
             * proibição atual para liberar o acesso.
             *
             * Obs.: $this->allow sobrescreve $this->deny
             */
            if( !empty($this->allow) ){

                /**
                 * Se allow está configurado, tudo está bloqueado por padrão,
                 * exceto o que for especificado
                 */
                $this->forbidden = true;
                
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
                }
            }
            /**
             * Se $this->allow está vazio, não configurado, os actions login
             * e logout são permitidos
             */
            else {

                /**
                 * Allow vazio
                 *
                 * Se deny está configurado, tudo está liberado por padrão,
                 * exceto o que for especificado
                 */
                $this->forbidden = false;
                
                /**
                 * $THIS->DENY configurado
                 *
                 * $this->allow está vazio
                 *
                 * Verifica se há um asterísco como valor na array $this->deny.
                 * Se sim, tudo está bloqueado. Se não, verifica se há
                 * alguma configuração específica para algum controller/action
                 * e faz o bloqueado de acordo.
                 */
                if( !empty($this->deny) ){

                    /**
                     * *: Tudo bloqueado
                     */
                    if( in_array("*", $this->deny) ){
                        $this->forbidden = true;
                    }

                    /**
                     * Verifica se controller está completamente bloqueado
                     */
                    else if( in_array($this->params["controller"], $this->deny) ){
                        $this->forbidden = true;
                    }
                    /**
                     * Se o controller bloqueado contém configurações de
                     * bloqueio de actions
                     */
                    else if( array_key_exists($this->params["controller"], $this->deny) ){
                        /**
                         * Verifica se o Action está bloqueado
                         */
                        if( in_array($this->params["action"], $this->deny[$this->params["controller"]]) ){
                            $this->forbidden = true;
                        }
                    }

                } else {
                    $this->forbidden = true;
                }
            }

            /**
             * actions login/logout sempre liberados
             */
            if( in_array($this->params["action"], array("login","logout")) ){
                $this->forbidden = false;
            }

        }
        /**
         * LOGADO
         */
        else {
            
            if( !empty($_SESSION["Sys"]["Auth"]["autoRedirect"]) )
                unset($_SESSION["Sys"]["Auth"]["autoRedirect"]);

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

            $_SESSION["Sys"]["Auth"]["autoRedirect"] = $this->params["url"];
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
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function loginPage($loginPage=""){
        if( !empty($loginPage) ){
            /**
             * Se o usuário não está logado
             */
            //if( !$this->logged ){
                /**
                 * defaultLoginPage
                 *
                 * Para automatizar sistema de login, usa a variável global para
                 * criar formulário com FormHelper
                 */
                global $globalVars;
                $globalVars["defaultLoginPage"] = $loginPage;
                $this->loginPage = $loginPage;
            //}
        } else {
            return $this->loginPage;
        }
    }

    /**
     * allow()
     *
     * @param array $allow Quais Controllers/actions liberados (para sintaxe,
     *                      ver propriedade $this->allow)
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function allow($allow){
        $this->allow = $allow;
    }

    /**
     * deny()
     *
     * @param array $deny Quais Controllers/actions proibidos (para sintaxe,
     *                    ver propriedade $this->deny)
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function deny($deny){
        $this->deny = $deny;
    }
    /**
     * redirectTo()
     *
     * @param array $redirectTo Contém endereço para onde o usuário será
     *                          redirecionado após efetuar login
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
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
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function loginFields($loginFields){
        $this->loginFields = $loginFields;
    }
    /**
     * model()
     *
     * @param array $model Qual o model relacionado ao login
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
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
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
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
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function deniedMessage($message=""){
        if( !empty($message) ){
            $this->deniedAccessMessage = $message;
        } else {
            return $this->deniedAccessMessage;
        }
    }

    /**
     * autoRedirect()
     *
     * Indica se o sistema deve ter o redirecionamento automático.
     *
     * @param bool $bool
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function autoRedirect($bool = ""){
        if( is_bool($bool) ){
            $this->autoRedirect = $bool;
        } else {
            return $this->autoRedirect;
        }
    }

    /**
     * expireTime()
     *
     * Quanto tempo de inatividade é permitida.
     *
     * @param string $time Em minutos, quanto tempo é permitida a inatividad
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function expireTime($time = ""){
        if( !empty($time) ){
            $this->expireTime = $time;
        } else {
            return $this->expireTime;
        }
    }

    /**
     * MÉTODOS DE VERIFICAÇÃO
     */
    /**
     * checkLogin()
     *
     * Verifica se o usuário está logado
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    protected function checkLogin(){
        /**
         * Verifica se usuário está logado
         */
        if( !empty($_SESSION["Sys"]["Auth"]["logged"]) AND $_SESSION["Sys"]["Auth"]["logged"] == 1 ){
            $this->logged = true;
        } else {
            $this->logged = false;
        }
    }
}
?>