<?php
/**
 * Controller principal deste m�dulo
 *
 * @package ModController
 * @name nome
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1.6 06/07/2009
 */

class SetupController extends ModsSetup
{

    function beforeFilter(){
        $_SESSION['exPOST'] = $_POST;
        $this->set('exPOST', $_SESSION['exPOST']);
    }

    /**
     * setuppronto()
     *
     * Cria cadastro
     *
     * Campos especificados, agora come�a a criar tabelas e configura��es.
     *
     * @global array $aust_charset Cont�m o charset global do sistema
     */
    function setuppronto(){

        //pr($_POST);

        global $aust_charset;

        /**
         * Par�metros para gravar uma nova estrutura no DB.
         */
        $params = array(
            'nome' => $_POST['nome'],
            'categoriaChefe' => $_POST['categoria_chefe'],
            'estrutura' => 'estrutura',
            'moduloPasta' => $_POST['modulo'],
            'autor' => $this->administrador->LeRegistro('id')
        );
        /**
         * CRIA ESTRUTURA (Aust)
         *
         * Verifica se consegue gravar a estrutura (provavelmente na tabela
         * 'categorias').
         */
        if( $status_insert = $this->aust->gravaEstrutura( $params ) ){
            
            $status_setup[] = "Categoria criada com sucesso.";

            /**
             * Cria string com o charset geral do projeto
             */
            $cur_charset = 'CHARACTER SET '.$aust_charset['db'].' COLLATE '.$aust_charset['db_collate'];

            /**
             * Trata o nome da tabela para poder criar no db
             */
            $tabela = RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome'])));

            /**
             * TRATAMENTO DE CAMPOS
             *
             * Gera o SQL dos campos para salvar em 'cadastros_conf'
             */
            /*
             * Loop por cada campo para gera��o de SQL para salvar suas
             * configura��es em 'cadastros_conf'.
             */
            $ordem = 0; // A ordem do campo
            for($i = 0; $i < count($_POST['campo']); $i++) {
                $ordem++;

                $valor = ''; // Por seguran�a
                $_POST['campo_descricao'][$i] = addslashes( $_POST['campo_descricao'][$i] );

                /**
                 * Verifica se o atual campo analisado est� especificado.
                 *
                 * Se...
                 *      - sim: faz os devidos tratamentos;
                 *      - n�o: n�o faz nada.
                 */
                if( !empty($_POST['campo'][$i]) ){

                    /**
                     * !!!ATEN��O!!!
                     *
                     * Altere condi��es abaixo para modifica��es do $_POST['campo_tipo']
                     */

                    /**
                     * TIPAGEM F�SICA DOS CAMPOS
                     *
                     * Define os tipos f�sicos dos dados.
                     *
                     * A tabela criada para o cadastro ter� campos especificados
                     * na instala��o do mesmo, e estes campos devem receber um
                     * formato adequado. Se � campo texto, ser� varchar, e assim
                     * por diante.
                     */
                    /**
                     * Tipo Password
                     * Se o tipo de campo for pw, $campo_tipo=varchar(180)
                     */

                    $tipagemFisicaDosCampos = array(
                        "pw" => "varchar(180)",
                        "arquivo" => "varchar(240)",
                        "relacional_umparaum" => array(
                            "tipo" => "int",
                        )
                    );

                    /**
                     * Se o tipo f�sico foi configurado anteriormente, salva de
                     * acordo, sen�o o tipo � aquele especificado no formul�rio
                     * de configura��o.
                     */
                    if ( array_key_exists( $_POST['campo_tipo'][$i], $tipagemFisicaDosCampos ) ){
                        if( is_array( $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ] ) ){
                            $campo_tipo = $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ]["tipo"];
                        } else {
                            $campo_tipo = $tipagemFisicaDosCampos[ $_POST['campo_tipo'][$i] ];
                        }
                    } else {
                        $campo_tipo = $_POST['campo_tipo'][$i];
                    }

                    /*
                    if($_POST['campo_tipo'][$i] == 'pw'){
                        $campo_tipo = 'varchar(180)';
                    }
                    /**
                     * Se o tipo de campo for arquivo, $campo_tipo=varchar(240)
                     *
                    elseif($_POST['campo_tipo'][$i] == 'arquivo'){
                        $campo_tipo = 'varchar(240)';
                    } elseif($_POST['campo_tipo'][$i] == 'relacional_umparaum'){
                        $campo_tipo = 'int';
                    } else {
                        $campo_tipo = $_POST['campo_tipo'][$i];
                    }
                     * 
                     */

                    /**
                     * Retira acentua��o e caracteres indesejados para criar
                     * campos nas tabelas
                     */
                    $valor = RetiraAcentos(strtolower(str_replace(' ', '_', $_POST['campo'][$i]))).' '. $campo_tipo;

                    /**
                     * Se for data ou relacional, n�o tem charset
                     */
                    if($campo_tipo <> 'date' AND $campo_tipo <> 'int')
                        $valor .= ' '. $cur_charset.' NOT NULL';

                    /**
                     * Descri��o: ajusta coment�rio do campo
                     */
                    if(!empty($_POST['campo_descricao'][$i]))
                        $valor .=  ' COMMENT \''. $_POST['campo_descricao'][$i] .'\'';

                    /**
                     * Ajusta v�rgulas (se for o primeiro campo, n�o tem v�rgula)
                     */
                    if($i == 0){
                        $campos = $valor;
                    } else {
                        $campos .= ', '.$valor;
                    }
                    
                    $campo = RetiraAcentos(strtolower(str_replace(' ', '_', $_POST['campo'][$i])));

                    /**
                     * CONFIGURA��O DE CAMPOS
                     *
                     * Analisa campo por campo e grava informa��es diferenciadas
                     * sobre campos especiais (exemplo: password, arquivos)
                     */
                    /**
                     * Password. tipo=campopw
                     */
                    if($_POST['campo_tipo'][$i] == 'pw'){
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'password',".$ordem.")";
                    }
                    /**
                     * Arquivos
                     */
                    elseif($_POST['campo_tipo'][$i] == 'arquivo'){
                        $cria_tabela_arquivos = TRUE;
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'arquivo',".$ordem.")";
                    }
                    /**
                     * Campo relacional um-para-um
                     */
                    elseif($_POST['campo_tipo'][$i] == 'relacional_umparaum'){
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem,ref_tabela,ref_campo)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1, 'relacional_umparaum',".$ordem.", '".$_POST['relacionado_tabela_'.($i+1)]."', '".$_POST['relacionado_campo_'.($i+1)]."')";
                    }
                    /**
                     * Campo normal, grava suas informa��es
                     */
                    else {
                        $sql_campos[] =
                                    "INSERT INTO cadastros_conf
                                        (tipo,chave,valor,comentario,categorias_id,autor,desativado,desabilitado,publico,restrito,aprovado,especie,ordem)
                                    VALUES
                                        ('campo','".$campo."','".$_POST['campo'][$i]."','".$_POST['campo_descricao'][$i]."',".$status_insert.", ".$this->administrador->LeRegistro('id').",0,0,1,0,1,'string',".$ordem.")";
                    }
                }
            }
            //pr($sql_campos);
            /**
             * SQL
             *
             * Cria tabela
             */
            $sql = 'CREATE TABLE '.$tabela.'(
                        id int auto_increment,
                        '.$campos.',
                        bloqueado varchar(120) '.$cur_charset.',
                        aprovado int,
                        adddate datetime,
                        PRIMARY KEY (id), UNIQUE id (id)

                    ) '.$cur_charset;
            //echo $sql;

            /**
             * Se o tipo de campo � arquivo, cria outra tabela para os arquivos
             */
            if( !empty( $cria_tabela_arquivos )
                AND $cria_tabela_arquivos == TRUE ){
                $sql_arquivos =
                    "CREATE TABLE ".$tabela."_arquivos(
                    id int auto_increment,
                    titulo varchar(120) {$cur_charset},
                    descricao text {$cur_charset},
                    local varchar(80) {$cur_charset},
                    url text {$cur_charset},
                    arquivo_nome varchar(250) {$cur_charset},
                    arquivo_tipo varchar(250) {$cur_charset},
                    arquivo_tamanho varchar(250) {$cur_charset},
                    arquivo_extensao varchar(10) {$cur_charset},
                    tipo varchar(80) {$cur_charset},
                    referencia varchar(120) {$cur_charset},
                    categorias_id int,
                    adddate datetime,
                    autor int,
                    PRIMARY KEY (id),
                    UNIQUE id (id)
                ) ".$cur_charset;
            }
            //echo '<br><br><br>'.$sql_arquivos;

            /**
             * TABELA F�SICA
             */
            /*
             * Executa QUERY na base de dados
             *
             * Se retornar sucesso, salva configura��es gerais sobre o cadastro na tabela cadastros_conf
             */
            //pr( addslashes( $sql) );
            if( $this->conexao->exec( $sql, 'CREATE_TABLE') ){
                $status_setup[] = "Tabela '".$tabela."' criada com sucesso!";

                /**
                 * Se h� SQL para cria��o de tabela para arquivos
                 */
                if( !empty($sql_arquivos) AND $cria_tabela_arquivos == TRUE ){
                    if($this->conexao->exec($sql_arquivos, 'CREATE_TABLE')){
                        $status_setup[] = 'Cria��o da tabela \''.$tabela.'_arquivos\' efetuada com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar tabela \''.$tabela.'_arquivos\'.';
                    }

                    $sql_conf_arquivos =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('estrutura','tabela_arquivos','".$tabela."_arquivos',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_arquivos)){
                        $status_setup[] = 'Configura��o da estrutura \''.$tabela.'_arquivos\' salva com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar tabela \''.$tabela.'_arquivos\'.';
                    }


                }

                /*
                 * CONFIGURA��O
                 *
                 * Aqui, guardamos as principais configura��es de cadastro
                 */
                // salva configura��o sobre aprova��o quanto ao cadastro
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','aprovacao','".$_SESSION['exPOST']['aprovacao']."','Aprova��o','bool',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configura��o de aprova��o salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configura��o de aprova��o n�o foi salva com sucesso.';
                    }

                // DESCRI��O: salva o par�grafo introdut�rio ao formul�rio
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','descricao','".$_SESSION['exPOST']['descricao']."','Descri��o','blob',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configura��o de aprova��o salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configura��o de aprova��o n�o foi salva com sucesso.';
                    }

                // salva configura��o sobre pr�-senha para o cadastro
                    $sql_conf_2 =
                                "INSERT INTO
                                    cadastros_conf
                                    (tipo,chave,valor,nome,especie,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                                VALUES
                                    ('config','pre_senha','".$_SESSION['exPOST']['pre_senha']."','Pr�-senha','string',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                                ";
                    if($this->conexao->exec($sql_conf_2)){
                        $status_setup[] = 'Configura��o de pr�-senha salva com sucesso!';
                    } else {
                        $status_setup[] = 'Configura��o de pr�-senha n�o foi salva com sucesso.';
                    }




                // configura��es sobre a estrutura, como tabela a ser usada
                $sql_conf =
                            "INSERT INTO
                                cadastros_conf
                                (tipo,chave,valor,categorias_id,adddate,autor,desativado,desabilitado,publico,restrito,aprovado)
                            VALUES
                                ('estrutura','tabela','".RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome'])))."',".$status_insert.", '".date('Y-m-d H:i:s')."', ".$this->administrador->LeRegistro('id').",0,0,1,0,1)
                            ";
                if($this->conexao->exec($sql_conf)){
                    $status_setup[] = 'Configura��o da estrutura \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\' salva com sucesso!';

                    // n�mero de erros encontrados
                    $status_campos = 0;
                    foreach ($sql_campos as $valor) {
                        if(!$this->conexao->exec($valor)){
                            $status_campos++;
                        }
                    }
                    if($status_campos == 0){
                        $status_setup[] = 'Campos criados com sucesso!';
                    } else {
                        $status_setup[] = 'Erro ao criar campos';
                    }
                } else {
                    $status_setup[] = 'Erro ao salvar configura��o da estrutura \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\'.';
                }
            } else {
                $status_setup[] = 'Erro ao criar tabela \''.RetiraAcentos(strtolower(str_replace(' ', '_', $_SESSION['exPOST']['nome']))).'\'.';
            }

        }

        echo '<ul>';
        foreach ($status_setup as $valor){
            echo '<li>'.$valor.'</li>';
        }
        echo '</ul>';


        $this->autoRender = false;
    }

}
?>