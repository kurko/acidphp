<?php
/**
 * HELPER PAGINATOR
 *
 * Responsável pela paginação de lista de conteúdos provenientes do banco de
 * dados
 *
 * @package Helpers
 * @name Paginator
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1, 01/09/2009
 */
class PaginatorHelper extends Helper
{



    function __construct($params = ""){
        parent::__construct($params);

        /**
         * Salva configurações desta paginação
         */

    }

    public function show($pagClass = "", $options = array() ){

        /**
         * Inicializa variáveis do ambiente
         */
            $startLimit = $this->params["paginator"][$pagClass]["startLimit"];
            $totalRows = $this->params["paginator"][$pagClass]["totalRows"];
            $page = $this->params["paginator"][$pagClass]["page"];
            $urlGivenPage = $this->params["paginator"][$pagClass]["urlGivenPage"];
            $limit = $this->params["paginator"][$pagClass]["limit"];

            if( !isset($options["show"]) ){
                $options["show"] = true;
            }
            if( !empty($options["format"]) ){
                $format = $options["format"];
            }
            
        /**
         * Configurações iniciais
         */
        $pag["first"] = 1;
        $pag["page"] = $page;

        /**
         * Faz com que a lista de páginas seja mostrada dinamicamente
         * (a página atual fica no centro da lista de página disponíveis)
         */
            if( empty($options["pages"]) )
                $maxLoop = 20;
            else
                $maxLoop = $options["pages"]-1;

        /**
         * Primeira página da lista de páginas
         */
            $tmp = number_format( $page-($maxLoop/2), 0, "","");

            if( $tmp <= 0)
                $tmp = 1;

        /**
         * Loop
         *
         * Serão descobertas todas as páginas disponíveis
         *
         * Prepara-se para o Loop
         */
        $loop = true;
        $i = 0;
        while( $loop ){

            $actualPage = ($tmp) * $limit;

            $pag["pages"][ $tmp ] = $tmp;

            if( $actualPage > $totalRows ){
                $loop = false;
            } else {
                $tmp++;
            }
            $last = $tmp;
            
            $i++;
            if( $i > $maxLoop ){
                $loop = false;
            }

        }

        $pag["last"] = number_format($totalRows/$limit, 0, "","");

        /**
         * MOSTRA PÁGINAS AUTOMATICAMENTE
         */
        if( $options["show"] ){

            /**
             * AMOSTRAGEM PERSONALIZADA
             */
            if( !empty($format) ){
            
                /**
                 * Formata páginas
                 */
                $first = false;
                $conteudo = "";
                foreach( $pag["pages"] as $pageN ){

                    if( !$first ){
                        if( $pag["first"] + 1 != $pageN AND $pag["first"] != $pageN ){
                            $conteudo.= " ...";
                        }
                        $first = true;
                    }

                    if( $page == $pageN ){
                        $conteudo.= '<span class="paginator_actualpage">';
                        $conteudo.= " ".$pageN."";
                        $conteudo.= "</span>";
                    } else {
                        $conteudo.= ' <span class="paginator_page">';
                        $conteudo.= '<a href="'.substituteUrlTerm("/page:".$urlGivenPage, "/page:".$pageN, $this->params["url"]).'">';
                        $conteudo.= " ".$pageN."";
                        $conteudo.= '</a>';
                        $conteudo.= "</span>";
                    }

                    $last = $pageN;
                }

                $toFormat = array("&page&","&total&" ,"&last&"    ,"&first&"    ,"&pages&");
                $newFormat = array($page  ,$totalRows,$pag["last"],$pag["first"],$conteudo);

                $conteudo = str_replace($toFormat, $newFormat, $format);

            }
            /**
             * AMOSTRAGEM PADRÃO
             */
            else {
                $conteudo = "";

                /**
                 * Mostra a primeira página.
                 */
                if( $pag["first"] != reset($pag["pages"]) ){
                    if( $page == $pag["first"] ){
                        $conteudo.= '<span class="paginator_actualpage">';
                        $conteudo.= " ".$pag["first"]."";
                        $conteudo.= "</span>";
                    } else {
                        $conteudo.= ' <span class="paginator_page">';
                        $conteudo.= '<a href="'.substituteUrlTerm("/page:".$urlGivenPage, "/page:".$pag["first"], $this->params["url"]).'">';
                        $conteudo.= $pag["first"];
                        $conteudo.= '</a>';
                        $conteudo.= "</span>";
                    }
                }

                $first = false;

                /**
                 * Mostra todas as páginas.
                 */
                foreach( $pag["pages"] as $pageN ){

                    if( !$first ){
                        if( $pag["first"] + 1 != $pageN AND $pag["first"] != $pageN ){
                            $conteudo.= " ...";
                        }
                        $first = true;
                    }

                    if( $page == $pageN ){
                        $conteudo.= '<span class="paginator_actualpage">';
                        $conteudo.= " ".$pageN."";
                        $conteudo.= "</span>";
                    } else {
                        $conteudo.= ' <span class="paginator_page">';
                        $conteudo.= '<a href="'.substituteUrlTerm("/page:".$urlGivenPage, "/page:".$pageN, $this->params["url"]).'">';
                        $conteudo.= " ".$pageN."";
                        $conteudo.= '</a>';
                        $conteudo.= "</span>";
                    }

                    $last = $pageN;
                }

                /**
                 * Mostra a última página.
                 */
                if( $pag["last"] != end($pag["pages"]) ){
                    if( $last + 1 != $pag["last"] ){
                        $conteudo.= "...";
                    }
                    if( $page == $pag["last"] ){
                        $conteudo.= ' <span class="paginator_actualpage">';
                        $conteudo.= "".$pag["last"]."";
                        $conteudo.= "</span>";
                    } else {
                        $conteudo.= ' <span class="paginator_page">';
                        $conteudo.= '<a href="'.substituteUrlTerm("/page:".$urlGivenPage, "/page:".$pag["last"], $this->params["url"]).'">';
                        $conteudo.= $pag["last"];
                        $conteudo.= '</a>';
                        $conteudo.= "</span>";
                    }
                }

            } // fim amostragem padrão
            
            return $conteudo;

        } else {
            return $pag;
        }
    }


}

?>