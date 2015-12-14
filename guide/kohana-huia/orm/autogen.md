### Geração de Banco de dados

O modulo possui a capacidade de gerar desde o banco de dados, até as tabelas e uma base de modelos de banco.

#### Configuração

    // application/config/huia/autogen.php
    return array(
        'autogen' => array(
            'database' => Kohana::$environment !== Kohana::PRODUCTION,
            'tables'   => FALSE,
            'models'   => Kohana::$environment === Kohana::DEVELOPMENT,
        ),
        'ignore_models' => array('Log', 'Role', 'User', 'User_Token'),
    );

#### Criação dos modelos

Ao ler o banco de dados é criada uma estrutura básica de modelos baseada no mesmo. Não é recomendado mecher na pasta `application/classes/Model/Base/*` pois essa gera sempre em desenvolvimento os arquivos. A leitura do arquivo origina arquivos similares a esse:

    class Model_Base_Tag extends Model_App {

        protected $_table_columns = array(
            'id' => array (
                'data_type' => 'int',
                'extra' => 'auto_increment',
                'key' => 'PRI',
                'display' => '11',
            ),
                'name' => array (
                'data_type' => 'varchar',
                'key' => 'UNI',
                'character_maximum_length' => '64',
            ),
                'category_id' => array (
                'data_type' => 'int',
                'display' => '11',
            ),
                'created_at' => array (
                'data_type' => 'datetime',
            ),
                'updated_at' => array (
                'data_type' => 'datetime',
                'is_nullable' => TRUE,
            ),
        );

        protected $_belongs_to = array(
            'category' => array('model' => 'Category'),
        );

        public function rules()
        {
            return array(
                'name' => array(
                    array(array($this, 'unique'), array(':field', ':value')),
                    array('not_empty'),
                    array('max_length', array(':value', 64)),
                ),
                'category_id' => array(
                    array('numeric'),
                    array('not_empty'),
                    array('max_length', array(':value', 11)),
                ),
            );
        }

        public function labels()
        {
            return array(
                'name' => __('Name'),
                'category' => __('Category'),
            );
        }

    }

Também é gerado um arquivo modelo vazio para caso necessite de alguma modificação essa seja executada nele:

    class Model_Tag extends Model_Base_Tag {}

O objetivo de possuir a table colums é manter um cache do schema do banco, caso seja necessário acompanhar a atualização do mesmo e para otimizar as buscas em produção.

#### Relacionamentos

Como pode ser observado no exemplo acima o sistema reconhece e sugere elementos relacionados, seguindo o design patters descrito no item acima [ORM](orm).

#### Validações

- **Único**: Caso o campo não seja o `id`, mas mesmo assim seja único ele recebe uma validação para garantir que não possuirá uma tentativa de duplicidade.
- **Não núlo**: Se o campo possuir a propriedade not null.
- **Tamanho máximo**: Baseado no tamanho descrito para o campo é gerada a validação.
- **CPF**: Se o campo for `cpf` ele passa uma validação para o mesmo.
- **Email**: Se o campo possuir o nome `email` será validado com o validados interno do kohana.