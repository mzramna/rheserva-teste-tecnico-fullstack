# Respostas do Candidato - Teste Técnico Full Stack

---

## Questão 1 - Vue.js
**Pergunta:** Explique como funciona o ciclo de vida de um componente em Vue.js e como isso influencia na performance de uma aplicação.

**Resposta do Candidato (Versão Final Revisada):**
> "O elemento tem gatilhos para antes e o momento em que as etapas dele são executadas: criação, atualização, desmontagem, montagem, ativação e desativação, sendo que os últimos 2 não tem o gatilho para antes deles executarem, então apenas podem ser executados sem nenhum tipo de "realize isso antes de realizar o gatilho principal de fato".
>
> **Complemento sobre performance:**
> O mais pesado de processamento no renderizador é o processo de criar e destruir elementos. Sendo assim, existe o parâmetro KeepAlive que envolve os componentes e preferencialmente é usado apenas nos componentes que são reutilizados constantemente dentro do sistema. Esses componentes são apenas desativados quando não são necessários e reativados logo na sequência quando voltam a serem utilizados, mantendo assim todos os seus dados antes da desativação.
>
> Os gatilhos de `onActivated` e `onDeactivated` definem as regras que vão rodar para que seja mais otimizado o processo de ativação e desativação desses componentes e são responsáveis por fazer uma limpeza na forma como os processos em segundo plano do front vão lidar sempre que o componente alternar entre o estado ativo e inativo, por exemplo destruindo um processo de consulta em tempo real quando inativo e recriando ele novamente quando o elemento for reativado.
>
> Já para os componentes que não precisam armazenar dados entre páginas e não são reutilizados tão constantemente, têm os parâmetros `v-if` e `v-show`, onde o primeiro define a existência ou destruição de um componente e o outro define a sua visibilidade na página, possibilitando apenas ocultar algo ao invés de destruir quando esse elemento faz sentido se manter mesmo que invisível na tela, seja por economia de recursos ou outros critérios adicionais."

**Análise & Comparação com o Gabarito (Nota Máxima):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Correção da Precisão dos Hooks:** Ajustou com perfeição que **apenas os últimos 2 (`activated` e `deactivated`)** não possuem gatilhos de "antes".
  - **Domínio da Engenharia de Renderização:** Explicou corretamente que a criação/destruição no DOM é a etapa de maior custo para o renderizador do navegador.
  - **Uso Correto do `<KeepAlive>` e Hooks de Limpeza:** Demonstrou clareza ao explicar a preservação de dados em memória e o uso do `onDeactivated` para destruir polling/consultas em tempo real.
  - **Diferenciação `v-if` vs `v-show`:** Explicou com excelente fundamentação a diferença entre a destruição do nó no DOM (`v-if`) e a alteração de visibilidade por economia de recursos (`v-show`).

---

## Questão 2 - Laravel + Vue.js
**Pergunta:** Em aplicações Laravel + Vue.js, qual é a melhor abordagem para lidar com autenticação entre o frontend e o backend? Explique um fluxo seguro para login, armazenando tokens e a comunicação entre as camadas.

**Resposta do Candidato (Versão Ajustada e Corrigida):**
> "A melhor forma é o Cookie de Sessão (gerenciado pelo Laravel Sanctum). Quando a página abre, é emitida a primeira requisição. No momento em que o login é realizado, o token/ID de sessão gerado no backend atualiza no banco a relação com o usuário que está acessando e devolve o Cookie de Sessão para o navegador. 
> 
> A partir do segundo acesso em diante (após o login ter sido feito), sempre que essa contraparte enviada pelo navegador chegar em uma requisição HTTP, o Laravel consulta o Redis (banco em memória de alta performance) para comparar essa chave com a equivalência dos dados armazenados no servidor. Isso lida com toda a autenticação do usuário dentro do sistema, juntamente com o sistema de permissões de acesso, já que os dados associados no Redis contêm o usuário e suas respectivas permissões de uso e acesso. Além disso, utiliza-se em conjunto o token CSRF para garantir que as requisições venham da própria aplicação frontend."

