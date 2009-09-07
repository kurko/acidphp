<?php
/**
 * HELPER AJAX
 *
 * Contém gerador de elementos Javascript para Ajax automáticos.
 *
 * @package Helpers
 * @name Ajax
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.0.5, 07/19/2009
 */
class AjaxHelper extends Helper {

    private $callbacks = array(
        'beforeSend',
        'complete',
        'error',
        'success'
    );

    var $_randoms_list = array();

    // after,append,appendTo,before,insertAfter,insertBefore,prepend,prependTo

    /**
     * linkToRemote()
     *
     * Cria um link HTML <a></a> com função Ajax embutida.
     *
     * @param string $text
     * @param array $options
     * @param array $htmlOptions
     * @return string
     */
    public function linkToRemote($text, array $options, $htmlOptions = null ){

        /*
         * Configura o id do elemento HTML. Se nenhum foi especificado, cria um.
         */
        $htmlOptions['id'] = isset( $options['id'] ) ? $options['id'] : $this->randomizeId();

        /*
         * Chama linkToFunction.
         */
        return $this->linkToFunction( $text, $this->remoteFunction($options), $htmlOptions );
    }

    /**
     * remoteFunction()
     *
     * Retorna somente o código Javascript para a função Ajax (sem html).
     *
     * @param array $options
     * @return bool
     */
    public function remoteFunction(array $options){

        $javascript_options = $this->setOptionsForAjax($options);

        /*
         * Sintaxe padrão de uma função Ajax
         */
        $ajaxFunction = '$.ajax({'.$javascript_options.'})';

        /*
         * Verifica se não foi passada nenhuma outra restrição.
         */
        $ajaxFunction = ( isset($options['before']) ) ?  $options['before'].';'.$ajaxFunction : $ajaxFunction;
        $ajaxFunction = ( isset($options['after']) ) ?  $ajaxFunction.';'.$options['after'] : $ajaxFunction;
        $ajaxFunction = ( isset($options['condition']) ) ? 'if( '.$options['condition'].') {'.$ajaxFunction.'}' : $ajaxFunction;
        $ajaxFunction = ( isset($options['confirm']) ) ? 'if(  confirm("'.$options['confirm'].'" ) ) { '.$ajaxFunction.' } ':$ajaxFunction;

        return $ajaxFunction;
    }
    /**
     * linkToFunction()
     *
     * Cria um link HTML chamando uma função Javascript.
     *
     * @param string $text
     * @param string $function
     * @param array $htmlOptions
     * @return bool
     */

    public function linkToFunction($text, $function, $htmlOptions = null){

        $uid = ( isset($htmlOptions['id']) )  ? $htmlOptions['id'] : $this->randomizeId();

        $id_string = 'id="'.$uid.'"';

        return '<a href="'.((isset($htmlOptions['href']) ) ? $htmlOptions['href'] : '#').'" onclick=\''.((isset($htmlOptions['onclick']) ) ? $htmlOptions['onclick'].';':'').$function.'; return false;\'>'.$text.'</a>';
    }

    /**
     * escape()
     *
     * Por segurança, se necessário, filtra strings.
     *
     * @param string $str
     * @return string
     */
    function escape($str){
        $str = str_replace( array("\r\n","\n","\r"),array("\n"), $str);
        $str = addslashes($str);
        return $str;
    }

    /*
     * FUNÇÕES INTERNAS
     */
    /**
     * randomizeId()
     *
     * Cria um id randômico quando necessário
     *
     * @return <type>
     */
    private function randomizeId(){

        $salt = "abchefghjkmnpqrstuvwxyz0123456789";
        srand((double)microtime()*1000000);

        while(1) {
            $i = 0;
            $makepass = '';
            while ($i <= 6) {
                $num = rand() % 33;
                $tmp = substr($salt, $num, 1);
                $makepass = $makepass . $tmp;
                $i++;
            }

            if(!in_array($makepass,$this->_randoms_list)){
                $this->_randoms_list[] = $makepass;
                return  $makepass;
            }
        }
    }

