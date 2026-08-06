# Respostas do Candidato - Teste Técnico Full Stack

---

## Questão 1 - Vue.js
**Pergunta:** Explique como funciona o ciclo de vida de um componente em Vue.js e como isso influencia na performance de uma aplicação.

**Resposta do Candidato:**
> "Os componentes possuem gatilhos (*hooks*) prévios e posteriores às etapas de seu ciclo de vida: criação, montagem, atualização, desmontagem, ativação e desativação. Vale destacar que os dois últimos (`activated` e `deactivated`) não possuem um gatilho de execução prévia, operando apenas de forma direta no momento de sua ocorrência.
>
> **Complemento sobre performance:**
> A etapa de maior custo de processamento para o renderizador do navegador é a criação e destruição de elementos no DOM. Sendo assim, existe o componente nativo `<KeepAlive>`, que envolve outros componentes e deve ser utilizado preferencialmente naqueles reutilizados com frequência na aplicação. Esses componentes são apenas desativados quando não estão em uso e reativados em seguida ao serem requisitados, preservando seu estado e dados em memória.
>
> Os gatilhos `onActivated` e `onDeactivated` definem as regras que otimizam o processo de ativação e desativação desses componentes. Eles são responsáveis por realizar a limpeza e o gerenciamento de processos em segundo plano no frontend (como interromper uma consulta em tempo real (*polling*) durante o estado inativo e reestabelecê-la ao reativar o componente).
>
> Já para componentes que não necessitam armazenar estado entre páginas ou reutilização frequente, utilizam-se as diretivas `v-if` e `v-show`. A primeira controla a criação ou destruição do nó no DOM, enquanto a segunda altera apenas sua visibilidade via CSS (`display: none`), permitindo ocultar o elemento em vez de destruí-lo quando for convenientemente mantido na árvore do DOM por economia de recursos."

---

## Questão 2 - Laravel + Vue.js
**Pergunta:** Em aplicações Laravel + Vue.js, qual é a melhor abordagem para lidar com autenticação entre o frontend e o backend? Explique um fluxo seguro para login, armazenando tokens e a comunicação entre as camadas.

**Resposta do Candidato:**
> "A melhor abordagem é a autenticação baseada em Cookies de Sessão (gerenciados pelo Laravel Sanctum). Ao carregar a aplicação, é realizada uma requisição inicial para obtenção do token CSRF. No momento do login, o ID de sessão gerado no backend associa o usuário no banco de dados e retorna um Cookie de Sessão seguro para o navegador. 
> 
> A partir das requisições subsequentes (após a autenticação), o navegador envia automaticamente o cookie. O Laravel consulta então o Redis (banco de dados em memória de alta performance) para validar a chave de sessão em relação aos dados salvos no servidor. Esse fluxo gerencia toda a autenticação e o sistema de permissões do usuário, visto que os dados associados no Redis contêm as informações do usuário e suas respectivas regras de acesso. Adicionalmente, utiliza-se o token CSRF em conjunto para garantir que as requisições sejam originadas legitimamente do frontend da aplicação."

---

## Questão 3 - APIs
**Pergunta:** Um endpoint da API está demorando muito para responder. Quais técnicas você usaria para identificar e resolver esse problema no backend?

**Resposta do Candidato:**
> "Inicialmente, realiza-se a análise de logs e a inspeção do código para identificar se a API executa operações de alto custo de I/O, tais como consultas a banco de dados, chamadas a serviços externos ou leitura/escrita em arquivos. Havendo operações de I/O intensivo, analisa-se individualmente se esses destinos apresentam lentidão pontual ou consistente. Em seguida, avalia-se se o comportamento afeta outros usuários ou se trata de um cenário isolado decorrente de uma demanda de escala não prevista na modelagem inicial.
> 
> Com essas informações consolidadas, define-se o nível da intervenção necessária: refatoração de código, otimização de infraestrutura ou resolução pontual da inconsistência de dados do usuário em questão, evitando reestruturações desnecessárias caso a falha seja isolada.
>
> *Complemento sobre soluções técnicas:* Caso seja identificada a necessidade de reestruturação por gargalos no banco de dados ou processamentos extensos, as melhores soluções técnicas envolvem a implementação de **Filas Assíncronas (Queues)** via Redis (transferindo o processamento pesado para segundo plano) e a adoção do **Redis como camada de Cache** para armazenar os resultados de consultas frequentes."