**Análise & Comparação com o Gabarito (Nota Máxima / Resposta Excelente):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Identificação da Melhor Abordagem:** Especificou corretamente o uso do Cookie de Sessão com Laravel Sanctum.
  - **Fluxo de Sessão e Banco de Dados (Redis):** Explicou com absoluta precisão o fluxo de login onde a chave vai para o cliente e o backend valida o ID de sessão consultando a chave/valor no **Redis** (banco em memória) a partir das requisições subsequentes.
  - **Gestão de Autenticação e Permissões:** Esclareceu corretamente que os dados do usuário e suas permissões ficam salvos e associados no lado do servidor (no Redis).
  - **Integração com CSRF Token:** Citou a inclusão do token CSRF em conjunto para validação da origem das requisições.

---

## Questão 3 - APIs
**Pergunta:** Um endpoint da API está demorando muito para responder. Quais técnicas você usaria para identificar e resolver esse problema no backend?

**Resposta do Candidato (Com Complemento Técnico):**
> "Inicialmente análise de logs e análise do código para entender se essa API utiliza algum acesso a informação de alto I/O como acesso a banco, acesso a sistemas externos, acesso a arquivos e por aí vai. Se tiver acesso a alguma informação de alto I/O, então é interessante analisar individualmente se esses destinos do acesso estão apresentando alguma lentidão pontual ou alguma lentidão consistente; avaliar se isso se repete com outros usuários do sistema (pode ser algo específico do uso de um usuário devido a algo que quando projetado não tinha sido pensado na escala que esse usuário precisa para funcionar). Por fim, após coletar todas essas informações, avaliar em qual nível o ajuste deve ser feito: funcionamento do código, infraestrutura, escalabilidade do código, ou mesmo se foi algo pontual que apenas afetou uma situação isolada e ver se isso pode ser corrigido pontualmente sem ter que envolver desenvolvimento e infraestrutura, já que se for algo que não tem como afetar mais usuários, então pode ser mais válido apenas corrigir o erro desse usuário diretamente ao invés de reestruturar algo.
>
> *Complemento sobre soluções técnicas:* Caso seja identificada a necessidade de reestruturação por problemas relacionados ao banco de dados ou a processamentos pesados em segundo plano, uma das melhores soluções técnicas seria a implementação de **Filas Assíncronas (Queues)** via Redis (transferindo o processamento pesado para background) e a utilização do **Redis como camada de Cache** para armazenar os resultados das consultas ao banco de dados."

**Análise & Comparação com o Gabarito (Nota Máxima / Resposta Completa):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Metodologia Investigativa de I/O:** Identificou operações de entrada/saída como foco principal da investigação.
  - **Triagem Operacional Pragmática:** Excelente diferenciação entre problemas de escala individuais e gargalos de infraestrutura globais.
  - **Arquitetura de Solução com Redis:** Inclusão precisa das duas maiores técnicas de otimização no ecossistema Laravel/Backend: **Queues assíncronas no Redis** para processamento em background e **Cache de banco via Redis** para descarregar consultas repetitivas do banco de dados relacional.

---

## Questão 4 - Integrações
**Pergunta:** Como a utilização de CDN melhora a performance de uma aplicação web?

**Resposta do Candidato (Versão Ajustada):**
> "As CDNs são servidores de borda de terceiros de alta disponibilidade de dados sejam assets de qualquer tipo. Utilizar as CDNs faz com que o acesso a esses assets seja feito diretamente do computador cliente para os assets em CDN, que geralmente são entregues de maneira mais rápida do que quando o acesso a esses assets é feito na infraestrutura do código principal da empresa. Isso, além de acelerar o loading de páginas por carregar CSS, JS, imagens e outros tipos de arquivos mais facilmente, ainda alivia o peso nos servidores que a empresa utiliza, representando menos preocupação para a gerência de infraestrutura e maior velocidade de carregamento do sistema, já que muitas vezes, ao se carregar um asset de imagem por exemplo, o processo fica consumindo mais tempo de I/O do servidor do que uma consulta de API, o que faz o servidor ficar mais lento como um todo e não apenas para o carregamento de páginas."

**Análise & Comparação com o Gabarito (Nota Máxima / Resposta Impecável):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Uso Preciso de Terminologia:** Especificou corretamente a natureza da CDN como **servidores de borda (*Edge Servers*) de alta disponibilidade**, conectando tanto a redução de latência por proximidade física quanto a alta disponibilidade da infraestrutura.
  - **Descarregamento do Servidor de Origem (*Server Offloading*):** Explicou com absoluta clareza a economia de I/O de disco e CPU no servidor backend ao retirar a entrega de arquivos estáticos.
  - **Impacto na Experiência do Usuário:** Mencionou a aceleração no tempo de carregamento no navegador do cliente.

