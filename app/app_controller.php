<?php
class AppController extends Controller
{
    var $components = array("Auth");
    var $helpers = array("Html", "Form");

    function beforeFilter(){
        
        $this->auth->allow(array(
            "site" => array(
                "index"
            ),
        ));

        $this->auth->redirectTo( array("controller" => "site", "action" => "index") );
        $this->auth->loginPage( array("controller" => "site", "action" => "login") );
        $this->auth->errorMessage("Seus dados estão incorretos!");
        $this->auth->deniedMessage("Você não tem permissão de acesso!");
        $this->auth->model("Usuario");

    }
    
}
?>