---

## Questão 4 - Integrações
**Pergunta:** Como a utilização de CDN melhora a performance de uma aplicação web?

**Resposta do Candidato:**
> "As CDNs (*Content Delivery Networks*) são redes de servidores de borda (*Edge Servers*) de alta disponibilidade dedicadas à entrega de arquivos estáticos (*assets*). Sua utilização permite que o navegador do cliente solicite esses arquivos diretamente aos servidores da CDN, garantindo uma entrega significativamente mais célere do que quando servidos pela infraestrutura principal da aplicação.
> 
> Além de acelerar o carregamento das páginas ao entregar arquivos CSS, JavaScript e imagens com menor latência, essa abordagem descarrega o servidor de origem (*Server Offloading*). Isso reduz o consumo de CPU e I/O de disco do backend — custos que muitas vezes superam o processamento de rotas de API —, otimizando a performance global do sistema e trazendo eficiência à gestão de infraestrutura."

---

## Questão 5 - Engenharia de Software e Banco de Dados
**Pergunta:** Em uma aplicação Laravel utilizando MySQL (RDS), por que seria interessante usar Memcached ou ElastiCache? Como essas tecnologias ajudam na escalabilidade?

**Resposta do Candidato:**
> "O uso de soluções de cache em memória como Memcached, Redis ou AWS ElastiCache é vantajoso por armazenar as consultas mais frequentes do banco de dados, disponibilizando os dados para entrega imediata nas requisições subsequentes. Essas tecnologias mantêm as informações diretamente na memória RAM, cujo tempo de acesso é exponencialmente menor do que a leitura em disco (SSD/HD).
> 
> Para dados com baixa frequência de alteração e alta taxa de leitura (como catálogo de produtos em um e-commerce ou configurações do sistema), a camada de cache proporciona um ganho expressivo de performance. Em frameworks como o Laravel, o ciclo de vida do cache pode ser gerenciado de forma reativa, invalidando ou atualizando os registros no Redis no momento em que ocorrem alterações no banco relacional, garantindo que a informação esteja sempre disponível de forma rápida para o acesso dos usuários.
>
> *Complemento sobre Escalabilidade & Custos:* Em serviços de banco de dados gerenciados na nuvem (como o Amazon RDS MySQL), o faturamento é fortemente impactado pela utilização de CPU e IOPS. A redução das consultas diretas ao banco via cache resulta em uma diminuição significativa nos custos operacionais de infraestrutura (FinOps). Além disso, clusters de cache distribuído oferecem escalabilidade horizontal e alta disponibilidade (*High Availability*) por meio de replicação multi-nó, aumentando a resiliência da aplicação."

---

## Questão 6 - Engenharia de Software e Banco de Dados
**Pergunta:** Quais as vantagens e desvantagens em utilizar o Eloquent do Laravel? Existe algum possível problema recorrente (N+1 queries)?

**Resposta do Candidato:**
> "A principal vantagem do Eloquent ORM reside na simplificação da camada de persistência, permitindo manipular relacionamentos entre tabelas como propriedades de objetos. Adicionalmente, o ORM fornece proteção nativa contra injeção de SQL (*SQL Injection*) por meio do uso de *Prepared Statements*. Em contrapartida, sua principal desvantagem surge ao estruturar consultas altamente especializadas (como *selects* complexos contendo múltiplos blocos `CASE` e subqueries), que podem ser mais legíveis quando escritas em SQL nativo ou via *Query Builder*.
>
> *Complemento sobre o problema de N+1 Queries:*
> O problema de N+1 queries decorre da própria facilidade de acesso aos relacionamentos como propriedades do objeto. Ao consultar uma coleção de registros e iterar sobre seus relacionamentos sem pré-carregamento, o ORM executa uma consulta inicial para a lista principal (1) e consultas individuais adicionais para cada item da coleção (N). A solução consiste em utilizar o *Eager Loading* via método `with()`, instruindo o Eloquent a realizar o pré-carregamento dos relacionamentos em consultas otimizadas no banco de dados."