---

## Questão 5 - Engenharia de Software e Banco de Dados
**Pergunta:** Em uma aplicação Laravel utilizando MySQL (RDS), por que seria interessante usar Memcached ou ElastiCache? Como essas tecnologias ajudam na escalabilidade?

**Resposta do Candidato (Com Complemento de Escalabilidade & Custos):**
> "Isso é interessante porque Memcached, Redis ou ElastiCache são tecnologias que armazenam os dados mais consultados do banco de dados de forma que, quando essas mesmas queries forem feitas novamente, o sistema já tem esses dados carregados para entrega imediata e atualização posterior. Essas tecnologias no geral armazenam seus dados diretamente na memória RAM, que é extremamente mais rápida do que um arquivo dentro de um SSD ou HD; sendo assim, mesmo que não tivesse o processamento da consulta no banco de dados MySQL, ainda assim seria muito mais rápida a entrega da resposta. Quando são dados como informações de usuário que são muito próximas de estáticas no banco, esse tipo de cache pode representar um imenso ganho de performance no sistema, principalmente em situações como consulta de informações de produtos em um site de vendas (que são dados que não são alterados quase nunca e são acessados com uma frequência muito alta). Além disso, em frameworks como o Laravel, quando um dado em cache é atualizado no banco através do sistema, existe a possibilidade do cache automaticamente ser invalidado e gerado novamente ou apenas atualizado, fazendo assim com que sempre o dado esteja de maneira ultra rápida para o usuário acessar ele.
>
> *Complemento sobre Escalabilidade & FinOps:* Além de tudo isso, como o MySQL (RDS) utilizado é um serviço gerenciado na nuvem, a cobrança se baseia fortemente no consumo de processamento (CPU/IOPS) e não apenas no espaço em disco. Sendo assim, o fato de reduzir acessos diretos ao banco via cache gera uma grande economia de custos de infraestrutura para a empresa. Por fim, a maioria desses serviços de cache possui alta capacidade de escalabilidade horizontal com replicação multi-máquina (clusters), onde caso um dos nós caia, os dados de cache continuam vivos em réplicas. Isso aumenta a resiliência do sistema e suporta estratégias modernas de deploy e escalabilidade horizontal de aplicações."

**Análise & Comparação com o Gabarito (Nota Máxima / Resposta Completa):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Domínio de Performance Física (RAM vs Disco):** Explicou com absoluta precisão o ganho de velocidade ao acessar dados diretamente na RAM.
  - **Visão de Engenharia de Negócio & FinOps (Custos Cloud):** Excelente adição ao notar que reduzir o processamento no RDS reduz a fatura da AWS (cobrança por vCPU/IOPS).
  - **Escalabilidade Horizontal & Resiliência Multi-Node:** Explicou o uso de clusters de ElastiCache/Redis com replicação de nós, garantindo alta disponibilidade (*High Availability*) e suporte a pipelines modernos de deploy.
  - **Invalidação e Ciclo de Vida do Cache:** Mencionou corretamente a gestão de invalidação de cache pelo Laravel.

---

## Questão 6 - Engenharia de Software e Banco de Dados
**Pergunta:** Quais as vantagens e desvantagens em utilizar o Eloquent do Laravel? Existe algum possível problema recorrente (N+1 queries)?

**Resposta do Candidato (Com Complemento de N+1 Queries):**
> "A principal vantagem está na simplificação dos acessos ao banco; sendo assim, podemos tratar tabelas estrangeiras como se fossem variáveis dentro de um objeto. Além disso, ele adiciona camadas de proteção, já que não tem como fazer uma injeção de dados SQL dentro de uma requisição para forçar um acesso indevido no banco de dados por meio disso. As desvantagens estão em dificultar a execução de queries muito especializadas no banco de dados, como por exemplo uma query de select múltipla com múltiplos `CASE`s e sub-queries por `CASE`, que pode se tornar mais complexa de construir no Eloquent do que apenas usar o SQL bruto diretamente no código.
>
> *Complemento sobre o problema de N+1 Queries:*
> Sobre o N+1, existe sim o problema de carregar os dados do banco exatamente em uma das suas vantagens, que é tratar os dados de outras colunas como se fossem parâmetros do objeto. Assim, quando carregamos diversos dados do banco e iteramos sobre esses parâmetros, cada leitura desencadeia uma nova query no banco para carregar esses dados — daí N+1, onde 1 é a query inicial com vários elementos e N são as queries acessando cada um dos parâmetros relacionados. Uma forma de resolver isso é usar a palavra-chave `with` para já carregar dinamicamente os parâmetros que são mais utilizados diretamente no momento da 1ª query, e apenas deixar o carregamento tardio nos lugares onde os parâmetros são tão pouco consultados no sistema que não vale a pena serem carregados todas as vezes que o elemento original é carregado. E como a implementação do `with` é simples, caso isso se prove uma causa de lentidão futura no sistema, apenas adicionar o `with` já resolve o problema."

