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
class HtmlHelper extends Helper
{

    public function link($linkText, $linkDestination, $options=array()){

        $inlineProperties = "";

        /**
         * Opções reservadas. Todas $options serão elementos inline da tag,
         * exceto os índices abaixo.
         */
        $reservedOptions = array();
        /**
         * Analisa cada valor de $options
         */
        foreach($options as $chave=>$valor){
            if( !in_array($chave, $reservedOptions) ){
                $inlineProperties.= ' '.$class.'="'.$valor.'"';
            }
        }

        echo '<a href="'.translateUrl($linkDestination).'" '.$inlineProperties.'>'.$linkText.'</a>';
    }

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