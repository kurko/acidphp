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
class FormHelper extends Helper
{

    protected $modelName;
    /**
     *
     * @var array Contém as propriedades do respectivo Model objeto
     */
    protected $modelProperties;

    function __construct(){

    }

    /**
     * create()
     *
     * Inicializa um formulário HTML e um novo objeto de formulário.
     *
     * Cada formulário é criado sob o domínio de um Model. Estas informações
     * ficam guardadas em $this->modelProperties, que retém propriedades
     * relacionadas a cada model.
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
         * DescribedTables
         *
         * Carrega as tabelas descritas deste respectivo Model deste $form
         */
        global $describedTables;
        $this->modelProperties["describedTables"] = $describedTables;

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
         * ANÁLISE DO ARGUMENTO $OPTIONS
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
        $standardAtrib = 'id="input-'.$fieldName.'" ';
        $standardAtribValue = $fieldValue;

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
         * Se não há tipos especificados na configuração do $form, verifica qual
         * o tipo de campo na tabela e mostra o campo <input> de acordo
         */
        if( empty($inputType) ){
            /**
             * Verifica global $describedTables que já está carregado e salvo
             * em $this->modelProperties[$modelName]["describedTables"]
             */
             $physicalType = $this->modelProperties["describedTables"][$modelName][$fieldName]["Type"];
             /**
              * Esta variável com nome gigante tem a posição do primeiro
              * parêntesis no nome físico deste campo na tabela do banco de
              * dados. Isto serve para tomar o nome do tipo de dados somente.
              *
              * Ex.:
              *     - varchar() -> pega somente "varchar"
              *     - tinyint() -> pega somente "tinyint"
              */
             //pr($this->modelProperties[$this->modelName]["describedTables"]);
             $physicalTypeNameParenthesisPos = strpos( $physicalType, "(" );
             if( $physicalTypeNameParenthesisPos > 0 ){
                 $physicalType = substr( $physicalType, 0, $physicalTypeNameParenthesisPos );
             }

             /**
              * Segundo o tipo de dado físico de cada campo na tabela,
              * instancia um tipo para um campo do formulário.
              */
             switch($physicalType){
                 /**
                  * Textos pequenos como varchar
                  */
                 case "varchar"     : $inputType = "text"; break;
                 /**
                  * Textos grandes como text, blob
                  */
                 case "text"        : $inputType = "textarea"; break;
                 case "blob"        : $inputType = "textarea"; break;
                 /**
                  * Números como int
                  */
                 case "int"         : $inputType = "text"; break;
                 /**
                  * Boolean, tinyint
                  */
                 case "bool"        : $inputType = "text"; break;
                 case "tinyint"     : $inputType = "text"; break;
                 /**
                  * Datas e time
                  */
                 case "datetime"    : $inputType = "text"; break;
                 case "date"        : $inputType = "text"; break;
                 case "time"        : $inputType = "text"; break;
                 default            : $inputType = "text"; break;
             }

        }
        /**
         * INPUT TYPE="TEXT"
         */
        if( $inputType == "text" ){
            $conteudo.= '<div class="input_field input_text">';
            $conteudo.= '<input type="text" name="'.$inputName.'" '.$standardAtrib.' />';
        }
        /**
         * <TEXTAREA>
         *
         * Quando o tipo de dados do DB for text, blog, etc, o campo será
         * textarea.
         */
        else if( $inputType == "textarea"){
            $conteudo.= '<div class="input_field input_textarea">';
            $conteudo.= '<textarea name="'.$inputName.'" '.$standardAtrib.' />';
            $conteudo.= $standardAtribValue;
            $conteudo.= '</textarea>';
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