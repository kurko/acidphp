<?php
/**
 * HELPER
 *
 * Classe que contém funções nativas a todos os Helpers.
 *
 * @package Helper
 * @name Helper
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 03/08/2009
 */
class Helper
{
    /**
     *
     * @var array Parâmetros do sistema como $_POST, $_GET, Controller, Action
     */
    protected $params;
    /**
     *
     * @var array Dados vindos de um formulário
     */
    protected $data = array();

    /**
     * Cada classe contém um versão. Esta versão é descrita nesta variável.
     *
     * @var string Versão desta classe
     */
    protected $version;


    function __construct($params = ""){

        /**
         * Inicialização de variáveis
         */
        /**
         * $this->params
         */
        $this->params = ( empty($params["params"]) ) ? array() : $params["params"];
        /**
         * $this->data
         */
        $this->data = ( empty($params["data"]) ) ? array() : $params["data"];
    }

    /**
     * MÉTODOS DE INFORMAÇÕES SOBRE CLASSE
     */
    /**
     * Cada classe contém um versão. Este método retorna a versão desta
     * classe.
     *
     * @return string Versão desta classe
     */
    public function version(){
        return $version;
    }
}

?>