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
     * Cada classe contém um versão. Esta versão é descrita nesta variável.
     *
     * @var string Versão desta classe
     */
    protected $version;

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