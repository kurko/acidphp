<?php
/**
 * HELPER
 *
 * Html
 *
 * Contém geradores automáticos de elementos HTML
 *
 * @package Helpers
 * @name Html
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 19/07/2009
 */
class HtmlHelper
{

    /**
     * Cria e retorna o código HTML para carregar um arquivo CSS
     *
     * @param string $path Caminho para o arquivo CSS
     * @return string Código HTML para carregar o arquivo CSS especificado
     */
    public function css($path){
        $cssLink = '<link rel="stylesheet" href="'. WEBROOT.APP_CSS_DIR.$path .'.css" />';
        return $cssLink;
    }


}

?>