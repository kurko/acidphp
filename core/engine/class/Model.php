<?php
/**
 * Arquivo que representa a estrutura controller de um MV
 *
 * @package MVC
 * @name Controller
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.1
 * @since v0.1.5, 22/06/2009
 */
class Model
{
    /**
     * CONEXÃO
     *
     * @var <type>
     */
    private $conn;

    function  __construct($params = "") {
        $this->conn = ( empty($params["conn"]) ) ? '' : $params["conn"];
    }

    public function saveAll($data){
        pr($data);
    }



}

?>