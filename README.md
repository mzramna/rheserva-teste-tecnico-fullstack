# 🚀 Teste Técnico Full-Stack RHeserva (2026)

Este repositório contém as soluções práticas completas das Questões Práticas do Teste Técnico Full-Stack.

---

## 📁 Estrutura de Diretórios do Repositório

### 📌 Questão 10 - CRUD de Produtos (Laravel + Vue 3 + Redis + Traefik)
- 📁 [`10_infra/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/10_infra): Traefik v3 (Porta `880`), MySQL 8.0, Redis Alpine.
  - Contêineres: `rheserva_q10_traefik`, `rheserva_q10_backend`, `rheserva_q10_frontend`, `rheserva_q10_mysql`, `rheserva_q10_redis`.
- 📁 [`10_backend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/10_backend): Laravel 11 API (PHP 8.4) + Sanctum Auth + Redis Cache + CLI Command (`php artisan user:create`).
- 📁 [`10_frontend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/10_frontend): Vue 3 (Composition API `<script setup>`) + Vite.

### 📌 Questão 12 - Checkout de Endereço (Vue 3 + `cep-promise` + MySQL + Validação + Loading)
- 📁 [`12_infra/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/12_infra): Traefik v3 (Porta `882`), Backend API, Frontend Vue 3 e MySQL 8.0.
  - Contêineres: `rheserva_q12_traefik`, `rheserva_q12_backend`, `rheserva_q12_frontend`, `rheserva_q12_mysql`.
- 📁 [`12_backend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/12_backend): Endpoint REST Laravel 11 `POST /api/checkout` com persistência no MySQL (`checkouts` table) e Eloquent model [`Checkout.php`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/12_backend/app/Models/Checkout.php).
- 📁 [`12_frontend/`](file:///home/mzramna/Documentos/teste_fullstack_rheserva_04_08_26/12_frontend): Formulário de Checkout Vue 3 com busca automática por CEP (`cep-promise`), validações reativas, estado de campos interagidos (*dirty/touched*), spinners de carregamento e confirmação com ID de pedido.

---

## 🐳 Como Subir a Questão 10 (CRUD de Produtos)

```bash
cd 10_infra
docker compose up -d --build
docker exec rheserva_q10_backend php artisan migrate:fresh --seed --force
```
- **Acesso ao Sistema (Traefik):** `http://localhost:880`
- **Credenciais de Teste:** `admin@rheserva.com` / `password123`
- **Comando CLI de Usuário:** `docker exec rheserva_q10_backend php artisan user:create`

---

## 🐳 Como Subir a Questão 12 (Checkout com `cep-promise` & MySQL)

```bash
cd 12_infra
docker compose up -d --build
docker exec rheserva_q12_backend php artisan migrate:fresh --force
```
- **Acesso ao Sistema de Checkout (Traefik):** `http://localhost:882`
- **Dashboard do Traefik Q12:** `http://localhost:8882`

---

## 🧪 Execução de Testes Automatizados

### Questão 10:
- **Backend:** `docker exec rheserva_q10_backend php artisan test`
- **Frontend:** `docker exec rheserva_q10_frontend npx vitest run`

### Questão 12:
- **Backend Feature Tests (MySQL Persistence):** `docker exec rheserva_q12_backend vendor/bin/phpunit`
- **Frontend Checkout Unit Tests:** `docker exec rheserva_q12_frontend npx vitest run`

---
