## Dynamic Finder

Permite gerar dinamicamente buscas dentro de modelos ORM em queries simples.
É possível utilize `find` `find_all` `first` `last` `count` para abuscar e `and` e `or` para concatenar as queries e `limit` e `order_by` para organizar.

Se você possuir um campo name em uma modelo, por exemplo, e executar a seguinte query:
	
	$orm->find_by_name('eduardo');

O que irá acontecer, se não existir um metodo com esse nome no modelo, é que ele irá interpretrar da seguinte forma:

	$orm->where('name', '=', 'eduardo')->find();

Caso utilize somente um valor, os próximos serão tidos como o mesmo, como no exemplo:
	
	// $orm->or_where('name', '=', 'eduardo')->or_where('email', '=', 'eduardo')->find_all();
	$orm->find_all_by_name_or_email('eduardo');

Mais alguns exemplos de utilização:	

	// $orm->where('name', '=', 'eduardo')->find();
	$orm->find_by_name('eduardo');

	// $orm->where('name', '=', 'eduardo')->find_all();
	$orm->find_all_by_name('eduardo');

	// $orm->where('name', '=', 'eduardo')->count_all();
	$orm->count_by_name('eduardo');

	// $orm->or_where('name', '=', 'eduardo')
	//     ->or_where('email', '=', 'eduardo.pacheco@kanema.com.br')
	//     ->find_all();
	$orm->find_all_by_name_and_email('eduardo', 'eduardo.pacheco@kanema.com.br');
	
	// $orm->where('name', 'eduardo')
	//     ->where('email', '=', 'eduardo.pacheco@kanema.com.br')
	//     ->where('is_active', '=', TRUE)
	//     ->find_all();
	$orm->find_all_by_name_and_email_and_is_active('eduardo', 'eduardo.pacheco@kanema.com.br', TRUE);
	
	// $orm->where('name', 'eduardo')
	//     ->where('email', '=', 'eduardo.pacheco@kanema.com.br')
	//     ->where('is_active', '=', TRUE)
	//     ->limit(5)
	//     ->find_all();
	$orm->find_all_by_name_and_email_and_is_active_limit('eduardo', 'eduardo.pacheco@kanema.com.br', TRUE, 5);

	// $orm->where('name', '=', 'eduardo')
	//     ->order_by('id', 'ASC')
	//     ->find();
	$orm->first_by_name('eduardo');
	
	// $orm->where('name', '=', 'eduardo')
	//     ->order_by('id', 'DESC')
	//     ->find();
	$orm->last_by_name('eduardo');