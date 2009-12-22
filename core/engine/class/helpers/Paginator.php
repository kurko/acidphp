<?php
/**
 * HELPER PAGINATOR
 *
 * Responsável pela paginação de lista de conteúdos provenientes do banco de
 * dados.
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

    /**
     *  SHOW()
     *
     * Mostra o menu de paginação automaticamente. A amostragem acontece
     * a partir de uma formatação padrão do sistema, formatação do usuário
     * (se especificada) ou simplesmente retorna uma array para o usuário
     * tratar os dados de paginação e mostrar manualmente.
     *
     *
     * @param string $pagClass Model da paginação
     * @param array $options Opções de configuração da paginação
     * @return mixed
     */
    public function show( $pagClass, array $options = array() ){

        /**
         * O código é dividido no seguinte:
         *
         *      Primeiro o código vai analisar que páginas devem ser mostradas,
         *      qual a última e primeira, e outros cálculos.
         *
         *      Amostragem do menu de paginação
         */

        /**
         * Inicializa variáveis do ambiente
         */
            $startLimit = $this->params["paginator"][$pagClass]["startLimit"];
            $totalRows = $this->params["paginator"][$pagClass]["totalRows"];
            $page = $this->params["paginator"][$pagClass]["page"];
            $urlGivenPage = $this->params["paginator"][$pagClass]["urlGivenPage"];
            $limit = $this->params["paginator"][$pagClass]["limit"];

            /**
             * Ajusta "limit" se não especificado
             */
            if( $limit > $totalRows )
                $limit = $totalRows;

            if( !isset($options["show"]) ){
                $options["show"] = true;
            }
            if( !empty($options["format"]) ){
                $format = $options["format"];
            }

            /**
             * Faz com que a lista de páginas seja mostrada dinamicamente
             * (a página atual fica no centro da lista de página disponíveis)
             */
                if( empty($options["pages"]) )
                    $maxLoop = 10;
                else
                    $maxLoop = $options["pages"]-1;

        /**
         * Configurações iniciais
         */
        $pag["first"] = 1;
        $pag["page"] = $page;
        $pag["last"] = number_format($totalRows/$limit, 0, "","");


        /**
         * Primeira página da lista de páginas
         */
            $tmp = number_format( $page-($maxLoop/2), 0, "","");


        /**
         * Ajusta numeração das páginas para mostrar sempre o especificado
         * em $options["pages"]
         */
            /**
             * Ajusta numeração intermediária com relação ao fim
             */
            if( $page+($maxLoop/2) >= $pag["last"] ){
                $offset = number_format( ($page+$maxLoop/2), 0, "","") - $pag["last"];
                $tmp = $tmp - $offset;
            }
            /**
             * Ajusta numeração intermediária com relação ao início
             */
            if( $tmp <= 0){
                $tmp = 1;
            }

        /**
         * Loop
         *
         * Serão descobertas todas as páginas disponíveis para amostragem,
         * respeitando o limit $maxLoop.
         *
         * Prepara-se para o Loop
         */
        $loop = true;
        $i = 0;
        while( $loop ){

            /**
             * Define a página atual.
             */
            $actualPage = ($tmp) * $limit;

            /**
             * Se passou do limite, quebra o loop
             */
            if( $actualPage > $totalRows ){
                $loop = false;
            } else {
                $pag["pages"][ $tmp ] = $tmp;
                $tmp++;
            }
            $last = $tmp;
            
            $i++;
            if( $i > $maxLoop ){
                $loop = false;
            }
        }


        if( $pag['last'] == 1
            AND (
                empty($options["always"])
                OR $options["always"] == false
                )
            )
        {
            return false;
        }

        /**
         * AMOSTRAGEM DE MENU DE PAGINAÇÃO
         *
         * Mostra páginas automaticamente
         */
        if( $options["show"] ){

            /**
             * AMOSTRAGEM PERSONALIZADA
             *
             * Se o usuário enviou uma formatação personalizada via
             * $options["format"]
             */
            if( !empty($format) ){
            
                /**
                 * Formata páginas
                 */
                $first = false;
                $conteudo = "";
                $conteudo.= '<span class="paginator">';
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

                $conteudo.= str_replace($toFormat, $newFormat, $format);

                $conteudo.= '</span>';

            }
            /**
             * AMOSTRAGEM PADRÃO
             *
             * Amostragem sem formatação personalizada.
             */
            else {
                $conteudo = "";
                $conteudo.= '<span class="paginator">';

                /**
                 * Mostra o número da primeira página, com link ou não
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

                /**
                 * Segurança.
                 */
                $first = false;

                /**
                 * Mostra todas as páginas intermediárias de navegação da
                 * pagincação.
                 */
                foreach( $pag["pages"] as $pageN ){

                    if( !$first ){
                        if( $pag["first"] + 1 != $pageN AND $pag["first"] != $pageN ){
                            $conteudo.= " ...";
                        }
                        $first = true;
                    }

                    /**
                     * Criar links
                     */
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

                    /**
                     * Guarda a última página intermediaria. Isto serve para
                     * decisão de mostrar reticências ou não.
                     */
                    $last = $pageN;
                }

                /**
                 * Mostra a última página.
                 */
                if( $pag["last"] != end($pag["pages"]) ){
                    if( $last + 1 != $pag["last"] AND ($last + 1 < $pag["last"]) ){
                        $conteudo.= "...";
                    }
                    
                    if( $page == $pag["last"] ){
                        $conteudo.= ' <span class="paginator_actualpage">';
                        $conteudo.= "".$pag["last"]."";
                        $conteudo.= "</span>";
                    } else {
                        if( $last < $pag["last"] ){
                            $conteudo.= ' <span class="paginator_page">';
                            $conteudo.= '<a href="'.substituteUrlTerm("/page:".$urlGivenPage, "/page:".$pag["last"], $this->params["url"]).'">';
                            $conteudo.= $pag["last"];
                            $conteudo.= '</a>';
                            $conteudo.= "</span>";
                        }
                    }
                }

                $conteudo.= '</span>';

            } // fim amostragem padrão
            
            return $conteudo;
        }
        /**
         * Se não deve ser mostrado nada, retorna array para o usuário decidir
         * o que fazer.
         */
        else {
            return $pag;
        }
    }


}

?>