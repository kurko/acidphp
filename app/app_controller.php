<?php
class AppController extends Controller
{
    var $components = array("Auth");
    var $helpers = array("Html", "Form", "Paginator");

    function beforeFilter(){

        $this->auth->allow(array(
            "site" => array(
                "*"
            )

        ));

        // Após login com sucesso, para onde o usuário deve ser redirecionado
        $this->auth->redirectTo( array("controller" => "site", "action" => "index") );

        // Qual é a página de login
        $this->auth->loginPage( array("controller" => "site", "action" => "login") );

        $this->auth->model("Usuario");

    }
    
}
?>