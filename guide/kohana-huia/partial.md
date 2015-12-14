### Partial

Essa classe serve para imprimir de forma condicional a rota uma view relacionada.
Seu objetivo principal é, caso o padrao do projeto seja de navegação vertial e necessite carregar inicialmente uma parte, relacionada a rota, a mesma se faça com o mínimo de progração agregada.

    <div id="home" data-loaded="<?php echo ((Partial::is('inicio')) ? 'true' : 'false'); ?>">
        <?php echo Partial::factory('home', 'inicio'); ?>
    </div>

    <div id="kits" data-loaded="<?php echo ((Partial::is('presentes')) ? 'true' : 'false'); ?>">
        <?php echo Partial::factory('presentes'); ?>
    </div>

No exemplo acima quando o usuário solicitar `/presentes` o backend irá imprimir a sub view de presentes, já se entrar em `/inicio` a resposta será a view correspondente. O front-end nesse caso se encarrega de buscar o resto das informações.