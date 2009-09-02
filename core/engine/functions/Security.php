<?php
/**
 * Classe responsável por tratar variáveis de forma a manter a segurança do
 * sistema.
 *
 * @package Core Functions
 * @name Security
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 06/08/2009
 */
class Security
{
    /**
     * Sanitize()
     *
     * Responsável por tratar variáveis enviadas por usuários através de
     * formulários e outros.
     *
     * Insere barras e aspas onde necessário para evitar SQLInjection, entre
     * outros
     *
     * @param mixed $data Variável a ser tratada
     * @param array $options [opcional] Configurações de ação do método
     * @return mixed
     */
    static function Sanitize($data, $options = array()){

        /**
         * Strings
         */
        if( is_string($data) or is_int($data) ){

            // Barras antes de aspas
            $data = addslashes($data);

            $result = $data;
            
        }
        /**
         * Array
         */
        else if( is_array($data) ){

            /**
             * Vai varrer toda a array, tratando cada valor e guardar em $result
             */
            $result = array();

            /**
             * checkLooper()
             *
             * Serve para varrer uma array para tratá-la. Esta função
             * possibilita varrer um array com recursividade infinita.
             *
             * @param array $array Variável a ser tratada
             * @return array Todos os itens de uma array tratados com Sanitize()
             */
            /**
             * Inicializa varredura
             */
            $result = Security::checkLooper($data);

        } else {

            return $data;
            /**
             * @todo - implementar
             */
            trigger_error("Sanitize não totalmente implementado", E_USER_ERROR);
        }
        
        /**
         * Retorna variável tratada
         */
        return $result;
    } // fim Sanitize()

    function checkLooper($array){

        foreach( $array as $chave=>$valor ){
            /**
             * Sendo o valor encontrado dentro da array outra array,
             * chama novamente checkLooper() (esta mesma função) para
             * seguir a varredura em subArrays.
             */
            if( is_array($valor) ){
                $result[$chave] = Security::checkLooper($valor);
            }
            /**
             * O nó atual da array não é array, portanto basta tratá-la.
             */
            else {
                /**
                 * Devido a $result[$chave] ser igual a
                 * Sanitize($valor), não é necessário implementar esta
                 * função cada vez que se necessitar alterar Sanitize().
                 *
                 * Basta alterar Sanitize() onde ocorre o tratamento de
                 * strings.
                 */
                $result[$chave] = Security::Sanitize($valor);
            }

        }

        return $result;
    }
}
?>