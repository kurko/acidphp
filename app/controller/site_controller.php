<?php
/**
 * Controller padrão da aplicação
 *
 * @since v0.1 18/07/2009
 */

class SiteController extends AppController
{

    var $uses = array("Usuario"); // change this to whatever models you have
    var $siteTitle = "AcidPHP";

    function index(){
        $this->pageTitle = "Página principal";

    }

}
?>