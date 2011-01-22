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
     *
     * @var array Contém dados do usuário logado
     */
    public $user;
    /**
     * Loaded Models
     *
     * @var array Contém quais models devem ser carregados. Se nenhum for
     * especificado, carrega todos.
     */
    public $userModels = array();


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
         * @var mixed Endereço para onde deve ser redirecionado o usuário após login
         */
        public $redirectTo;

        /**
         *
         * @var mixed Endereço para onde deve ser redirecionado o usuário após
         * logout. Pode ser formato array ou string
         */
        public $logoutRedirectTo;

        /**
         *
         * @var array Indica quais são os campos de login padrão
         */
        protected $loginFields = array(
            "username" => "username",
            "password" => "password"
        );

        public $requiredFields = array();

    /*
     * CONFIGURAÇÕES INTERNAS
     */

    /**
     *
     * @var int Quantos campos devem ser enviados no mínimo num formulário
     * (exceto se especificado $this->requiredFields)
     */
    public $_minimumSentFields = 2;

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
     * beforeBeforeFilter()
     *
     * Cria variáveis importantes para os controllers, como carregar os dados
     * dos usuários.
     *
     * @return bool
     */
    public function beforeBeforeFilter(){
        /*
         * LOGADO
         *
         * Verifica se o usuário logado e salva suas informações em
         */
        if( $this->checkLogin() ){

            /*
             * Guarda informações sobre o usuário atual
             */

            if( !empty($_SESSION["Sys"]["Auth"]["user"]) ){
                $this->user = $_SESSION["Sys"]["Auth"]["user"];
            }

        }

        return true;
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

		if( empty($this->models[$this->model()]) ){
			
			pr( $this->model() );
			$this->models[$this->model()] = $this->controller->loadModel($this->model());
			exit();
		}
		
        /*
         * 
         * EXPIRE TIME
         *
         */
        /*
         * Se foi configurado um tempo limite da sessão autenticada
         */
        if( !empty($this->expireTime) OR $this->expireTime > 0 ){

            if( !empty($_SESSION["Sys"]["Auth"]["startMicrotime"]) ){

                $expireTimeInSec = $this->expireTime * 60;

                /*
                 * Sets the session lifetime
                 */
                try {
                    ini_set("session.gc_maxlifetime", $expireTimeInSec); // in seconds
                } catch(exception $e){

                }

                $elapsedTime = microtime(true) - $_SESSION["Sys"]["Auth"]["startMicrotime"];

                if( $elapsedTime > $expireTimeInSec ){
                    $this->logout();
                    $actionCommand = "logout";
                }
            }

            /*
             * Update actual microtime
             */
            $_SESSION["Sys"]["Auth"]["startMicrotime"] = microtime(true);
        }
        /*
         * Não foi especificado um tempo limite da sessão, então é infinito
         * (até o browser ser fechado)
         */
        else {
            try {
                ini_set("session.gc_maxlifetime", 0); // in seconds
            } catch(exception $e){

            }
        }

        /**
         * LOGOUT
         *
         * Redireciona para $this->loginPage
         */
        if( $this->params["action"] == "logout" or $actionCommand == "logout" ){
            return $this->logout();
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
                    $dataFields = $data;

                    /**
                     * Cria conditions para SQL
                     */
                    /*
                     * Loop por cada model passado
                     */
                    $sentFields = array();
                    foreach( $dataFields as $fieldModel=>$campos ){
                        /*
                         * Loop por cada campo
                         */
                        foreach( $campos as $campo=>$valor ){
                            if( array_key_exists( $campo, $this->models[$fieldModel]->tableDescribed ) ){
                                $conditions[$fieldModel.'.'.$campo] = $valor;
                                /*
                                 * Indica quantos campos foram enviados.
                                 */
                                $sentFields[$fieldModel][$campo] = true;
                            }
                        }
                    }

                    $processStatus = $this->_checkSentFields( $dataFields );

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
                    if( !empty($result)
                        AND count($result) > 0
                        AND $processStatus )
                    {

						$this->login( reset($result) );

                        
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
                            redirect( $newUrl );
                        } else {
                            redirect( $this->redirectTo() );
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
			/*
			$target = str_replace(WEBROOT, '', translateUrl( $this->loginPage() ) );
			$dispatcher = Dispatcher::getInstance();
			$url  = str_replace(WEBROOT, '', $dispatcher->url );
			*/

            return true;
        }

    } // fim afterBeforeFilter()


	public function login($result){

        $this->logged = true;

        $_SESSION["Sys"]["Auth"]["logged"] = true;
        $_SESSION["Sys"]["Auth"]["startMicrotime"] = microtime(true);

        /*
         * Carrega dados do usuário e guarda em $auth->user
         */
        if( $this->userModels === false ){
            $_SESSION["Sys"]["Auth"]["user"] = array();
        } else if( empty($this->userModels) ){
            $_SESSION["Sys"]["Auth"]["user"] =
                $result;
        } else {
            if( is_array($this->userModels) ){
                foreach( $this->userModels as $modelToLoad){
                    if( !empty($result[$modelToLoad]) ){
                        $_SESSION["Sys"]["Auth"]["user"][$modelToLoad] =
                            $result[$modelToLoad];
                    }
                }
            } else {
                $_SESSION["Sys"]["Auth"]["user"][$this->model()] =
                    $result[$this->model()];
            }
        }
		
	}
    /**
     * logout()
     *
     * Força o Logout
     *
     * @return bool
     */
    public function logout($redir = true){
        unset($_SESSION["Sys"]["Auth"]);
        unset($_SESSION["Sys"]["FormHelper"]["statusMessage"]);
        $this->checkLogin();

        if( $redir ){
            if( !empty($this->logoutRedirectTo) )
                redirect( translateUrl( $this->logoutRedirectTo ) );
            else
                redirect( translateUrl( $this->loginPage() ) );
            return false;
        } else {
            return true;
        }
    }

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
     * logoutRedirectTo()
     *
     * @param array $logoutRedirectTo Contém endereço para onde o usuário será
     *                          redirecionado após efetuar logout
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function logoutRedirectTo($logoutRedirectTo=""){
        if( !empty($logoutRedirectTo) ){
            $this->logoutRedirectTo = $logoutRedirectTo;
        } else {
            return $this->logoutRedirectTo;
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

	function lastPage($page = ''){
		if( empty($page) )
			return false;
		
		$_SESSION["Sys"]["Auth"]["autoRedirect"] = $page;
		return true;
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
     * requiredFields()
     *
     * Ajusta ou retorna quais campos devem ser enviados obrigatoriamente
     *
     * @param array $fields
     * @author Alexandre de Oliveira <chavedomundo@gmail.com>
     */
    public function requiredFields($fields = array()){
        if( !empty($fields) ){
            $this->requiredFields = $fields;
        } else {
            return $this->requiredFields;
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
        if( !empty($_SESSION["Sys"]["Auth"]["logged"])
            AND $_SESSION["Sys"]["Auth"]["logged"] == "1" )
        {
            $this->logged = true;
        } else {
            $this->logged = false;
        }
        return $this->logged;
    }

    /*
     *
     * MÉTODOS DE AÇÃO
     *
     */

    public function _checkSentFields($data = array(), $customRequiredFields = array() ){
        if( empty($data) )
            return false;

        /*
         * Loop por cada model
         */
        $numSentFields = 0;
        foreach( $data as $fieldModel=>$campos ){

            /*
             * Loop por cada campo do model
             */
            foreach( $campos as $campo=>$valor ){
                if( array_key_exists( $campo, $this->models[$fieldModel]->tableDescribed ) ){
                    $conditions[] = $fieldModel.'.'.$campo;

                    if( $this->model == $fieldModel )
                        $conditions[] = $campo;
                    /*
                     * Indica quantos campos foram enviados.
                     */
                    $numSentFields++;
                }
            }
        }

        if( empty($customRequiredFields) )
            $requiredFields = $this->requiredFields;
        else
            $requiredFields = $customRequiredFields;
            
        if( empty($requiredFields) ){
            if( $numSentFields < $this->_minimumSentFields )
                return false;
        } else {
             if( is_string($requiredFields) ){
                 $requiredFields = array($requiredFields);
             }

             foreach( $requiredFields as $campo ){
                 if( !in_array($campo, $conditions) ){
                     return false;
                 }
             }

        }

        return true;
    }
}
?>