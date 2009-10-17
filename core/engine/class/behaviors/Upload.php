<?php
/**
 * Behavior para Upload de arquivos
 *
 * @package Behaviors
 * @name Upload
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.0.6, 16/10/2009
 */
class UploadBehavior extends Behavior
{
    function __construct($model) {
        parent::__construct($model);
        pr( $this->model );
    }

    public function upload($file){


        
    }


}
?>