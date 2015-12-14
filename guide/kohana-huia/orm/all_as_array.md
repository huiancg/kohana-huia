## Retorno de modelo como array

Uma das melhorias mais interessantes do modulo base, permite retornar um modelo com todas as suas dependências, ou seja, se você possui um Produto que possui Tags e uma categoria, ao executar:

	// Modelo de exemplo
	class Model_Product extends Model_App {

		protected $_has_many = array(
			'tags' => array('model' => 'Tag'),
		);

		protected $_belongs_to = array(
			'category' => array('model' => 'Category'),
		);

	}

	// Digamos que busque o primeiro produto
	$product = Model_Product::factory('Product', 1);

	// E queira utilizar todo seu conteúdo
	echo Debug::vars($product->all_as_array());

Terá o retorno similar a esse com todos os relacionados:

	array(
		'id' => 1,
		'tags' => array(
			array(
				'id' => 1,
				'name' => 'Tag 1',
			),
			array(
				'id' => 2,
				'name' => 'Tag 2',
			),
		),
		'category_id' => 1,
		'category' => array(
			'id' => 1
			'name' => 'Categoria 1'
		),
	)

### Tipos que campo com arquivos

Caso o campo inicie com `image` `thumb` `file_` `upload_` o campo será reconhecido como arquivo e o seu retorno acontecerá com a url `public/%NOME_DA_TABELA%/%NOME_DO_ATTRIBUTO%/%VALOR_DO_CAMPO%`

### Auto slug

Caso na tabela exista o campo `title` ou `name` será gerado um campo no retorno chamado `slug` que utilizará o `URL::slug` para gerar o campo, baseado nesse campo mais o `id`.

	array(
		'id' => 1,
		'title' => 'Foobar'
	)

Um objeto similar a esse terá o seguinte retorno:

	array(
		'id' => 1,
		'title' => 'Foobar',
		'slug' => '/foobar-1'
	)