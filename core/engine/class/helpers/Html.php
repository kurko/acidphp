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
     * IMAGE()
     *
     * @param string $url Endereço da imagem
     * @param array $options
     * @return string HTML
     */
    public function image($url, array $options = array() ){

        /*
         * OPÇÕES RESERVADAS
         */
        $reservedWords = array(
            ""
        );

        if( !empty($url) ){

            $conteudo = "";

            $inlineProperties = "";

            if( is_array($options) ){
                foreach( $options as $chave=>$valor ){
                    /*
                     * Somente palavras não reservadas
                     */
                    if( !in_array($chave, $reservedWords) )
                        $inlineProperties.= " ".$chave.'="'.$valor.'"';
                }
            }

            $conteudo.= '<img src="'.translateUrl(APP_IMAGES_DIR.$url, true).'" '.$inlineProperties.' />';

            return $conteudo;
        } else {
            /*
             * @todo - retornar erro
             */
            showError("Não foi especificado o endereço da imagem para HtmlHelper::imagem()");
        }
    }
    /**
     * Cria e retorna o código HTML para carregar um arquivo CSS
     *
     * @param string $path Caminho para o arquivo CSS
     * @return string Código HTML para carregar o arquivo CSS especificado
     */
    public function css($path){
        $cssLink = '<link rel="stylesheet" href="'. WEBROOT . APP_CSS_DIR . $path .'.css" />';
        return $cssLink;
    }

    /**
     * javascript()
     *
     * Retorna a linha HTML para carregar um arquivo javascript.
     *
     * @param string $path
     * @return string
     */
    public function javascript($path){
        /*
         * Verifica se o arquivo existe em app/ ou no core/
         */
        if( file_exists(APP_JS_DIR.$path .'.js') ){
            $jsLink = '<script language="Javascript" src="'. WEBROOT.APP_JS_DIR.$path .'.js"></script>';
            return $jsLink;
        }
        /*
         * Verifica se a biblioteca já está no core do AcidPHP
         */
        else if( file_exists(CORE_JS_DIR.$path .'.js') ){
            $jsLink = '<script language="Javascript" src="'. WEBROOT.CORE_JS_DIR.$path .'.js"></script>';
            return $jsLink;
        }

        return false;
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