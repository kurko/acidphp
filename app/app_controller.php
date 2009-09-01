<?php
class AppController extends Controller
{
    //var $components = array("Auth");
    var $helpers = array("Html", "Form", "Paginator");

    function beforeFilter(){

        /**
        $this->auth->allow(array(
            "site" => array(
                "index","outro"
            )

        ));

        $this->auth->model("Usuario");
         * 
         */

    }
    
}
?>