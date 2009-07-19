<?php
/**
 * Controller padrão da aplicação
 *
 * @package Controller
 * @name Main
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 18/07/2009
 */

class SiteController extends AppController
{

    function index(){
    }

    function listar($id="nao", $id2="naotb"){


        $this->autoRender = false;
    }


}
?>