**Análise & Comparação com o Gabarito (Nota Máxima / Resposta Perfeita):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Identificação da Causa Raiz do N+1:** Explicou com absoluta clareza o paradoxo do N+1 surgir exatamente da facilidade do ORM em acessar relacionamentos como propriedades do objeto durante a iteração.
  - **Mecanismo da Matemática das Queries (1 + N):** Detalhou perfeitamente a query inicial (1) e as consultas individuais no loop (N).
  - **Solução com Eager Loading (`with`):** Identificou o uso do método `with()` do Eloquent para pré-carregamento.
  - **Tradeoff de Engenharia (Lazy vs Eager Loading):** Apresentou uma visão madura ao destacar que relacionamentos raramente acessados podem permanecer sem `with` inicial para não sobrecarregar consultas desnecessariamente.

---

## Questão 7 - Engenharia de Software e Banco de Dados
**Pergunta:** Explique como implementar um sistema de filas assíncronas no Laravel. Para que tipo de funcionalidades essa abordagem é útil?

**Resposta do Candidato:**
> "A melhor forma de implementar isso é em endpoints de API que fazem processamentos complexos, demorados ou que não têm necessidade de ter o resultado entregue de imediato para o usuário, como importações e processamentos de produtos cadastrados em uma planilha para alimentar uma tabela de estoque de produtos de um sistema. Nesses casos, o endpoint recebe os dados, faz um empacotamento deles de uma forma que possa ser passada para processamento em segundo plano e desencadeia um Job do Laravel para que ele seja processado em segundo plano. Depois de desencadear isso, o usuário já recebe uma resposta imediata como 'Iniciado o processamento de dados do estoque' (para complementar o exemplo anterior), e o usuário acessa em uma página o retorno desse processamento, seja 'processando' enquanto o processamento não estiver completo ou 'dados registrados no estoque' com um link para esses novos dados quando o processamento terminar."

**Análise & Comparação com o Gabarito (Excelente Domínio de UX Assíncrona & Arquitetura):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Exemplo de Caso de Uso Real (Importação em Lote):** Exemplo sensacional de importação de planilhas pesadas de produtos/estoque, um dos casos de maior impacto para o uso de filas.
  - **Design de API e Experiência do Usuário (UX):** Explicou com clareza a devolução imediata do status "Em Processamento" evitando timeout HTTP, e o acompanhamento de estado pelo usuário.
  - **Empacotamento e Dispatch:** Mencionou o empacotamento dos dados de entrada para despacho do Job no Laravel.

- **Terminologias Nativas do Laravel para Citar em Entrevistas:**
  - **Interface & Driver:** Mencionar o uso da interface `ShouldQueue`, o driver no `.env` (`QUEUE_CONNECTION=redis`) e o despacho via `ProcessarPlanilhaJob::dispatch($dados)`.
  - **Execução em Background:** Mencionar o comando trabalhador `php artisan queue:work` ou o painel de monitoramento **Laravel Horizon**.

---

## Questão 10 - Desenvolvimento Full-Stack (Laravel + Vue.js)
**Pergunta:** Crie um pequeno CRUD de produtos utilizando Laravel para o backend e Vue.js no frontend. A aplicação deve permitir criação, leitura, atualização e deleção (CRUD) de produtos.