---

## Questão 7 - Engenharia de Software e Banco de Dados
**Pergunta:** Explique como implementar um sistema de filas assíncronas no Laravel. Para que tipo de funcionalidades essa abordagem é útil?

**Resposta do Candidato:**
> "Filas assíncronas devem ser adotadas em endpoints de API que realizam operações de alto custo computacional, de longa duração ou que não exigem resposta síncrona imediata ao cliente — tais como a importação e o processamento de planilhas de produtos para atualização de estoque. Nesses cenários, o endpoint valida a requisição, empacota os parâmetros e despacha um *Job* do Laravel para execução em segundo plano. O cliente recebe uma resposta HTTP imediata (ex: status 202 *Accepted* com a mensagem 'Processamento de estoque iniciado'), acompanhada de um identificador para consultar o progresso do processamento assíncrono."

---

## Questão 8 - Engenharia de Software e Banco de Dados
**Pergunta:** Quando é interessante utilizar transactions?

**Resposta do Candidato:**
> "Transações de banco de dados (*Transactions*) devem ser utilizadas ao executar conjuntos de operações de escrita (`INSERT`, `UPDATE`, `DELETE`) que possuem interdependência negocial. Elas asseguram a propriedade da **Atomicidade** (conceito ACID): caso qualquer instrução falhe durante a execução, todas as alterações anteriores são revertidas (*Rollback*), impedindo a persistência parcial ou a corrupção do estado dos dados."

---

## Questão 9 - O que você entende do log abaixo?
**Pergunta:** Analise o log de erro retornado pela aplicação Laravel (`FatalThrowableError: Call to a member function getImage() on null` em `Imagem.php:147`).

**Resposta do Candidato:**
> "O log de erro indica a tentativa de encadeamento de chamada a um método em um objeto nulo (`Call to a member function getImage() on null`). Durante a consulta aos detalhes de um item na API, a aplicação tentou obter o registro de imagem associado, mas a inexistência dessa relação no banco de dados resultou em um valor nulo sem o devido tratamento. A solução consiste em implementar uma verificação de nulidade (utilizando o operador *Nullsafe* do PHP 8, `?->`) e definir uma imagem padrão (*placeholder*) como *fallback*."

---

## Questão 10 - Desenvolvimento Full-Stack (Laravel + Vue.js) & Questão 12 (Checkout e Busca por CEP)
**Pergunta:** Crie um pequeno CRUD de produtos utilizando Laravel para o backend e Vue.js no frontend. A aplicação deve permitir criação, leitura, atualização e deleção (CRUD) de produtos. / Página de Checkout com CEP.

**Resposta do Candidato:**
> "O projeto foi estruturado seguindo os princípios de desenvolvimento enxuto (*no unnecessary boilerplate or over-engineering*), acompanhado de documentação completa no arquivo [`README.md`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/README.md) na raiz do projeto. É dividido em 3 diretórios principais:
>
> 1. **`infra/`**: Contém o arquivo [`docker-compose.yml`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/infra/docker-compose.yml) configurado com o **Traefik v3** como Proxy Reverso único na porta 880 (roteamento unificado para front e back na URL `http://localhost:880`).
> 2. **`backend/`**: Aplicação Laravel contendo a migration [`create_produtos_table.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/database/migrations/2026_01_01_000000_create_produtos_table.php), model [`Produto.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Models/Produto.php), controller REST [`ProdutoController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/ProdutoController.php) (com **Redis Cache**), controller [`AuthController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/AuthController.php), o **Comando Artisan Customizado** [`CreateUserCommand.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Console/Commands/CreateUserCommand.php) (`php artisan user:create {email} {name} {password}`), rotas protegidas em [`routes/api.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/routes/api.php) e os **testes automatizados de API e CLI** em [`ProdutoTest.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/tests/Feature/ProdutoTest.php) e [`CreateUserCommandTest.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/tests/Feature/CreateUserCommandTest.php).
> 3. **`frontend/`**: Aplicação Vue 3 (Composition API com `<script setup>`) e Vite em [`App.vue`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/App.vue) e **testes unitários de componentes** em [`App.spec.js`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/__tests__/App.spec.js)."

