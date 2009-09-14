<?php
/**
 * Funções de tratamento de URLs
 *
 * @package Core
 * @category Funções
 * @name URL Functions
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1
 */

/**
 * translateUrl() traduz uma URL
 *
 * @global Object $engine Objeto inicializador do sistema
 * @param mixed $mixed Configuração de Url para criação de string final
 * @return string Retorna um endereço Url válido
 */
function translateUrl($mixed, $isFile = false){

    global $engine;
    /**
     * $mixed é array
     */
    if( is_array($mixed) ){
        $controller = ( empty($mixed["controller"]) ) ? $engine->callController : $mixed["controller"];
        $action = ( empty($mixed["action"]) ) ? "index" : $mixed["action"];
        $args = ( empty($mixed[0]) ) ? "" : $mixed[0];

        if( isset($args[0]) AND $args[0] != "/" ){
            $args = "/".$args;
        }

        $url = $engine->webroot.$controller."/".$action.$args;
    }
    /**
     * $mixed é string
     */
    else if( is_string($mixed) ){

        /*
         * A URL é uma string mas nenhum dos termos a seguir.
         */
        if( !in_array(  StrTreament::getNameSubStr($mixed, ":"),
                        array("http","ftp","ssh","git","https") )
            AND (!$isFile)
        ){

            $url = explode("/", $mixed);
            $args = array();
            $i = 0;
            foreach( $url as $chave=>$valor ){
                if( empty($valor) ){
                    unset($url[$chave]);
                } else {
                    if( $i == 0 ){
                        $controller = $valor;
                    } else if( $i == 1 ){
                        $action = $valor;
                    } else {
                        $args[] = $valor;
                    }
                    $i++;
                }
            }

            $url = $engine->webroot.$controller."/".$action."/".implode("/", $args);
        }
        /*
         * A URL é para um arquivo (css, js, imagem, etc)
         */
        else if( $isFile ){

            $url = $engine->webroot.$mixed;

        } else {
            $url = $mixed;
        }
    }
    return $url;

    return false;
}

/**
 * Redireciona o cliente para o endereço $url indicado.
 *
 * Se $url é uma array, trata-a para um endereço válido
 *
 * @param string $url Endereço Url válido a ser aberto
 * @return boolean Retorna falso se não conseguir redirecionar
 */
function redirect($url=""){
    /**
     * Segurança: se $url for array
     */
    if( is_array($url) ){
        $url = translateUrl($url);
    }

    /**
     * Redireciona
     */
    if( !empty($url) ){
        header("Location: ". $url);
        return false;
    } else {
        return false;
    }
}

/**
 * substituteUrlTerm()
 *
 * Toma uma URL (atual) e cria uma nova, substituindo valores desejados.
 *
 * @param string $oldTerm Termo a ser substituído
 * @param string $newTerm Termo a ser inserido na URL
 * @param string $url URL atual
 * @return string Nova URL criada
 */
function substituteUrlTerm($oldTerm, $newTerm, $url){

    /**
     * Quebra a URL em uma array para substituição mais facil
     */
    $urlSlices = explode("/", $url);
    $urlSlices = array_diff($urlSlices, array("") );

    $newTerm = str_replace("/", "", $newTerm);
    $oldTerm = str_replace("/", "", $oldTerm);


    /**
     * Há page:n na url
     */
    if( in_array($oldTerm, $urlSlices) ){
        $urlSlices = str_replace($oldTerm, $newTerm, $urlSlices);
        $newUrl = implode("/", $urlSlices);
    } else {
        array_push($urlSlices, str_replace("/", "", $newTerm) );
        $newUrl = implode("/", $urlSlices);
    }


    return "/".$newUrl;
}

?>