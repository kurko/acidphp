<?php
/**
 * Classe dbSchema.
 *
 * Cont�m propriedades das tabelas da base de dados
 *
 * @package dbSchema
 * @name dbSchema
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @version 0.1
 * @since v0.1.5, 15/05/2009
 */
/**
 * COMO INSTALAR UM NOVO DBSCHEMA
 *      1) Instancie um objeto usando a classe dbSchema, enviando como
 *         par�metros:
 *         a) vari�vel contendo o DBSCHEMA;
 *         b) a classe de conex�o;
 *         ex. $dbSchema = new dbSchema($dbSchema, $conexao);
 * 
 *         O novo schema fica guardado em $this->dbSchema;
 *
 *      2) Agora $dbSchema j� est� pronta para ser usada. No momento de instalar
 *         um novo schema, chame $dbSchema->instalarSchema();
 */
class dbSchema
{
    /**
     *
     * @var array Possui o schema das tabelas do DB
     */
    protected $dbSchema;
    /**
     *
     * @var class Classe respons�vel pela conex�o com o banco de dados
     */
    protected $conexao;
    /**
     *
     * @var array Tabelas existentes no DB
     */
    public $tabelasAtuais;
    /**
     *
     * @var array Campos inexistentes na base de dados atual
     */
    public $camposInexistentes;
    /**
     *
     * @var array Cont�m informa��es sobre quais tabelas est�o instaladas.
     *
     * Cada tabela (sendo �ndice desta array) tem os seguintes valores
     *     -1: tabela existe, mas algum(ns) campo(s) n�o existe(�m)
     *      0: tabela n�o existe
     *      1: tabela existe
     *
     */
    public $status;
    /**
     *
     * @var array ?
     */
    public $schemaStatus;
    /**
     *
     * @var array Cont�m �ndices com nomes das tabelas instaladas recentemente, com valores:
     *      0: n�o instalado por falha.
     *      1: instalado normalmente.
     */
    public $tabelasInstaladas;
    /**
     *
     * @var array Possui todos os nomes especiais de $dbSchema
     */
    protected $camposEspeciais = array(
        'dbSchemaTableProperties',
        'dbSchemaSQLQuery',
    );


    /**
     * Ajusta $this->tabelasExistente com as tabelas e campos atuais
     *
     * @param array $dbSchema O Schema das tabelas necess�rio ao funcionamento do sistema
     * @param class $conexaoClass Classe de conex�o com o banco de dados
     */
    function  __construct($dbSchema, $conexaoClass) {
        if ( is_array($dbSchema) ){
            $this->dbSchema = $dbSchema;
        }
        
        $this->conexao = $conexaoClass;

        //$this->tabelasAtuais();
        /**
         * Se n�o houve verifica��o do Schema atual ainda
         */
        if(empty($this->status)){
            //$this->verificaSchema();
        }
    }

    /**
     * VERIFICA��ES
     */
    /**
     * Fun��o verifica se o Schema est� instalado
     *
     * @return string
     *     -2: Algumas tabelas n�o existem
     *     -1: Todas as tabelas exist�m, mas alguns campos n�o
     *      0: Nenhuma tabela existe
     *      1: Tudo ok
     */
    public function verificaSchema(){
        /**
         * Reseta propriedades do objeto
         */
        $this->camposInexistentes = array();
        $this->tabelasInstaladas = array();
        $this->tabelasAtuais();

        /**
         * Nomes de campos especiais
         */
        $status = array();

        foreach($this->dbSchema as $tabela=>$valor){
            /**
             * Se tabela n�o existe
             */
            if(!$this->verificaTabela($tabela)){
                $status[$tabela] = 0;
            } else {
                /**
                 * Se tabela existe, verifica campos
                 */
                $status[$tabela] = 1;
                if(is_array($valor)){
                    /**
                     * Loop por cada campo do Schema
                     */
                    foreach($valor as $campo=>$propriedades){
                        /**
                         * Se n�o for um dado especial (informa��es sobre associa��o, etc),
                         * � um campo normal
                         */
                        /**
                         * Se campo n�o existe
                         */
                        if(!$this->verificaCampo($campo, $tabela)){
                            $status[$tabela] = -1;
                            $this->camposInexistentes[$tabela][$campo] = $propriedades;
                        }
                    }
                }
            }
        }

        /**
         * Guarda status das tabelas
         */
        $this->status = $status;

        /**
         * Retorna resultado
         *     -2: algumas tabelas exist�m e outras n�os
         *     -1: as tabelas exist�m, mas alguns campos n�o
         *      0: nenhuma tabela existe
         *      1: todas as tabelas exist�m
         */

        if(!empty($status)){
            /**
             * Algumas tabelas exist�m e outras n�o
             */
            if(in_array(0, $status) AND in_array(1, $status)){
                $this->schemaStatus = -2;
            }
            /**
             * As tabelas exist�m, mas alguns campos n�o
             */
            elseif(in_array(1, $status) AND in_array(-1, $status)) {
                $this->schemaStatus = -1;
            }
            /**
             * Todas as tabelas precisam ser instaladas
             */
            elseif(in_array(0, $status) AND !in_array(1, $status)) {
                $this->schemaStatus = 0;
            } else {
                $this->schemaStatus = 1;
            }
        } else {
            /**
             * H� algum erro com o schema
             */
            $this->schemaStatus = false;
        }

        return $this->schemaStatus;
        
    }

