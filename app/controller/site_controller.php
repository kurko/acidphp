<?php
/**
 * Controller padrão da aplicação
 *
 * @package Controller
 * @name Main
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 18/07/2009
 */

class SiteController extends AppController
{

    var $uses = array("Usuario", "Tarefa", "Idade");
    var $siteTitle = "AcidPHP";

    function index(){
        $this->pageTitle = "Página principal";

        //$this->Usuario->update( array("email"=>"123@123.com"), "1" );

        //pr( $this->params );
        pr( $this->data );

    }

    function save(){
        //pr($this->Idade->hasMany);
        $this->data = array(
            "Usuario" => array(
                "id" => 2,
                "nome" => "sei lá"
            ),
            "Idade" => array( // hasOne
                "id" => "24",
                "titulo" => "teste",
            ),
            "Tarefa" => array( // hasMany
                0 => array(
                    "id" => "24",
                    "nome" => "tarefa1",
                ),
                1 => array(
                    "nome" => "tarefa2",
                ),
            ),

        );

        //pr( $this->data );

        //pr( $this->Usuario->save( $this->data ) );
        
        //echo '<br>------->Salva a seguir:----<br>';
        $this->Usuario->save( $this->data );
        //$this->Usuario->update( array("Usuario.nome"=>"HELLOOO", "Idade.titulo"=>"táitou"), "2" );
        //pr( $this->Usuario->countRows() );
    }

    function listar(){
        
        $usuarios = $this->Usuario->paginate(array(
            "fields" => array("Usuario.nome"),
            "limit" => 2,
            //"page" => $this->params["args"]["page"]
        ));

        //pr($usuarios);
        $this->set("usuarios", $usuarios);
    }

    function deletar($id){

        if( $this->Usuario->deleteAll( $id ) )
            echo 'deu';
        else
            echo 'não deu';

        $this->autoRender = false;
        
    }

    /**
     * Para edição de usuário num formulário, basta criar um campo no formHelper
     * chamado id. Simples assim. Não precisa dar find() nem nada. O Helper
     * faz tudo sozinho.
     */
    function editar($id){
        $this->set("id", $id);
    }


    function edit(){
        $this->autoRender = false;

        /**
         * Se o id já existe, atualiza automaticamente.
         */
        if($this->data){
            pr($this->data);
            $this->Usuario->save($this->data);
        }

    }


}
?>