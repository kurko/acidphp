<?php
/**
 * HELPER FORM
 *
 * Contém gerador de elementos FORM automáticos.
 *
 * Sua construção foi inicializada no AustCMS.
 *
 * @package Helpers
 * @name Form
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 19/07/2009
 */
class FormHelper
{

    protected $modelName;

    function __construct(){

    }

    /**
     * Inicializa um formulário HTML
     *
     * @param string $modelName Nome do Model responsável pelo tratamento dos
     *                          dados
     * @param array $options Opções de configuração e amostragem do formulário
     * @return string Código HTML contendo a abertura do formulário <form>
     */
    public function create($modelName, $options = ''){
        $conteudo = "";

        /**
         * Ajusta o nome do model no objeto instanciado
         */
        if( !empty($modelName) ){
            $this->modelName = $modelName;
        }

        /**
         * Controller
         *
         * Verifica se o usuário especificou um controller que deverá
         * ser usado para salvar as informações do formulário
         */
        $controller = (empty($options["controller"])) ? $this->modelName : $options["controller"];
        /**
         * Action
         *
         * O action padrão para salvar um formulário é 'save'
         */
        $action = (empty($options["action"])) ? 'save' : $options["action"];

        /**
         * ABRE FORMULÁRIO
         */
        $conteudo.= '<form method="post" action="'.WEBROOT.''.$controller.'/'.$action.'" class="formHelper">';

        /**
         * INPUTS HIDDEN
         *
         * Mostra todos os inputs type=hidden se existir
         */
        if( !empty($options["hidden"]) ){
            foreach( $options["hidden"] as $chave=>$valor){
                if( is_string($valor) ){
                    $conteudo.= $valor;
                }
            }
        }

        /**
         * MODEL PRINCIPAL
         */
        if( !empty($modelName) ){
            $conteudo.= '<input type="hidden" name="modelName" value="'.$modelName.'" />';
        }
        /**
         * Indica que é formulário de um FormHelper
         */
        $conteudo.= '<input type="hidden" name="sender" value="formHelper" />';

        return $conteudo;

    }
    /**
     * Cria inputs de formulários HTML automaticamente, necessitando somente
     * indicar o nome do campo relacionado na base de dados.
     *
     * @param string $fieldName Nome do campo no banco de dados.
     * @param array $options Opções de configurações e amostragem.
     *      "label" string :
     *      "value" string : Valor padrão do campo
     *      "select" array :
     *      ""
     * @return string Código HTML para o form input pedido.
     */
    public function input($fieldName, $options = ''){
        $conteudo ='';

        $conteudo.= '<div class="input">';

        /**
         * ANÁLISE DE OPTIONS
         */
        /**
         * ["label"]
         *
         * Se Label não foi especificado
         */
        if( empty($options["label"]) ){
            $conteudo.= '<label for="input-'.$fieldName.'">'.$fieldName.'</label>';
        } else {
            $conteudo.= '<label for="input-'.$fieldName.'">'.$options["label"].'</label>';
        }

        /**
         * ["select"]
         * 
         * Tipos de campos
         */
        if( !empty($options["select"]) ){
            $inputType = "select";
        } else {
            $inputType = "text";
        }
        /**
         * ["value"]
         *
         * Padrão padrão do input
         */
        $fieldValue = ( empty($options["value"]) ) ? "" : 'value="'.$options["value"].'" ';


        /**
         * PROPRIEDADES-PADRÃO
         */
        $standardAtrib = 'id="input-'.$fieldName.'" '. $fieldValue;

        /**
         * NOMES DO INPUTS
         *
         * Gera nomes para os inputs
         */
        /**
         * Verifica se o Model é padrão ou foi especificado algum outro
         */
        $dotPos = strpos($fieldName, "." );
        if( $dotPos === false ){
            /**
             * É o model padrão
             */
            $modelName = $this->modelName;
        }
        /**
         * Outro Model foi especificado
         */
        else {
            $modelName = substr( $fieldName, 0, $dotPos );
            $fieldName = substr( $fieldName, $dotPos+1, 100 );
        }

        /**
         * Define o nome do input
         */
        if( !empty($modelName) ){
            $inputName = "data[".$modelName."][".$fieldName."]";
        } else {
            $inputName = "data[".$fieldName."]";
        }

        /**
         * ANÁLISE DE TIPOS DE CAMPOS
         */

        /**
         * Mostra inputs de acordo com o especificado
         */
        /**
         * INPUT TEXT
         */
        if( $inputType == "text" ){
            $conteudo.= '<div class="input_field input_text">';
            $conteudo.= '<input type="text" name="'.$inputName.'" '.$standardAtrib.' />';
        }
        /**
         * INPUT <SELECT>
         *
         * Pega as informações de $options["select"] e cria
         * um <select> com vários <option></option>
         */
        else if( $inputType == "select" ){
            $select = $options["select"];
            /**
             * <option> selecionado
             */
            $selectSelected = $select["selected"];
            /**
             * Opções a serem mostradas
             */
            $selectOptions = $select["options"];
            $conteudo.= '<div class="input_field input_select">';
            $conteudo.= '<select name="'.$inputName.'" '.$standardAtrib.'>';
            /**
             * Loop pelo select criando <options>
             */
            foreach($selectOptions as $chave=>$valor){
                /**
                 * Verifica se o <option> atual deve ser selecionado por
                 * padrão
                 */
                if( !empty($selectSelected) AND $selectSelected == $chave ){
                    $selectThis = 'selected="true"';
                } else {
                    $selectThis = false;
                }
                $conteudo.= '<option '.$selectThis.' value="'.$chave.'">'.$valor.'</option>';
            }

            $conteudo.= '</select>';
        }
        /*
         * Não é nenhum tipo de campo pré-definido.
         *
         * Imprime campo com mensagem de erro para que o desenvolvedor veja
         * a falha.
         */
        else {
            $conteudo.= '<div class="input_field input_text">';
            $conteudo.= '<input type="text" name="'.$inputName.'" value="ERRO NO TIPO DE CAMPO" '.$standardAtrib.'>';
        }
        $conteudo.= '</div>'; // fecha div do .input_field

        $conteudo.= '</div>'; // fecha div do .input

        return $conteudo;
    }

    /**
     * Termina um formulário, inserindo o campo Submit e fechando o bloco de
     * código HTML <form></form>.
     *
     * @param string $submitValue Valor a ser escrito no botão de Submit
     * @param array $options Opções de amostragem e configurações
     * @return string Código HTML para finalizar bloco de código <form></form>
     */
    public function end($submitValue = "Enviar", $options = ""){
        $conteudo = '';
        $conteudo.= '<input type="submit" name="formSubmit" value="'.$submitValue.'" class="submit" />';
        $conteudo.= '</form>';
        return $conteudo;
    }

}

?>