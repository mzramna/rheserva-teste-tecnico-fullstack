# 🚀 Teste Técnico Full-Stack RHeserva (2026)

Aplicação Full-Stack E-Commerce profissional desenvolvida em **Laravel 11 (PHP 8.4)** e **Vue 3 (Composition API)**, conteinerizada via **Docker Compose** e orquestrada pelo **Traefik v3 Proxy Reverso**.

---

## 📁 Estrutura Unificada de Diretórios do Projeto

- 📁 [`infra/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/infra): Orquestração Docker Compose com **Traefik v3** (porta `880`), **MySQL 8.0** e **Redis Alpine**.
- 📁 [`backend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend): API REST Laravel 11:
  - **Catálogo & Redis Cache:** GET `/api/produtos` público, com mutações (POST/PUT/DELETE) protegidas por **Sanctum Auth**.
  - **Checkout de Pedidos:** POST `/api/checkout` público registrando compras e endereços no MySQL (`checkouts` table).
  - **Autenticação Admin:** POST `/api/login` (`admin@rheserva.com` / `password123`).
  - **Comando CLI Artisan:** `php artisan user:create {email} {name} {password}`.
- 📁 [`frontend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend): Aplicação Vue 3 + Vite:
  - **🛍️ Catálogo da Loja:** Visualização pública de produtos com adição ao carrinho em tempo real.
  - **🛒 Carrinho & Checkout (Questão 12):** Gestão de quantidade, busca automática de CEP via `cep-promise`, validações reativas, higienização numérica e aviso visual de erro apenas para campos interagidos (*dirty/touched*).
  - **🔐 Gestão de Produtos Admin (Questão 10):** Autenticação e formulário/tabela de gestão CRUD de produtos.

---

## 🐳 Como Subir a Aplicação Completa (Porta 880)

```bash
cd infra
docker compose up -d --build
docker exec rheserva_backend php artisan migrate:fresh --seed --force
```

- 🌐 **URL da Loja & Checkout (Traefik):** `http://localhost:880`
- 📊 **Dashboard do Traefik:** `http://localhost:8881`
- 🔑 **Credenciais do Admin:** `admin@rheserva.com` / `password123`
- 🛠️ **Comando CLI Artisan:** `docker exec rheserva_backend php artisan user:create`

---

## 🧪 Execução dos Testes Automatizados

### Backend (PHPUnit):
```bash
docker exec rheserva_backend vendor/bin/phpunit
```
> Executa 6 testes de integração cobrindo API de Produtos, Sanctum Auth, Validações de Checkout e Comando CLI.

### Frontend (Vitest):
```bash
docker exec rheserva_frontend npx vitest run
```
> Executa a suíte de testes de componentes Vue 3.

---
