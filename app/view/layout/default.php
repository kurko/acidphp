<?php
/**
 * LAYOUT DEFAULT
 *
 * Este arquivo é o layout default do sistema.
 *
 * @package View
 * @name Default Layout
 * @author Alexandre de Oliveira <chavedomundo@gmail.com>
 * @since v0.1 16/07/2009
 */
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=<?php echo Config::read("charset") ?>">
    <title><?php echo $siteTitle." - ".$pageTitle; ?></title>
    <?php
    echo $html->css("standard.style");

    /*
     * Uncomment the line below to use AjaxHelper
     */
    //echo $html->javascript("jquery");

    echo $html->metatags();
    ?>
</head>
<body>
    <div id="global">
        <div id="cabecalho">
            <h1>Bem-vindo ao AcidPHP!</h1>
        </div>
        <div id="view">
            <div class="content">
            <?php echo $content_for_layout; ?>
            </div>
        </div>
        <div id="rodape">
            
        </div>
    </div>
</body>
</html>
