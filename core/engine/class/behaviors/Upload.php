<?php

class UploadBehavior extends Behavior
{
    function __construct($model) {
        parent::__construct($model);
        pr( $this->model );

    }
}
?>