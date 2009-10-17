<?php

class Behavior
{
    // Objeto da classe (Model) chamadora
    protected $Model;

    function __construct($model){
        $this->Model = $model;
    }
}
?>