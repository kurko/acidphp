<?php
class Idade extends AppModel {
    
    public $useTable = "idades";

    var $belongsTo = array(
        "Usuario" => array(
            "foreignKey" => "usuario_id"
        ),
    );
    
}

?>