---

## Questão 11 - Análise Crítica e Refatoração de Código PHP

**Pergunta:** Critique o código abaixo e como poderia ser melhorado:
```php
/**
 * @param InterfaceItemPedido $orderItem
 * @return array
 */
public function transform(InterfaceItemPedido $orderItem)
{
    return [
        'item_pedido_id' => $orderItem->getId(),
        'product_id' => $orderItem->getProductId(),
        'quantity' => $orderItem->getQuantity(),
        'price' => $orderItem->getPrice(),
        'total_price' => $orderItem->getTotalPrice(),
        'discount' => $orderItem->getDiscount(),
        'product_name' => $orderItem->getProduct()->getName(),
        'link_rewrite' => $orderItem->getProduct()->getLinkRewrite(),
        'size_name' => $orderItem->getSize()->getName(),
        'gender' => $orderItem->getSize()->getGender(),
        'gender_name' => $orderItem->getSize()->getLongGender(),
        'active' => $orderItem->getProduct()->isActive(),
        'type' => $orderItem->getProduct()->getType()
    ];
}
```

**Resposta do Candidato:**
> "O método apresenta vícios de implementação: realiza chamadas encadeadas profundas (violando a Lei de Demeter) e carece de tratamento para relacionamentos ausentes ou deletados no banco de dados, o que pode gerar uma exceção fatal (`FatalThrowableError`) e interromper o retorno da API. Atribuir instâncias locais (`$product = $orderItem->getProduct()` e `$size = $orderItem->getSize()`) evita a reinvocação repetida de métodos na memória. Para assegurar resiliência, deve-se adotar o operador *Nullsafe* (`?->`) combinado com valores padrão (*fallbacks*), garantindo que a ausência de um produto ou tamanho não rompa o contrato do JSON."

---

## Questão 13 - Descreva uma situação em que você teve que lidar com um problema técnico complexo ou um imprevisto em um projeto de desenvolvimento (Método STAR: Situação, Tarefa, Ação, Resultado)

**Resposta do Candidato:**

- **S — Situação (Situation):** Um cliente reportou um problema crítico de comunicação: as mensagens enviadas ativamente pelo sistema não estavam aparecendo no aparelho celular (dispositivo móvel) do contato/destinatário final.
- **T — Tarefa (Task):** Isolar e diagnosticar a causa raiz da falha de entrega — verificando se tratava-se de um problema no disparo/envio, na camada de sincronização de dados ou de um bug não mapeado no código da plataforma.
- **A — Ação (Action):**
  1. **Análise Comparativa de Evidências Visual e Comportamental:** Avaliei os prints de tela enviados pela cliente (comparando a interface do sistema versus a tela do dispositivo móvel do destinatário), o que permitiu identificar que o problema ocorria exclusivamente com as mensagens originadas e disparadas ativamente pelo sistema (e não com as respostas/mensagens vindas do contato).
  2. **Investigação da Cadeia de Logs Internos:** Analisei os logs da ferramenta de envio de mensagens do sistema, os quais registravam incorretamente que o disparo havia sido efetuado com sucesso.
  3. **Inspeção no Gateway de Erros da Plataforma de Mensageria:** Acessei o gateway de erros da API da plataforma de comunicação de terceiros. Constatei que, no exato intervalo de tempo dos disparos, a plataforma retornava erros de recusa informando que o sistema não podia enviar a mensagem por descumprimento de diretrizes de uso (bloqueio por janela de conversação fechada).
  4. **Identificação da Causa Raiz:** Descobri que o sistema utilizava "suposições calculadas" (cálculos locais estimados de tempo) para adivinhar se a janela de comunicação de 24h com o contato estava aberta, em vez de consultar e validar os status reais fornecidos pelo provedor.
  5. **Desenvolvimento da Solução:** Como parte de uma demanda em andamento (integração do recebimento de status via gateway), desenvolvi e implementei um mecanismo de captura e registro de webhooks/eventos com valores realistas (provenientes diretamente das respostas do gateway da plataforma), eliminando completamente o cálculo por suposição.