    protected function tabelasAtuais(){
        /**
         * Carrega todas as tabelas do DB
         */
        $mysql = $this->conexao->query('SHOW TABLES');

        $this->tabelasAtuais = array();
        /**
         * La�o por todas as tabelas do DB
         */
        //while( $dados = mysql_fetch_array($mysql) ){
        foreach($mysql as $chave=>$dados){

            /**
             * Carrega todos os campos das tabelas e ent�o grava em $this->tabelasAtuais
             */
            $describeSql = 'DESCRIBE '.$dados[0];
            


            foreach($this->conexao->query($describeSql) as $tabela=>$info){
                $this->tabelasAtuais[$dados[0]][$info['Field']] = $info;
            }
        }

    }

    /**
     * Verifica se determinada tabela existe no DB atual
     *
     * @param string|array $tabela Tabela a ser verifica ante
     * @return bool Retorna se determinada tabela existe
     */
    protected function verificaTabela($tabela){
        /**
         * Verifica se $tabela existe DB atual
         */
        if(is_string($tabela)){
            if(!array_key_exists($tabela, $this->tabelasAtuais)){
                return false;
            }
        }
        return true;

    }

    /**
     * Verifica se determinado campo existe no DB atual
     *
     * @param string|array $campo
     * @return bool
     */
    protected function verificaCampo($campo, $tabela){

        //pr($this->tabelasAtuais[$tabela]);
        if(is_string($campo)){
            if(!in_array($campo, $this->camposEspeciais)){
                if(!array_key_exists($campo, $this->tabelasAtuais[$tabela])){
                    //echo $campo;
                    return false;
                }
            }
        }

        return true;
    }

    /**
     *
     * INSTALA��O
     *
     * M�todos de instala��o
     */
    /**
     * 
     * Instala um Schema de DB a partir de $this->dbSchema.
     * 
     * Para instala��o de tabelas de m�dulos, o m�dulo deve instanciar um objeto
     * -new dbSchema- e enviar seu schema de tabelas para o objeto,
     * posteriormente chamando a fun��o a seguir.
     *
     * @return bool
     *      - Retorna 1 se tudo ocorreu normalmente;
     *      - Se n�o est� tudo ok, retorna array com tabelas n�o instaladas.
     */
    public function instalarSchema(){
        /**
         * Verifica se as tabelas j� existem. Somente se estiver tudo ok a
         * instala��o continua
         */
        //pr($this->schemaStatus);

        $checkedSchema = $this->verificaSchema();
        //var_dump($checkedSchema);
        //echo '<strong>--->'.$this->verificaSchema().'<----</strong>';
        if($checkedSchema != 1 AND $this->isDbSchemaFormatOk() ){
            //echo 'diferente de 1 e n�o falso';
            /**
             * Tabela por tabela do Schema
             */
            foreach($this->dbSchema as $tabela=>$campos){

                if(!array_key_exists($tabela, $this->tabelasAtuais) AND is_array($campos)){
                    foreach($campos as $nome=>$propriedades){
                        /**
                         * Se n�o for campo especial, gera SQL deste campo
                         */
                        if(!in_array( $nome, $this->camposEspeciais)){
                            $camposSchema[] = $nome.' '.$propriedades;
                        }
                        /**
                         * Campo especial (Keys)
                         */
                        else {
                            if(is_array($propriedades)){
                                /**
                                 * Percorre os campos especiais (Keys)
                                 */
                                foreach($propriedades as $key=>$propriedades2){
                                    /**
                                     * Se for propriedades como Keys prim�rias, �nicas, etc
                                     */
                                    if($nome == 'dbSchemaTableProperties'){
                                        $camposSchema[] = $key.' '.$propriedades2;
                                    }
                                    /**
                                     * Se for SQLs que devem ser rodados na cria��o da tabela
                                     */
                                    elseif($nome == 'dbSchemaSQLQuery'){
                                        $sqlsubquery[$tabela][] = $propriedades2;
                                    }
                                }
                            }
                            
                        }
                    }
                    /**
                     * Gera SQL
                     */
                    $sql[$tabela] = 'CREATE TABLE '.$tabela.' ('. implode(', ', $camposSchema) .')';
                    unset($camposSchema);
                }
            } // Fim do foreach

            /**
             * Executa Query por Query, criando cada tabela inexistente
             */
            if(is_array($sql)){
                foreach($sql as $tabela=>$valor){

                    $mysql = $this->conexao->exec($valor, 'CREATE_TABLE');
                    if($mysql){
                        $resultado[$tabela] = 1;
                        /**
                         * Guarda resultado como tabela instalada
                         */
                        $this->tabelasInstaladas[$tabela] = 1;
                        
                        /**
                         * Se h� querys subsequentes a serem rodadas
                         */
                        if(!empty($sqlsubquery[$tabela]) AND is_array($sqlsubquery[$tabela])){
                            foreach($sqlsubquery[$tabela] as $subsql){
                                if($this->conexao->exec($subsql)){
                                    
                                } else {

                                }
                            }
                        }

                    } else {
                        /**
                         * Guarda resultado como tabela n�o instalada
                         */
                        $this->tabelasInstaladas[$tabela] = 0;

                        $resultado[$tabela] = 0;
                    }
                }
            }

            /**
             * Se alguma tabela n�o foi instalada, retorna a array contendo o resultado
             */
            if(in_array(0, $resultado)){
                return $resultado;
            }
            /**
             * Tudo foi ok
             */
            return 1;
        }
        /**
         * Se as tabelas j� exist�m ou o schema est� com defeito
         */
        else {
            return false;
        }
    }

    protected function isDbSchemaFormatOk($dbSchema = ''){
        if ( is_array($this->dbSchema)) {
            return true;
        }

        return false;


    }
}

?>
