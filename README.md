# 🚀 Teste Técnico Full-Stack RHeserva (2026)

Aplicação Full-Stack E-Commerce profissional desenvolvida em **Laravel 11 (PHP 8.4)** e **Vue 3 (Composition API)**, conteinerizada via **Docker Compose** e orquestrada pelo **Traefik v3 Proxy Reverso**.

---

## 📁 Estrutura Unificada de Diretórios do Projeto

- 📁 [`infra/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/infra): Orquestração Docker Compose com **Traefik v3** (porta `880`), **MySQL 8.0** e **Redis Alpine**.
- 📁 [`backend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend): API REST Laravel 11:
  - **Catálogo & Redis Cache:** GET `/api/produtos` público paginado, com mutações (POST/PUT/DELETE) protegidas por **Sanctum Auth**.
  - **Checkout de Pedidos:** POST `/api/checkout` público registrando compras e endereços no MySQL (`checkouts` table). GET `/api/checkouts` protegido para administradores.
  - **Autenticação Admin:** POST `/api/login` (`admin@rheserva.com` / `password123`).
  - **Comando CLI Artisan:** `php artisan user:create {email} {name} {password}`.
  - **Seeder de Dados Completo:** [`PopulaSistemaSeeder.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/database/seeders/PopulaSistemaSeeder.php) (Popula 30 produtos de e-commerce e 10 pedidos completos com imagens e endereços).
- 📁 [`frontend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/frontend): Aplicação Vue 3 + Vite:
  - **🛍️ Catálogo da Loja:** Visualização pública de produtos com paginação (6 por página) e modal de detalhes/compra.
  - **🛒 Carrinho & Checkout (Questão 12):** Gestão de quantidade, busca automática de CEP via `cep-promise`, validações reativas, higienização numérica e aviso visual de erro apenas para campos interagidos (*dirty/touched*).
  - **🔐 Gestão do Painel Admin (Questão 10):** Autenticação protegida com watcher reativo (modal obrigatório), formulário de criação com pré-visualização de imagem ao vivo, modal de edição/exclusão de produtos e visualização paginada com modal de detalhes de pedidos.

---

## 🌾 Seeder de Dados (População do Sistema)

O projeto possui um Seeder dedicado [`PopulaSistemaSeeder.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/backend/database/seeders/PopulaSistemaSeeder.php) para cadastrar automaticamente **30 produtos com fotos em alta definição** e **10 pedidos completos**:

```bash
# Executar o Seeder dedicado via Docker
docker exec rheserva_backend php artisan db:seed --class=PopulaSistemaSeeder
```

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

## 🧪 Execução dos Testes Automatizados (100% Isolados em SQLite in-memory)

### Backend (PHPUnit):
```bash
docker exec rheserva_backend vendor/bin/phpunit
```
> Executa 9 testes de integração em SQLite em memória sem afetar os dados do banco MySQL.

### Frontend (Vitest):
```bash
docker exec rheserva_frontend npx vitest run
```
> Executa a suíte de testes de componentes Vue 3.

---
