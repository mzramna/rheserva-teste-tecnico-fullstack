# Nota Técnica: Reutilização de Rotas no Angular (`RouteReuseStrategy`)

**Objetivo desta nota:** Servir de guia para auditoria no codebase Angular e avaliar se a aplicação de chat/operador utiliza ou se beneficiará da implementação de `RouteReuseStrategy` para preservar o estado de abas/telas de atendimento na memória RAM durante a navegação.

---

## 1. O Problema Identificado no Front-end (Angular)

Na tela de atendimento do operador (ou em interfaces baseadas em múltiplas abas/contatos):
- Quando o operador alterna entre a conversa do **Contato A** e a do **Contato B**, o Angular Router por padrão **destrói o componente da tela anterior** (`ngOnDestroy`) e instancia a nova tela do zero (`ngOnInit`).
- **Impacto negativo:**
  - Perda do estado local da conversa (rascunhos de mensagens digitadas não enviadas, posição da barra de rolagem do chat, formulários preenchidos parcialmente).
  - Overhead de processamento ao re-instanciar o DOM e re-fazer requisições HTTP a cada troca de aba.

---

## 2. A Solução: `RouteReuseStrategy`

O Angular possui a API nativa `RouteReuseStrategy` que permite manter uma rota e sua árvore de visão (DOM + estado da classe) salvas em memória RAM (similar ao `<KeepAlive>` do Vue.js).

### Funcionamento do Fluxo de Cache:
1. **`shouldDetach`**: O Router pergunta se a rota atual deve ser destacada para cache ao navegar para longe dela.
2. **`store`**: Salva o `DetachedRouteHandle` em um repositório de memória (ex: um mapa de rotas ativas).
3. **`shouldAttach`**: Ao navegar para uma nova rota, verifica se o manipulador salvo já existe em memória.
4. **`retrieve`**: Recupera a rota da memória e a re-anexa ao DOM instantaneamente sem destruir nem reconstruir o componente.

---

## 3. Diretrizes para Auditoria no Codebase Real

Ao enviar esta nota para um agente analisar o repositório do projeto, as seguintes perguntas devem ser investigadas:

1. **Já existe implementação de `RouteReuseStrategy` no projeto?**
   - Buscar por `RouteReuseStrategy` no repositório.
   - Verificar se existe alguma classe customizada registrada nos `providers` do `app.config.ts` ou `app.module.ts`:
     ```typescript
     { provide: RouteReuseStrategy, useClass: CustomRouteReuseStrategy }
     ```

2. **Se já existe:**
   - Como é feito o expurgo de memória (garbage collection)? Existe limite máximo de rotas em cache (`maxEntries` / `LRU Cache`) para evitar estouro de memória RAM nos operadores?

3. **Se NÃO existe:**
   - Avaliar a viabilidade de aplicar o `RouteReuseStrategy` exclusivamente para as rotas de atendimento/chat (identificadas via `data: { reuse: true }` nas configurações de rotas).

---

## 4. Exemplo de Código para Implementação / Referência

```typescript
import { RouteReuseStrategy, DetachedRouteHandle, ActivatedRouteSnapshot } from '@angular/router';

export class CustomRouteReuseStrategy implements RouteReuseStrategy {
  private handlers: Map<string, DetachedRouteHandle> = new Map();
  private maxCachedRoutes = 5; // Limite de telas mantidas na memória

  shouldDetach(route: ActivatedRouteSnapshot): boolean {
    return route.data['reuse'] === true;
  }

  store(route: ActivatedRouteSnapshot, handle: DetachedRouteHandle | null): void {
    if (!handle) return;
    
    const key = this.getRouteKey(route);
    
    // Gerenciamento de Memória (FIFO / LRU Cache): remove a rota mais antiga se atingir o limite
    if (this.handlers.size >= this.maxCachedRoutes && !this.handlers.has(key)) {
      const firstKey = this.handlers.keys().next().value;
      if (firstKey) {
        this.handlers.delete(firstKey);
      }
    }

    this.handlers.set(key, handle);
  }

  shouldAttach(route: ActivatedRouteSnapshot): boolean {
    return this.handlers.has(this.getRouteKey(route));
  }

  retrieve(route: ActivatedRouteSnapshot): DetachedRouteHandle | null {
    return this.handlers.get(this.getRouteKey(route)) || null;
  }

  shouldReuseRoute(future: ActivatedRouteSnapshot, curr: ActivatedRouteSnapshot): boolean {
    return future.routeConfig === curr.routeConfig;
  }

  private getRouteKey(route: ActivatedRouteSnapshot): string {
    return route.pathFromRoot
      .map(it => it.url.map(segment => segment.path).join('/'))
      .filter(it => it.length > 0)
      .join('/');
  }
}
```
