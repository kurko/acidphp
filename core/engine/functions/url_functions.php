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
 * @global Object $dispatcher Objeto inicializador do sistema
 * @param mixed $mixed Configuração de Url para criação de string final
 * @return string Retorna um endereço Url válido
 */
function translateUrl($mixed, $isFile = false){

    $dispatcher = Dispatcher::getInstance();
    /**
     * $mixed é array
     */
    if( is_array($mixed) ){

        /*
         * Define APP
         */
        if( isset($mixed["app"])
            AND is_string($mixed["app"]) ){
            $app = $mixed["app"]."/";
        } else {
            $app = "";
        }

        $controller = ( empty($mixed["controller"]) ) ? "" : $mixed["controller"];
        $action = ( empty($mixed["action"]) OR empty($controller) ) ? "" : $mixed["action"];
		if( !empty($action) )
			$action = '/'.$action;
		
        $args = ( empty($mixed[0]) OR empty($action) ) ? "" : $mixed[0];

        if( isset($args[0]) AND $args[0] != "/" ){
            $args = "/".$args;
        }

        /*
         * Se app é vazio mas existe, acessa app sem nome, que é automaticamente
         * levado para app/.
         */
        if( !empty($app) ){
            $rootDir = ROOT;
            if( $app == "/" )
                $app = "";
        } else {
            $rootDir = $dispatcher->webroot;
        }

        $url = str_replace("//", "/", $rootDir.$app.$controller.$action.$args);
    }
    /**
     * $mixed é string
     */
    else if( is_string($mixed) ){

        /*
         * A URL é uma string mas não contém nenhum dos termos a seguir,
         * ou seja, é um link para uma página interna.
         */
        if( !in_array(  StrTreament::getNameSubStr($mixed, ":"),
                        array("http","ftp","ssh","git","https") )
            AND (!$isFile)
        )
        {

			/* Strips webroot off the beginning of the url */
			$pos = strpos($mixed, $dispatcher->webroot);
			if( $pos === 0 )
				$mixed = substr($mixed, mb_strlen($dispatcher->webroot) );
				
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

			
            /*
             * Se não foi especificado um action
             */
            if( !empty($action) )
                $action = "/".$action;
			else
				$action = '';
				
			$argsStr = implode("/", $args);
            if( !empty($argsStr) )
                $argsStr = "/".$argsStr;

            $url = $dispatcher->webroot.$controller.$action.$argsStr;
        }
        /*
         * A URL é para um arquivo (css, js, imagem, etc)
         */
        else if( $isFile ){

            $url = $dispatcher->webroot.$mixed;

        } else {
            $url = $mixed;
        }
    }
    $url = str_replace("//", "/", $url);

    return $url;
}

/**
 * Redireciona o cliente para o endereço $url indicado.
 *
 * Se $url é uma array, trata-a para um endereço válido
 *
 * @param string $url Endereço Url válido a ser aberto
 * @param mixed $options Se string, é um notice. Caso contrário, é array com
 * 						várias opções
 * @return boolean Retorna falso se não conseguir redirecionar
 */
function redirect($url, $options = ""){
    /**
     * Segurança: se $url for array
     */
    $url = translateUrl($url);

	if( !empty($options) &&
		is_string($options) )
	{
		$_SESSION['notice'] = array(
			'message' => $options,
			'url' => $url
		);
	}

    /**
     * Redireciona
     */
    if( !empty($url) ){
        //header("Status: 200"); if needed for IE6
        header("Location: ". $url);
        exit();
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