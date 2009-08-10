<?php
class UsuariosController extends AppController
{

    function index(){
        echo 'este é o index';
        $this->autoRender = false;
    }

    function index2(){
        echo 'este é o index2';
        $this->autoRender = false;
    }


}
?>