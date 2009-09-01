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

    function __construct($params = ""){
        parent::__construct($params);
    }

    /**
     * LINK()
     *
     * Cria uma âncora HTML automaticamente.
     *
     * @param string $linkText Texto a ser mostrado na tela.
     * @param mixed $linkDestination Endereço de envio (string ou array).
     * @param array $options Opções extras de amostragem e configuração.
     * @return bool
     */
    public function link($linkText, $linkDestination, array $options = array() ){

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

        return '<a href="'.translateUrl($linkDestination).'" '.$inlineProperties.'>'.$linkText.'</a>';
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

    /**
     * METATAGS()
     *
     * Função que monta metatags automaticamente.
     *
     * @return string Retorna todas as metatags justapostas, sem espaço.
     */
    public function metatags(){
        
        $conteudo = "";
        /**
         * $metas contém todas as metatags configuradas no controller
         */
        $metas = $this->environment["metaTags"];

        /**
         * Se de fato há metatags
         */
        if( !empty($metas) ){
            if( empty($type) ){

                /**
                 * Analisa se são várias metatags ou somente uma
                 */
                $chaves = array_keys($metas);

                $multipleMetaTags = false;
                foreach( $chaves as $chave ){
                    //var_dump
                    if( (is_int($chave) AND $chave >= 0) OR (is_array($chave)) )
                        $multipleMetaTags = true;
                }

                /**
                 * Múltiplas metatags
                 */
                if( $multipleMetaTags ){
                    foreach( $metas as $meta ){
                        $conteudo.= "<meta ";
                        foreach($meta as $chave=>$valor){
                            $conteudo.= " ".$chave.'="'.$valor.'"';
                        }
                        $conteudo.= " />";
                    }
                }
                /**
                 * É apenas uma metatag
                 */
                else {
                    $conteudo.= "<meta ";
                    foreach($metas as $chave=>$valor){
                        $conteudo.= " ".$chave.'="'.$valor.'"';
                    }
                    $conteudo.= " />";
                }

            }
        }

        return $conteudo;
    } // fim metatag()

}

?>