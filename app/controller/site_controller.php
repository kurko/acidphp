<?php
/**
 * Controller padrão da aplicação
 *
 * @since v0.1 18/07/2009
 */

class SiteController extends AppController
{

    var $uses = array('Categoria'); // change this to whatever models you have
    var $siteTitle = "AcidPHP";
    var $components = array();

    function index(){
        $this->autoRender = false;
        $this->pageTitle = "Página principal";

    }

    function save(){

    }
}
?>