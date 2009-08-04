<?php
class AppController extends Controller
{
    var $components = array("Auth");

    function beforeFilter(){
        
        $this->auth->allow(array(
            "site" => array(
                "outro", "index"
            ),
        ));

        $this->auth->loginPage( array("controller" => "site", "action" => "login") );
        
    }
    
}
?>