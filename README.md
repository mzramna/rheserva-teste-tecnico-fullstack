# Teste Técnico Full-Stack RHeserva (2026)

Este repositório contém as 15 questões do teste técnico. As questões dissertativas (1-9, 11, 13, 14, 15) estão em [`minhas_respostas.md`](minhas_respostas.md) e [`queries_sql.sql`](queries_sql.sql) (SQL das questões 14 e 15). As questões práticas (10 e 12) estão em código, organizadas em **3 projetos separados** conforme descrito abaixo.

---

## Estrutura dos projetos práticos

Cada exercício prático está identificado em sua própria pasta e sobe isoladamente, sem depender dos outros:

| Projeto | Pasta | O que é | Porta |
|---|---|---|---|
| **Questão 10** (isolada) | `10_backend/`, `10_frontend/`, `10_infra/` | CRUD de produtos (Laravel + Vue), exatamente como pedido no enunciado | `http://localhost:881` |
| **Questão 12** (isolada) | `12_frontend/`, `12_infra/` | Página de checkout 100% front-end, carrinho hardcoded, sem backend | `http://localhost:882` |
| **Unificado (10+12)** | `backend/`, `frontend/`, `infra/` | Loja completa combinando as duas questões num só sistema (catálogo dinâmico via API, carrinho real, painel admin, autenticação por cookie de sessão) | `http://localhost:880` |

O projeto unificado existe como um extra além do que foi pedido — mostra as duas funcionalidades integradas num fluxo de e-commerce real, mas não usa dados hardcoded em lugar nenhum (todo o catálogo/carrinho vem da API). Os projetos `10_*`/`12_*` seguem o enunciado ao pé da letra (inclusive o carrinho hardcoded na Q12).

---

## Como subir cada projeto

Todos usam Docker Compose + Traefik como proxy reverso. Pode rodar mais de um ao mesmo tempo (portas diferentes).

### Questão 10 (CRUD de produtos)
```bash
cd 10_infra
docker compose up -d --build
docker exec rheserva_q10_backend php artisan migrate --force
```
- Loja: `http://localhost:881`
- Admin: `admin@rheserva.com` / `password123` (criado via seeder ou `php artisan user:create`)

### Questão 12 (Checkout)
```bash
cd 12_infra
docker compose up -d --build
```
- Página de checkout: `http://localhost:882` (sem backend, tudo client-side)

### Projeto unificado (10 + 12)
```bash
cd infra
docker compose up -d --build
docker exec rheserva_backend php artisan migrate:fresh --seed --force
```
- Loja + Checkout + Admin: `http://localhost:880`
- Dashboard Traefik: `http://localhost:8881`
- Admin: `admin@rheserva.com` / `password123`
- Comando CLI: `docker exec rheserva_backend php artisan user:create {email} {name} {password}`

---

## Testes automatizados

```bash
# Backend (PHPUnit) — roda em SQLite em memória, não afeta o MySQL
docker exec rheserva_backend vendor/bin/phpunit

# Frontend (Vitest)
cd frontend && npx vitest run
```
O mesmo padrão vale para os containers `rheserva_q10_backend`/`10_frontend` e `12_frontend`.

---

## Decisões técnicas e por quê

- **Autenticação por cookie de sessão (Sanctum SPA)**, não token Bearer em localStorage: evita expor credenciais de acesso a JavaScript/XSS. Como o Traefik expõe front e back sob o mesmo host/porta, não há CORS cross-site a resolver — é o cenário ideal para esse modo do Sanctum.
- **Cache com Redis** no catálogo de produtos (`Cache::remember`), invalidado a cada mutação (`Cache::forget`), para reduzir carga no MySQL em leituras repetidas.
- **Validação de telefone via `libphonenumber-js` (front) e `propaganistas/laravel-phone` (back)**: a regra brasileira do "nono dígito" hoje é nacional e uniforme (celular = 9 dígitos, fixo = 8), então usei uma lib mantida com a metadata correta em vez de regex hardcoded, que fica desatualizada com o tempo.
- **LESS em vez de CSS puro** no frontend unificado, conforme pedido no enunciado (personalização de estilos).
- **CVC nunca é persistido no banco** (só validado) — prática padrão de segurança de dados de cartão (PCI-DSS).
- **Carrinho hardcoded só na Questão 12 isolada**, dinâmico no projeto unificado — a Q12 pede explicitamente dados fixos; o projeto unificado é uma extensão que faz mais sentido com dados reais.