- **R — Resultado (Result):** A solução implementou rastreabilidade real e confiável sobre a abertura/fechamento das janelas de comunicação, eliminando falsos positivos e prevenindo o bloqueio de envios por descumprimento de diretrizes das plataformas de mensageria (funcionalidade desenvolvida e atualmente em fase final de testes para implantação em produção).

---

## Questão 14 - SQL e Eloquent (Clientes e Pedidos)

**Enunciado do PDF:**
> Utilizando a seguinte estrutura de tabelas no MySQL para um banco de dados de vendas:
> - `clientes` (`id`, `nome`, `email`, `estado`)
> - `pedidos` (`id`, `cliente_id`, `data_pedido`, `valor_total`)
>
> Utilizando o Eloquent do Laravel:
> - Escreva uma query para listar os estados com o maior volume de vendas (soma de `valor_total`).
> - Escreva uma query para listar os 5 clientes que mais compraram (considerando `valor_total`).
> - Qual seria a melhor forma de otimizar a performance dessas consultas?

---

### 14.1 - Query em Eloquent (Estados com maior volume de vendas - Soma de `valor_total`):

```php
use App\Models\Cliente;
use Illuminate\Support\Facades\DB;

// Query em Eloquent do Laravel para agrupar por estado e somar o valor_total das vendas
$vendasPorEstado = Cliente::join('pedidos', 'clientes.id', '=', 'pedidos.cliente_id')
    ->select('clientes.estado', DB::raw('SUM(pedidos.valor_total) as total_vendas'))
    ->groupBy('clientes.estado')
    ->orderByDesc('total_vendas')
    ->get();
```

---

### 14.2 - Query em Eloquent (5 Clientes que mais compraram):

```php
use App\Models\Cliente;

// Query em Eloquent nativo utilizando withSum para agregação de relacionamentos e corte de 5 clientes
$top5Clientes = Cliente::withSum('pedidos', 'valor_total')
    ->orderByDesc('pedidos_sum_valor_total')
    ->limit(5)
    ->get();
```

---

### 14.3 - Melhor forma de otimizar a performance dessas consultas:

1. **Índices Estratégicos no Banco de Dados (MySQL):**
   - **Índice na Chave Estrangeira (`FK`):** Indexar a coluna `pedidos(cliente_id)` para aceleração imediata da junção (`join`).
   - **Índice Composto (Covering Index / Index-Only Scan):** Criar um índice composto em `pedidos(cliente_id, valor_total)`. Isso permite que o MySQL resolva a soma do valor total diretamente na árvore do índice sem precisar ler a tabela em disco.
   - **Índice na Coluna de Agrupamento:** Indexar a coluna `clientes(estado)` para otimizar a ordenação e agrupamento por localização.
   - **Índice de Intervalo Temporal:** Indexar `pedidos(data_pedido)` para acelerar consultas por intervalos de datas em cenários reais.

2. **Caching em Memória (Redis / Memcached):**
   - Utilizar **Redis Cache** no Laravel (`Cache::remember()`) para armazenar o resultado das consultas de agregação com TTL estratégico (ex: 1 hora), invalidando o cache reativamente apenas quando novos checkouts forem inseridos.

