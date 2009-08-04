<?php
/**
 * COMPONENT AUTH
 *
 * Automatiza sistema de login no sistema.
 *
 * @package Components
 * @name Auth
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 04/08/2009
 */
class AuthComponent extends Component
{
    protected $allow = array(
        "site" => array(
            "index", "login"
        ),
    );

    function __construct($params = ""){
        parent::__construct($params);

        pr($this->params);
    }

    public function allow($allow){
        $this->allow = $allow;
    }
}
?>