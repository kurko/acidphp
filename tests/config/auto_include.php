<?php
require_once 'tests/config/database.php';

require_once 'core/engine/functions/core_functions.php';

if( !defined('UPLOAD_DIR') )
    define('UPLOAD_DIR', 'tests/test_files/uploaded_files/');

if( !defined('THIS_TO_BASEURL') )
    define('THIS_TO_BASEURL', '');

function __autoload($className) {

    $classDirs = array(
        'core/engine/class/'.$className.'.php',
        'core/engine/class/helpers/'.$className.'.php',
        'core/engine/class/components/'.$className.'.php',
        'core/engine/class/behaviors/'.$className.'.php',
    );

    $found = false;
    foreach( $classDirs as $dir ){
        if( is_file('core/engine/class/'.$className.'.php') ){
            require_once 'core/engine/class/'.$className.'.php';
            continue;
        }
    }

}
?>