3. **Eager Loading no Eloquent & Agregação Assíncrona:**
   - Utilizar o carregamento adiantado (`with()`) no Eloquent para evitar consultas N+1 ao iterar sobre relacionamentos.
   - Em volumes extremos (milhões de pedidos), utilizar tarefas em segundo plano (Laravel Queues/Jobs) para manter uma tabela agregada `relatorio_vendas_estado`.

---

## Questão 15 - SQL e Eloquent (Produtos, Fornecedores e Estoque)

**Enunciado do PDF:**
> Você trabalhará com um banco de dados relacional contendo informações sobre produtos, fornecedores e estoque. Seu objetivo é escrever queries SQL para obter insights a partir dessas tabelas, aplicando funções de agregação e filtragem de dados.
>
> **Estrutura de Tabelas:**
> - `produtos` (`id`, `nome`, `fornecedor_id`, `categoria`, `preco_unit`, `data_aquisicao`)
> - `fornecedores` (`id`, `nome`, `pais`, `status`)
> - `estoque` (`id`, `produto_id`, `quantidade`, `data_contagem`)
>
> **Utilizando o Eloquent do Laravel:**
> - Escreva uma query para identificar os produtos que possuem estoque abaixo da média geral.
> - Escreva uma query para identificar quais fornecedores possuem produtos cujo preço unitário é maior que a média dos preços dentro de sua categoria? Exiba o nome do fornecedor, nome do produto, categoria e o preço. (Para melhor análise, lembre de ordenar a tabela por categoria e preço unitário dos produtos.)
> - Escreva uma query para identificar quais são os produtos mais recentes, adquiridos de fornecedores do Brasil, e possuem estoque acima da média de todos os produtos.

---

### 15.1 - Query em Eloquent (Produtos com estoque abaixo da média geral):
```php
use App\Models\Produto;
use App\Models\Estoque;

// 1. Média escalar geral de quantidade no estoque
$mediaEstoque = Estoque::avg('quantidade');

// 2. Consulta Eloquent com filtro no relacionamento de estoque e Eager Loading
$produtosEstoqueBaixo = Produto::whereHas('estoque', function ($query) use ($mediaEstoque) {
    $query->where('quantidade', '<', $mediaEstoque);
})->with('estoque')->get();
```

---

### 15.2 - Query em Eloquent (Fornecedores com produtos com preço acima da média da sua categoria):
```php
use App\Models\Produto;

$produtosPrecoAcimaMediaCategoria = Produto::from('produtos as p1')
    ->join('fornecedores as f', 'p1.fornecedor_id', '=', 'f.id')
    ->where('p1.preco_unit', '>', function ($query) {
        $query->selectRaw('AVG(p2.preco_unit)')
            ->from('produtos as p2')
            ->whereColumn('p2.categoria', 'p1.categoria');
    })
    ->select('f.nome as nome_fornecedor', 'p1.nome as nome_produto', 'p1.categoria', 'p1.preco_unit')
    ->orderBy('p1.categoria', 'asc')
    ->orderBy('p1.preco_unit', 'desc')
    ->get();
```

---

### 15.3 - Query em Eloquent (Produtos mais recentes de fornecedores do Brasil com estoque acima da média):
```php
use App\Models\Produto;
use App\Models\Estoque;

// 1. Média geral de estoque no banco
$mediaEstoque = Estoque::avg('quantidade');

// 2. Consulta Eloquent integrando relacionamentos de Fornecedor e Estoque
$produtosRecentesBrasilEstoqueAlto = Produto::whereHas('fornecedor', function ($query) {
        $query->where('pais', 'Brasil');
    })
    ->whereHas('estoque', function ($query) use ($mediaEstoque) {
        $query->where('quantidade', '>', $mediaEstoque);
    })
    ->with(['fornecedor', 'estoque'])
    ->orderByDesc('data_aquisicao')
    ->get();
```

---

<!-- Comentário: Versões antigas do repositório apresentam as respostas sem esta formatação. -->

> **Nota de rodapé:** O conteúdo destas respostas foi reestruturado com ajuda de inteligência artificial (IA).[^1]

[^1]: Versões antigas do repositório apresentam as respostas originais sem esta formatação.