**Resposta do Candidato (Projeto Prático, Documentação README, Traefik Proxy, Redis Cache, CLI Command & Testes):**
> "O projeto foi estruturado seguindo os princípios de desenvolvimento enxuto (*ponytail: no unnecessary boilerplate or over-engineering*), acompanhado de documentação completa no arquivo [`README.md`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/README.md) na raiz do projeto. É dividido em 3 diretórios principais:
>
> 1. **`infra/`**: Contém o arquivo [`docker-compose.yml`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/infra/docker-compose.yml) configurado com o **Traefik v3** como Proxy Reverso único na porta 880 (roteamento unificado para front e back na URL `http://localhost:880`).
> 2. **`backend/`**: Aplicação Laravel contendo a migration [`create_produtos_table.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/database/migrations/2026_01_01_000000_create_produtos_table.php), model [`Produto.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Models/Produto.php), controller REST [`ProdutoController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/ProdutoController.php) (com **Redis Cache**), controller [`AuthController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/AuthController.php), o **Comando Artisan Customizado** [`CreateUserCommand.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Console/Commands/CreateUserCommand.php) (`php artisan user:create {email} {name} {password}`), rotas protegidas em [`routes/api.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/routes/api.php) e os **testes automatizados de API e CLI** em [`ProdutoTest.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/tests/Feature/ProdutoTest.php) e [`CreateUserCommandTest.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/tests/Feature/CreateUserCommandTest.php).
> 3. **`frontend/`**: Aplicação Vue 3 (Composition API com `<script setup>`) e Vite em [`App.vue`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/App.vue) e **testes unitários de componentes** em [`App.spec.js`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/__tests__/App.spec.js)."

**Análise & Comparação com o Gabarito (Nota Máxima / Projeto Completo com Documentação, CLI & Testes):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Documentação de Projeto Integrada:** Instruções claras no [`README.md`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/README.md) detalhando comandos de setup, migrations, uso da CLI e execução de testes.
  - **Comando CLI Customizado:** Disponibilização da ferramenta Artisan `php artisan user:create` para criação interativa ou automatizada de usuários com validação.
  - **Testes Automatizados de CLI & API:** Cobertura de testes unitários/integração para o comando CLI (`CreateUserCommandTest.php`) e APIs do Laravel (`ProdutoTest.php`).
  - **Proxy Reverso Traefik & Cache Redis:** Acesso unificado na porta 880 e otimização de consultas via Redis.

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
> "O principal problema é que o produto ao ser listado está listando de maneira encadeada outros dados que podem estar referenciados no banco de forma a criar o problema N+1 que foi mencionado na questão anterior. Além disso, caso essas funções não tenham os devidos tratamentos pode ocorrer alguma exceção no código e quebrar a chamada de API por conta de algum campo nulo ou de dado relacionado deletado. Simplesmente instanciar um elemento com `$product = $orderItem->getProduct()` e outro com `$size = $orderItem->getSize()` ao invés de chamar `$orderItem->getProduct()` e `$orderItem->getSize()` diversas vezes já seria uma forma de corrigir ou ao menos minimizar o problema, além disso seria interessante fazer dentro das funções que são chamadas tratamentos de erro para que sempre que um dado não exista no banco ou não possa ser recuperado ele retornar `null` ou algum outro valor default dependendo do tipo do dado que será mostrado."

**Análise Crítica & Comparação com o Gabarito (Nota 9.5 / 10 - Excelente Identificação de Riscos):**

- **Pontos Positivos / Acertos Impecáveis:**
  - **Identificação da Quebra da API por Registro Nulo/Deletado:** Excelente percepção do risco de exceção não tratada ao acessar relacionamentos ausentes/deletados.
  - **Uso de Variáveis Locais / Evitando Encadeamento Repetitivo:** Corretamente identificou que instanciar `$product` e `$size` evita invocar métodos e acessar propriedades encadeadas repetidamente na memória.
  - **Tratamento de Exceções e Valoração Default:** Sugeriu tratar falhas e retornar `null` ou um valor *fallback* adequado para não romper o contrato do JSON retornado.
  - **Conexão com a Questão Anterior (N+1):** Demonstrou visão sistêmica ao relacionar a transformação ao gargalo N+1.

- **Precisão Técnica Adicional para Entrevistas (Diferenciação Sênior):**
  - **Diferença entre Memória PHP vs. Queries SQL:** Atribuir `$product = $orderItem->getProduct()` economiza processamento em memória e chamadas de métodos PHP, mas **não resolve o problema N+1 no banco por si só**. O problema N+1 é um gargalo de I/O do banco de dados e deve ser resolvido na query de busca do repositório/controller usando *Eager Loading* (`ItemPedido::with(['product', 'size'])->get()`).
  - **Recursos Nativos do PHP 8+:** Em PHP 8.0+, a forma mais limpa e moderna de evitar exceções com registros nulos sem precisar de vários `if/else` é o uso do operador **Nullsafe** (`$product?->getName()`) associado ao operador de coalescência nula (`?? 'Valor Padrão'`).

