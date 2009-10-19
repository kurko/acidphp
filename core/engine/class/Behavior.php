<?php
/**
 * Arquivo que representa a estrutura BEHAVIOR de um MODEL
 *
 * @package Model
 * @name Behavior
 * @author Lucas Pelegrino <lucaswxp@hotmail.com>
 * @since v0.0.6, 16/10/2009
 */
class Behavior
{
    /**
     * Objeto da classe (Model) chamadora
     *
     * @var Object Model que carregou este behavior
     */
    public $model;

    function __construct($model){
        
        $this->model = &$model;
    }
}
?>