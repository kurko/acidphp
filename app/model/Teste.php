<?php
/**
 * MODEL NAME
 *
 * Use this line to comment your Model
 *
 * @package Model
 * @name Your Model Name
 * @author Your name here <youremail@here.com>
 * @since v0.x.x xx/xx/xxxx
 */
class Teste extends AppModel
{
    /*
     * Which table this model represents
     */
    var $useTable = ""; // specify one (eg. users, comments)

    /*
     * VALIDATION
     */
    var $validation = array(
        "field" => array( // change "field" value to a field name you wish
            "rule" => "notEmpty", // which rule is set to this field
            "message" => "This field cannot be empty",
        )
    );

    /*
     * Model relationships
     *
     * Erase $hasMany and $hasOne if this model presents no relationship
     */
    var $hasMany = array(
        'model' => array(
            'foreignKey' => 'modelname_id',
        ),
    );

    var $hasOne = array(
        'mode_name' => array(
            'foreignKey' => 'modelname_id',
        ),
    );
}
?>