[!!] Antes de continuar é necessário instalar no mínimo o PHP ou o LAMP/XAMPP.

 - PHP 5.6 ou maior.

# O que é o Huia Template
Consiste em um sistema base para criação de aplicações WEB de baixa complexibilidade com velocidade, levando a lógica para a camada de interface.

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
`git clone http://malu.ncgroup.com.br/huia/template-php-kohana.git .`

![Install](http://i.imgur.com/fK3zYWb.png)

## Configuração de instalação

Rode o arquivo `install.bat` esse irá analizar o seu ambiente e instalar componentes necessários, lembrando que os dois programas que são impressindíveis são o php e o ruby 1.9+. Após rode o `run.bat` na raiz e entre no endereço `http://localhost:9000`

![Install Options](http://i.imgur.com/MM4xdKQ.png)

Basta selectionar quais os modulos que serão executados e salvar.

<style>
	#kodoc-body img {
		max-width: 100%;
	}
</style>