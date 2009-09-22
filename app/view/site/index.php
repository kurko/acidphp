<p>
    Parabéns! Sua versão do Acid parece estar funcionando.
</p>

<?php
if( !is_file(APP_CONFIG_DATABASE) ){
    ?>
    <div class="nota alerta">
        <p>
            <strong>Alerta:</strong> Você ainda não configurou uma conexão com
            o banco de dados. Vá até o diretório
            <strong>app/config/</strong> e renomeie o arquivo modelo
            <strong>database.sample.php</strong> para <strong>database.php</strong>
            e configure o que há dentro dele.
        </p>
        <p>
            O primeiro arquivo é um modelo para que você possa usá-lo para
            configurar o acesso de sua aplicação para o banco de dados com
            facilidade.
        </p>
    </div>

    <?php
} else {
    ?>
    <div class="nota dica">
        <p>
        <strong>Banco de Dados:</strong> Está tudo Ok com sua configuração de
        acesso ao banco de dados.
        </p>
    </div>
    <?php
}
?>

<h2>Índice de leitura</h2>
<p>
    Encontre informações rápidas a seguir. Se você já conhece o Acid, comece
    a desenvolver!

    <ul style="color: #999999">
        <li>
            <span class="black">
                <a href="#primeiravez">Esta é minha primeira vez no Acid</a>
            </span>
        </li>
        <li>
            <span class="black">
                <a href="#comofunciona">Como o Acid funciona? O que é MVC?</a>
            </span>
        </li>
        <li>
            <span class="black">
                <a href="#configurando">Configurando sua aplicação</a>
            </span>
        </li>
        <li>
            <span class="black">
                <a href="#maisajuda">Como obter mais ajuda</a>
            </span>
        </li>
    </ul>

</p>
<p>
    Basicamente, o Acid promete economizar muitas horas no seu desenvolvimento.
    Ele contém todas as funcionalidades que você espera ter quando
    <em>começa</em> a desenvolver um novo site ou aplicação.
</p>

<h2><a name="primeiravez">Primeira vez no Acid? Leia a seguir...</a></h2>
<p>
    O <strong>Acid</strong> é um framework PHP que agiliza o 
    desenvolvimento de suas aplicações!
</p>
<p> 
    Focado na velocidade e automatização de processos, o Acid dispõem de um
    vasto arsenal de ferramentas ao seu dispor.
    <ul style="color: #999999">
        <li>
            <span class="black">
            <strong>SQL nunca mais:</strong>
            o Acid monta todo SQL para você, seja para
            buscar ou salvar dados no banco de dados!
            </span>
        </li>
        <li>
            <span class="black">
            <strong>Código organizado:</strong> com o padrão MVC, seu código
            fica dividido e organizado. Designers e programadores não precisam
            mais se aborrecer.
            </span>
        </li>
        <li>
            <span class="black">
            <strong>Formulário HTML automáticos: </strong>
            <em>&lt;form&gt;</em> nunca mais! O Acid analisa suas tabelas e
            mostra um formulário formatado com pouco esforço.
            </span>
        </li>
        <li>
            <span class="black">
            <strong>AuthComponent:</strong>
            O componente de autenticação possibilita você ter um sistema de
            login completo automaticamente! Diga qual a tabela contém os dados
            dos usuários e o Acid cuida do resto.
            </span>
        </li>
        <li>
            <span class="black">
            <strong>Segurança:</strong>
            Contém diversas diretivas de segurança padrão, evitando ataques e
            <em>exploits</em> comuns.
            </span>
        </li>
    </ul>
</p>
<h2><a name="comofunciona">Como ele funciona?</a></h2>
<div class="nota">
    <p>
    <strong>Nota:</strong> Você encontrará a seguir uma breve introdução do
    funcionamento do Acid. Você salvará incontáveis horas de desenvolvimento
    usando este fabuloso Framework!
    </p>
</div>

<p>
    O Acid é baseado no padrão MVC (Modelo, Visualização, Controladores). Com
    o MVC, seu código fica organizado em três camadas, melhorando a 
    visualização do que já foi desenvolvido e acabando com a bagunça de
    diretórios e arquivos com prefixos e sufixos sem fim!
</p>

<div class="nota">
    <p>
    <strong>Nota:</strong> Você
    encontrará na internet os termos do MVC sempre em inglês, portanto vamos nos
    referir a eles desta forma: <strong>M</strong>odel, <strong>V</strong>iew
    e <strong>C</strong>ontroller.
    </p>
</div>

<p>
    Entender o significado de cada um destes elementos do padrão MVC é de
    extrema importância.
    <ul>
        <li>
            <strong>Models: </strong>
            Você guarda aqui qualquer código responsável por ler e escrever
            no banco de dados e regras de negócio.
        </li>
        <li>
            <strong>View: </strong>
            guarde aqui seus arquivos HTML e tudo que é mostrado ao usuário
            final.
        </li>
        <li>
            <strong>Controller: </strong>
            O <em>Controller</em> é a parte que toma os dados vindos dos Models
            e envia-os para os Views mostrarem aos usuários finais.
        </li>
    </ul>
</p>
<h3>Como o Acid cuida disto?</h3>
<p>
    Em uma palavra, <em>automaticamente</em>.
</p>
<p>
    Abra o diretório do Acid que você baixou. Você verá dois diretórios
    principais:

    <ul>
        <li>
            <strong>app/ </strong>
            Sua aplicação vai aqui. Tudo que você desenvolver vai neste
            diretório.
        </li>
        <li>
            <strong>core/ </strong>
            Não mexa neste diretório! Este é o motor por trás do Acid. Faça
            alterações somente se você deseja colaborar com o projeto do Acid.
        </li>
    </ul>
