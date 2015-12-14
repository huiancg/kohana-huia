# O que é o Huia Template
Consiste em um sistema base para criação de aplicações WEB de baixa complexibilidade com velocidade, levando a lógica para a camada de interface.

### Antes de continuar é necessário instalar no mínimo o PHP ou o LAMP/XAMPP.
 - PHP 5.6.

# Ambiente
Obrigatóriamente o sistema de desenvolvimento deverá possuir os seguintes componentes:

- [node.js](https://nodejs.org/download/)
- [Composer](https://getcomposer.org/download/)
- **grunt**: `npm install grunt -g`
- **grunt-cli**: `npm install grunt-cli -g`
- **bower**: `npm install bower -g`
	* A utilização do bower é facultativa para novos projetos.

# Projeto Base

## Clone o repositório:
`git clone https://github.com/huiancg/kohana-huia.git .`

![Install](http://i.imgur.com/fK3zYWb.png)

## Configuração de instalação

Rode o arquivo `install.bat` esse irá analizar o seu ambiente e instalar componentes necessários, lembrando que os dois programas que são impressindíveis são o php e o ruby 1.9+. Após rode o `run.bat` na raiz e entre no endereço `http://localhost:9000`

![Install Options](http://i.imgur.com/MM4xdKQ.png)

Basta selectionar quais os modulos que serão executados e salvar.

## Guia de usuário
- [Configurações](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/bootstrap.md)
- [Controllers](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller.md)
    + [Rotas automáticas](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/rotes.md)
    + [Auto Cache](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/cache.md)
    + [View com ajax](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/ajax.md)
    + [Prerender](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/prerender.md)
    + [Meta tags](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/meta_tags.md)
    + [Variáveis da View](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/vars.md)
    + [JSON](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/controller/json.md)
- [Modelos](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/orm.md)
	+ [Retorno de modelo como array](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/orm/all_as_array.md)
    + [Dynamic finder](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/orm/dynamic_finder.md)
	+ [Geração de modelos e tabelas](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/orm/autogen.md)
- [Analytics](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/analytics.md)
- [Partial](https://github.com/huiancg/kohana-huia/blob/master/guide/kohana-huia/partial.md)
