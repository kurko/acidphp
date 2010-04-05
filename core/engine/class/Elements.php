<?php
/**
 * ELEMENTS
 *
 * Arquivo responsável por carregar Elements.
 *
 * @package View
 * @name Elements
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.2, 04/04/2010
 */
class Elements {

    function __construct(){
    }

    /**
     * getInstance()
     *
     * @staticvar <object> $instance
     * @return <object>
     */
    public function getInstance(){
        static $instance;

        if( empty($instance[0]) ){
            $instance[0] = new Elements();
        }

        return $instance[0];
    }

    /**
     * load()
     *
     * Carrega um element no corpo de um view.
     *
     * @param <string> $element
     * @return <string>
     */
    function load($element){
        ob_start();
        include(APP_VIEW_DIR."elements/".$element.".php");
        $elementContent = ob_get_contents();
        ob_end_clean();
        if( !empty($elementContent) ){
            echo $elementContent;
            return $elementContent;
        } else {
            return false;
        }
    }

}
?>