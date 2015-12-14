## ORM

Por padrão de todas as tabelas são criadas em minúsculo e no plural e em inglês:
	
![tabelas](http://i.imgur.com/cCOI0Q6.png)

Toda tabela, com identidade deve possuir a seguinte estrutura base:

    'id' => int(11)
    'update_at' => datetime
    'created_at' => datetime NOT NULL

[!!] É obrigatória a utilização dos campos `updated_at` e `created_at` para todas as entidades.

### Belongs To
Para utilização de `belongs_to` o padrão é utilizar o nome da tabela remota no singular concatenado com `_id`, por exemplo se um produto possui uma categoria:

    // Tabela products
    'id' => int(11)
    'category_id' => int(11)

### Has Many
O `has_many` necessita possuir na tabela remota o nome da tabela princial no singular concatenado com `_id`, por exemplo se um produto possui várias tags, a tabela `tags` sera assim:

    // Tabela tags
    'id' => int(11)
    'product_id' => int(11)

### Has Many com através
Para `has_many` com através é necessário possuir uma tabela com o nome das duas tabelas (no plural) concatenadas, sendo a primeira a principal. Como exemplo a tablea `users_roles` possui os papéis da tabela `users`. Somente dois campos são válidos para esta, o nome das duas no singular concatenado com `_id`. Não é necessário fazer nenhuma mensão nas tabelas primárias.

    // Tabela users_roles
    'user_id' => int(11)
    'role_id' => int(11)