    private function buildcallbacks($options){
        $callbacks=array();
        foreach ($options as $callback=>$code) {
            if( in_array($callback,$this->callbacks)) {
                $callbacks[$callback] = 'function(response){'.$code.'}';
            }
        }
        return $callbacks;
    }

    /**
     * private setOptionsForAjax()
     *
     * Configura as opções da função Ajax
     *
     * @param array $options
     * @return array
     */
    private function setOptionsForAjax($options){

        /*
         * Ajusta variáveis das funções Ajax
         */
        /*
         * 'url'
         */
        if( isset($options['url']) )  $jsOptions['url'] = '"'.$options['url'].'"';

        $html_update = ( isset($options['position'] ) ? $options['position'] : 'html');

        /*
         * SERIALIZE
         *
         * A partir da versão 1.2, o jQuery tem uma funcionalidade, o
         * Serialize(), que transforma os dados de um form em uma querystring
         * para o envio para o servidor.
         */
            /*
             * 'form' configurado
             */
            if( isset($options['form']) ){
                $jsOptions['data'] = '$(this.elements).serialize()';
            }
            /*
             * 'parameters': alguns parâmetros foram passados
             */
            elseif( isset($options['parameters'])){
                $jsOptions['data'] = '$("'.$options['submit'].'").serialize()';
            } elseif( isset($options['with'])) {
                $jsOptions['data'] =  '"'.$options['with'].'"';
            }
        /*
         * 'update'
         *
         * Funcionalidade que atualiza um elemento html automaticamente com
         * response.responseText
         */
        if( isset($options['update']) )
            $options['success'] = '$("'.$options['update'].'").'.$html_update.'(response);'.( isset($options['success'] ) ? $options['success'] : '' );

        $jsOptions = array_merge( $jsOptions, (is_array($options)) ? $this->buildcallbacks($options) : array() );
        /*
         * Opções de assincronismo
         */
        if( isset($options['async']) )
            $jsOptions['async'] = $options['async'];
        /*
         * 'type', indica o método da requisição (POST, GET). Note que PUT,
         * DELETE e outro podem não ser suportados por todos os browsers.
         */
        if( isset($options['type']) )
            $jsOptions['type'] = '"'.$options['type'].'"';
        /*
         * 'contentType'
         */
        if( isset($options['contentType']) )
            $jsOptions['contentType'] = '"'.$options['contentType'].'"';
        /*
         * 'dataType': html por padrão
         */
        $jsOptions['dataType'] = ( isset($options['dataType']) ) ? '"'.$options['dataType'].'"' : '"html"';
        /*
         * 'timeout'
         */
        if( isset($options['timeout']) ) 
            $jsOptions['timeout'] = $options['timeout'];
        /*
         * 'processData
         */
        if( isset($options['processData']) ) 
            $jsOptions['processData'] = $options['processData'];
        /*
         * 'ifModified'
         */
        if( isset($options['ifModified']) ) 
            $jsOptions['ifModified'] = $options['ifModified'];
        /*
         * 'global'
         */
        if( isset($options['global']) )
            $jsOptions['global'] = $options['global'];

        return $this->setOptionsForJavascript($jsOptions);
    }

    /**
     * private setOptionsForJavascript()
     *
     * Retorna uma string para o Javascript com as opções finais.
     *
     * @param array $options
     * @param bool $constants
     * @return string
     */
    private function setOptionsForJavascript($options){
        $return_val = '';

        /*
         * Certifica-se que as opções estão no formato array
         */
        if( is_array($options)) {
            foreach( $options as $var=>$val ){
                if( !empty($return_val) )
                    $return_val.= ', ';

                $return_val.= "$var: $val";
            }
        }
        return $return_val;
    }
}
?>