</p>
<p>
    Dentro de <strong>app/</strong> você encontra os diretórios
    <strong>model/</strong>, <strong>view/</strong>,
    <strong>controller/</strong>.
</p>
<h3>O Controller: o cérebro da aplicação</h3>
<p>
    No diretório <strong>controller/</strong>, você tem um arquivo chamado
    <em>site_controller.php</em>. Nele você tem uma classe com o mesmo nome
    do arquivo, <em>SiteController</em>. O nome do controller aqui é Site.
</p>
<p>
    Cada Controller é composto pelo que chamamos de <strong>Actions</strong>,
    que são ações específicas. O Controller <em>Usuarios</em>, por exemplo,
    terá os
    <strong>actions</strong> <em>listar, adicionar, excluir</em> e assim por
    diante. Cada <em>action</em> possui seu próprio view. Um action, um View.
</p>
<p>
    Um <strong>Action</strong> é representado por um método dentro do
    Controller. Quer um exemplo? Abra app/controller/site_controller.php e você
    verá um método lá chamado <strong>index()</strong>.
</p>
<div class="nota">
    <p>
    <strong>Nota:</strong> Nomes de controllers devem obedecer à seguinte regra:
    arquivos recebem o nome no formato <strong>xxxx_controller.php</strong>, e a
    classe do controller <strong>XxxxController</strong>, onde Xxxx é o nome
    do controller.
    </p>
</div>
<h3>O View: mostrando para o mundo</h3>
<p>
    É no diretório <strong>view/</strong> que você guardará seu código HTML.
    No código atual (este que você acabou de baixar), temos o Controller
    <em>Site</em>, portanto dentro de <strong>view/</strong> teremos o
    subdiretório <em>site/</em>. Lá ficarão todos os Views de cada Action do
    Controller Site.
</p>
<p>
    Abra o Controller Site do código atual, e você verá que há um método na
    classe SiteController que chama-se index(). Traduzindo, temos um action
    chamado <em>index</em>, que é esta página que você está lendo. Agora vá em
    <strong>view/site/</strong> e você verá o view correspondente da action
    index, o arquivo <strong>index.php</strong>.
    Abra-o (neste arquivo você verá este texto).
</p>
<div class="nota dica">
    <p>
    <strong>Dica:</strong> Não use códigos de lógica dentro dos views, somente
    aqueles necessários para leitura de variáveis, como foreach, while. Você
    não é proibido disto, mas isto deixa seu código organizado. Use com
    bom senso.
    </p>
</div>
<p>
    Abra o Controller Site do código atual, e você verá que há um método na
    classe SiteController que chama-se index(). Traduzindo, temos um action
    chamado <em>index</em>, que é esta página que você está lendo. Agora vá em
    <strong>view/site/</strong> e você verá o view correspondente da action
    index, o arquivo <strong>index.php</strong>.
    Abra-o (neste arquivo você verá este texto).
</p>
<div class="nota dica">
    <p>
    <strong>Dica:</strong> Para mudar este layout, modifique o arquivo
    <strong>app/view/layout/default.php</strong>. Neste diretório estão os
    layouts padrão do seu site. Os Views são carregados dentro destes layouts,
    portanto você tem várias páginas, mas todas com o mesmo cabeçalho e rodapé e
    regras CSS.
    </p>
</div>

<h3>O Model: a memória da sua aplicação</h3>
<p>
    Uma das maiores mágicas do Acid está nos <strong>Models</strong>. Um Model
    representa uma tabela do banco de dados. Ele abstrai os dados, você não lida
    mais SQL, mas com um comando o Model faz tudo para você.
</p>
<p>
    Os models têm funções prontas e nativas do Acid, como find(), save(),
    update(), delete(). O próprio Model gerá todo o SQL necessário para a ação.
</p>
<p>
    É no diretório <strong>model/</strong> que você encontra os tais
    <strong>models</strong>.
    Atualmente, você deve ver alguns arquivos lá dentro, de exemplo,
    para você se basear no seu estudo.
</p>
<p>
    No Controller, em <em>var $uses = array();</em> você indica quais os Models
    devem ser carregados.
</p>
<p>
    Após ter dados na sua tabela usuarios, experimente digitar,
    no seu controller, <em>pr($this->Usuario->find();</em>. Ele vai retornar
    para você todos os dados da tabela <em>usuarios</em> automaticamente. Note
    o monitor de SQL no final da página.
</p>
<div class="nota dica">
    <p>
    <strong>Dica:</strong> Para maiores detalhes sobre como os models funcionam,
    visite o site do Acid.
    </p>
</div>

<h2><a name="configurando">Configurando e inicializando</a></h2>
<p>
    Primeiro, configure seu acesso ao <strong>banco de dados</strong>.
    Vá até o arquivo <em>app/config/database.php</em> e modifique as informações
    de conexão com o banco de dados. O Acid toma conta do resto.
</p>
<p>
    O <strong>modo Debug</strong> permite saber o que está acontecendo com sua
    aplicação no desenvolvimento, como o monitor SQL no final desta página.
    Você deve desativar esta opção para colocar sua aplicação em ambiente
    de produção no arquivo <em>app/config/core.php</em>.
</p>
<div class="nota dica">
    <p>
    <strong>Dica:</strong> Abra todos os arquivos do diretório app/config/. Eles
    estão comentados e são auto-explicativos.
    </p>
</div>

<h2><a name="maisajuda">Mais ajuda</a></h2>
<p>
    No diretório <strong>core/docs/</strong> você encontra documentação sobre
    os principais Helpers, Components e Behaviors.
</p>
<p>
    Para mais informações, visite o site oficial do Acid.
</p>