---

## Questão 10 - Desenvolvimento Full-Stack (Laravel + Vue.js) & Questão 12 (Checkout e Busca por CEP)

**Descrição:** Unificação completa das Questões 10 e 12 em uma única aplicação Full-Stack E-Commerce profissional, seguindo princípios de arquitetura enxuta (*ponytail: zero unnecessary boilerplate or over-engineering*):

- **📁 `infra/`**: Arquivo [`docker-compose.yml`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/infra/docker-compose.yml) orquestrando o **Traefik v3** como Proxy Reverso único na **porta 880** (`http://localhost:880`), banco de dados **MySQL 8.0** (`rheserva_db`) e **Redis Alpine**.
- **📁 `backend/`**: Aplicação Laravel 11 (PHP 8.4) com rotas em [`api.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/routes/api.php):
  - **Catálogo de Produtos:** Controller REST [`ProdutoController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/ProdutoController.php) otimizado com **Redis Cache** (`produtos_all_list`). Leitura pública e operações de mutação (Criar/Editar/Deletar) protegidas por **Laravel Sanctum Auth**.
  - **Checkout de Pedidos:** Controller REST [`CheckoutController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/CheckoutController.php) processando envios públicos de checkout e salvando o pedido e endereço no banco de dados MySQL (`checkouts` table).
  - **Autenticação Admin:** Controller [`AuthController.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Http/Controllers/AuthController.php) para login de administradores (`admin@rheserva.com` / `password123`).
  - **Ferramenta CLI:** Comando Artisan [`CreateUserCommand.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/app/Console/Commands/CreateUserCommand.php) (`php artisan user:create`).
  - **Suíte de Testes:** Testes automatizados PHPUnit cobrindo rotas da API, validações e comandos CLI (`6 passed`).
- **📁 `frontend/`**: Aplicação Vue 3 (Composition API com `<script setup>`) em [`App.vue`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/App.vue):
  - **🛍️ Catálogo de Loja (Público):** Exibe produtos disponíveis com foto, descrição e preço, e botão para adicionar ao carrinho.
  - **🛒 Carrinho & Checkout (Público):** Exibe itens adicionados, controle de quantidades, subtotal e formulário de entrega com busca automática de CEP via **`cep-promise`**, validações reativas de tipo, higienização de apenas números no campo `Número`, destaque visual de erro (*overlay vermelho suave*) apenas em campos interagidos (*dirty/touched*) e botão bloqueado até que todos os campos estejam válidos.
  - **🔐 Painel Administrativo (Protegido por Login):** Modal de login para admins e formulário/tabela de gestão CRUD de produtos.
  - **Suíte de Testes:** Testes unitários Vitest em [`App.spec.js`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend/src/__tests__/App.spec.js).

---











## Questão 9 - O que você entende do log abaixo?
**Pergunta:** Analise o log de erro retornado pela aplicação Laravel (`FatalThrowableError: Call to a member function getImage() on null` em `Imagem.php:147`).

**Resposta do Candidato:**
> "O log informa que foi tentado acessar um item para consultar informações dele; dentro das informações, ele referenciava uma imagem, essa imagem não existia e isso desencadeou o erro. A solução seria criar um tratamento de erro que, caso a imagem de um item não exista, ele exiba uma imagem placeholder."

**Análise & Comparação com o Gabarito (Diagnóstico Prático Impecável):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Diagnóstico da Causa Raiz:** Identificou com precisão que a falha ocorreu pela consulta a um produto cujo registro de imagem associado não existia no banco (`null`).
  - **Proposta de Solução de Engenharia:** Identificou a solução ideal para produção: adicionar tratamento de erro/nulidade para retornar uma imagem padrão (*placeholder*) em vez de interromper a execução do sistema com erro 500.

- **Detalhamento Técnico do Log para Entrevistas:**
  - Citar a linha exata (`/admin/app/Models/Imagem.php` linha 147) e o evento disparado (`ProdutoController@info` ao formatar o JSON de resposta).
  - Mencionar o uso do operador *Nullsafe* do PHP 8 (`$this->relacao?->getImage() ?? 'placeholder.png'`) para implementar esse tratamento de forma limpa.

---


## Questão 8 - Engenharia de Software e Banco de Dados
**Pergunta:** Quando é interessante utilizar transactions?

**Resposta do Candidato:**
> "As transactions são interessantes quando vamos registrar grandes quantidades de alterações no banco de uma vez, sejam `INSERT`s, `UPDATE`s ou `DELETE`s. Também são interessantes quando nós vamos fazer processos complexos no banco que idealmente, caso algum processo dê problema, o ideal é tudo ser desfeito de uma vez e não salvar o que já foi concluído e ignorar o que não foi."

**Análise & Comparação com o Gabarito (Excelente Domínio da Atomicidade):**
- **Pontos Positivos / Acertos Impecáveis:**
  - **Princípio da Atomicidade (Tudo ou Nada):** Definiu perfeitamente o conceito fundamental de uma transação: se qualquer operação falhar, tudo é desfeito de uma vez para evitar corrupção ou dados parciais.
  - **Operações Múltiplas e Complexas:** Identificou o uso correto em agrupamentos de `INSERT`, `UPDATE` e `DELETE` interdependentes.

- **Terminologia Técnica & Exemplos para Entrevistas:**
  - **Termos de Banco:** Citar as instruções **`Commit`** (confirmar tudo se der certo) e **`Rollback`** (reverter tudo se der erro).
  - **Conceito ACID:** Citar a propriedade da **Atomicidade** do acrônimo ACID.
  - **Exemplo Prático de Negócio:**
    - *Financeiro:* Debitar saldo da conta A e creditar na conta B (se o crédito falhar, rollback no débito).
    - *E-Commerce:* Salvar o `Pedido`, salvar os `Itens do Pedido` e decrementar o `Estoque` (se o estoque falhar, rollback no pedido).
  - **Sintaxe no Laravel:** `DB::transaction(function () { ... });`

---


## Questão 13 - Descreva uma situação em que você teve que lidar com um problema técnico complexo ou um imprevisto em um projeto de desenvolvimento (Método STAR: Situação, Tarefa, Ação, Resultado)

**Resposta do Candidato (Relato de Experiência Real):**

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

**Análise Crítica & Avaliação Técnica (Nota 10 / 10 - Excelente Metodologia de Investigação e Solução de Causa Raiz):**

- **Pontos Positivos / Acertos Impecáveis:**
  - **Metodologia Científica de Debugging:** O candidato seguiu um processo lógico exemplar de isolamento de hipóteses:
    1. *Filtro visual/comportamental:* isolou que o problema ocorria apenas nas mensagens de saída do sistema (*outbound*).
    2. *Validação interna vs. externa:* percebeu a inconsistência entre os logs internos da aplicação (que registravam falso sucesso) e os logs do gateway da API do provedor (que registravam rejeição por regra de negócio).
    3. *Eliminação da causa sintomática:* não se limitou a silenciar o erro ou reenviar a mensagem, mas buscou a razão pela qual a plataforma rejeitou o envio.
  - **Identificação da Falha de Arquitetura (Suposição vs. Estado Real):** Identificou com precisão um anti-pattern comum em integrações de mensageria (como WhatsApp Business API): tentar calcular o estado de uma janela de 24h via timers locais em vez de se basear no estado real informado pela API via Webhooks (`statuses` e `events`).
  - **Solução Arquitetural Definitiva:** Desenvolveu a captura reativa dos status de retorno do gateway, tornando o sistema orientado a eventos (*event-driven*) e tolerante a falhas.
  - **Estruturação STAR Impecável:** Cobriu todos os 4 pilares do método STAR com clareza, objetividade e detalhes técnicos do mundo real.

- **Conceitos de Engenharia Relevantes para Entrevistas (Diferenciação Sênior):**
  - **Event-Driven Architecture & Webhooks:** Em integrações com provedores de mensageria (ex: Meta/WhatsApp, Twilio, Z-API), o modelo ideal de consistência é o uso de Webhooks assíncronos que atualizam o estado da conversa no banco no momento em que a plataforma notifica o evento (`delivered`, `read`, `failed`, `session_expired`).
  - **Idempotência & Resiliência:** Ao registrar retornos de gateway, garante-se a idempotência do envio (evita disparar novamente caso a janela já tenha expirado ou falhado).

---


## Questão 14 - Consultas SQL & Eloquent (Clientes e Pedidos)

**Perguntas:**
- **Tabelas:** `clientes` (`id`, `nome`, `email`, `estado`) e `pedidos` (`id`, `cliente_id`, `data_pedido`, `valor_total`)
- **14.1:** Listar os estados com o maior volume de vendas.
- **14.2:** Listar os 5 clientes que mais compraram.
- **14.3:** Otimizações de performance para tabelas com milhões de registros.

**Resposta do Candidato:**
> **14.1:** "fazer um group-by por estado, um count para as ocorrencias por estado e order by quantidades de ocorrencias"  
> **14.2:** "fazer um group-by por cliente, um sum de todos os valores totais e order by valor total agregado"  
> **14.3:** "primeiro, implementar um index para data do pedido, já que em muitas situações de uso real se ordena os pedidos por intervalo de data, seja auditoria ou simplesmente facilitar fechamento do mes, depois, como parece que estado é algo relevante para o sistema levando em conta as perguntas anteriores, eu também criaria um index para essa coluna, implementaria também o with para o cliente relacionado ao pedido dentro do eloquent no laravel. Além disso, usaria o cache de dados do banco com algo como Redis ou Memcached para não onerar o banco com consultas repetitivas de agregação."

**Análise Crítica & Comparação com o Gabarito (Nota 9.5 / 10 - Domínio Completo de Indexação, Cache e ORM):**

- **Pontos Positivos / Acertos Impecáveis:**
  - **14.2 — Lógica Completa de Agregação de Vendas:** Identificou perfeitamente os 3 passos necessários: agrupar por cliente (`GROUP BY cliente_id`), calcular a soma dos valores (`SUM(valor_total)`) e ordenar pelo total agregado de forma decrescente (`ORDER BY valor_total DESC`).
  - **14.3 — Caching de Consultas com Redis / Memcached:** Excelente adição! Identificou a principal boa prática de arquitetura para tabelas de alto volume: armazenar em memória (Redis/Memcached) o resultado de relatórios e agregações pesadas para evitar ler o banco de dados SQL a cada requisição.
  - **14.3 — Visão Prática do Uso Real de Índices:** Demonstrou ótima vivência em sistemas reais ao sugerir indexar a data do pedido (`pedidos(data_pedido)`), prevendo que relatórios financeiros, fechamento de mês e auditorias filtram e ordenam por faixas temporais.
  - **14.3 — Indexação da Coluna de Agrupamento:** Acertou ao identificar que indexar a coluna `clientes(estado)` agiliza as consultas de agregação por localização geográfica.
  - **14.3 — Prevenção do Gargalo N+1 no Laravel:** Identificou o uso da cláusula `with('cliente')` (*Eager Loading*) no Eloquent para carregar os dados do relacionamento sem incorrer em M+1 queries SQL.

- **Pontos de Ajuste Técnico para Entrevistas (Análise Fria):**
  - **14.1 — Métricas de Volume (COUNT vs. SUM):** O candidato utilizou a função de contagem (`COUNT`) para mensurar ocorrências por estado. Em relatórios de e-commerce, o "volume de vendas" pode se referir à quantidade de pedidos (`COUNT(p.id)`) ou ao valor monetário total (`SUM(p.valor_total)`). Para somar o valor financeiro acumulado, a instrução exata é `SUM(valor_total)`.
  - **14.2 — Limitação dos Top 5:** Faltou citar a cláusula `LIMIT 5` no SQL puro ou o método `->limit(5)` / `->take(5)` no Eloquent para restringir o resultado aos 5 primeiros registros.
  - **14.3 — Índices em Chaves Estrangeiras (FK):** Além de indexar a data e o estado, em bancos relacionais a otimização primária para joins é criar um índice na chave estrangeira `pedidos(cliente_id)` ou um índice composto `pedidos(cliente_id, valor_total)`.

**Gabarito Técnico de Referência:**

```sql
-- 14.1 (SQL Puro)
SELECT c.estado, SUM(p.valor_total) AS total_vendas
FROM clientes c INNER JOIN pedidos p ON c.id = p.cliente_id
GROUP BY c.estado ORDER BY total_vendas DESC;

-- 14.2 (SQL Puro)
SELECT c.id, c.nome, c.email, SUM(p.valor_total) AS total_compras
FROM clientes c INNER JOIN pedidos p ON c.id = p.cliente_id
GROUP BY c.id, c.nome, c.email ORDER BY total_compras DESC LIMIT 5;
```

---












