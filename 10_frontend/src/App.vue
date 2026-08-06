<template>
  <div class="app-container">
    <!-- Header Alinhado com o Sistema de Design Unificado -->
    <header class="navbar">
      <div class="nav-brand">
        <span class="logo-icon">🛍️</span>
        <h2>RHeserva E-Commerce <span class="badge">Questão 10 - CRUD Produtos</span></h2>
      </div>

      <!-- Painel de Autenticação Admin ( Sanctum Token ) -->
      <div class="auth-box">
        <template v-if="token">
          <span class="user-status">Conectado como <strong>{{ user?.email || 'Admin' }}</strong></span>
          <button class="btn btn-danger" @click="logout">Sair</button>
        </template>
        <template v-else>
          <form @submit.prevent="login" class="login-inline">
            <input v-model="loginForm.email" type="email" placeholder="E-mail admin" required />
            <input v-model="loginForm.password" type="password" placeholder="Senha" required />
            <button class="btn btn-primary" type="submit">Entrar Admin</button>
          </form>
        </template>
      </div>
    </header>

    <div class="main-content">
      <div class="page-header">
        <h2>Catálogo de Produtos & Gestão CRUD</h2>
        <p>Produtos carregados diretamente do banco via <code>DatabaseSeeder</code> do Laravel Backend</p>
      </div>

      <!-- Formulário de Criação/Edição de Produtos (Visível apenas se Autenticado) -->
      <section v-if="token" class="card admin-card">
        <div class="card-header">
          <h3>{{ editId ? '✏️ Editar Produto' : '➕ Cadastrar Novo Produto' }}</h3>
        </div>
        <form @submit.prevent="salvarProduto" class="form-grid">
          <div class="form-group">
            <label>Nome do Produto *</label>
            <input v-model="form.nome" type="text" placeholder="Ex: Tênis Esportivo" required />
          </div>

          <div class="form-group">
            <label>Preço (R$) *</label>
            <input v-model="form.preco" type="number" step="0.01" min="0" placeholder="199.90" required />
          </div>

          <div class="form-group full-width">
            <label>Descrição *</label>
            <textarea v-model="form.descricao" rows="3" placeholder="Detalhes e especificações do produto..." required></textarea>
          </div>

          <div class="form-group full-width">
            <label>Imagem do Produto (Upload File)</label>
            <input type="file" @change="handleFileChange" accept="image/*" class="file-input" />
          </div>

          <div class="full-width btn-group">
            <button type="submit" class="btn btn-primary">{{ editId ? 'Atualizar Produto' : 'Cadastrar Produto' }}</button>
            <button v-if="editId" type="button" class="btn btn-secondary" @click="limparForm">Cancelar Edição</button>
          </div>
        </form>
      </section>

      <div v-else class="card alert-info">
        💡 <em>Apenas administradores autenticados podem cadastrar, editar ou excluir produtos. Faça login acima (<code>admin@rheserva.com</code> / <code>password123</code>).</em>
      </div>

      <!-- Lista de Produtos (Carregados do Backend Laravel Seed) -->
      <main class="catalog-section">
        <div class="section-title">
          <h3>Catálogo Público de Produtos <span class="badge-seed">Laravel Seed Data</span></h3>
        </div>

        <div v-if="loading" class="loading-box">
          <div class="spinner"></div>
          <p>Carregando produtos do seed no Laravel MySQL...</p>
        </div>

        <div v-else-if="produtos.length === 0" class="empty-box card">
          <p>Nenhum produto encontrado no banco de dados.</p>
        </div>

        <div v-else class="grid">
          <div v-for="item in produtos" :key="item.id" class="card product-card">
            <div class="img-wrapper">
              <img :src="item.imagem || 'https://via.placeholder.com/300x180?text=Sem+Imagem'" :alt="item.nome" class="product-img" />
            </div>
            <div class="product-body">
              <h4>{{ item.nome }}</h4>
              <p class="product-desc">{{ item.descricao }}</p>
              <div class="product-price">R$ {{ Number(item.preco).toFixed(2).replace('.', ',') }}</div>

              <div v-if="token" class="actions">
                <button class="btn btn-sm btn-primary" @click="prepararEdicao(item)">Editar</button>
                <button class="btn btn-sm btn-danger" @click="deletarProduto(item.id)">Excluir</button>
              </div>
            </div>
          </div>
        </div>
      </main>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

const API_URL = '/api'

const produtos = ref([])
const loading = ref(false)
const token = ref(localStorage.getItem('token') || '')
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))

const loginForm = reactive({ email: '', password: '' })
const editId = ref(null)
const selectedFile = ref(null)

const form = reactive({
  nome: '',
  descricao: '',
  preco: ''
})

const getHeaders = () => ({
  headers: {
    Authorization: token.value ? `Bearer ${token.value}` : '',
    'Content-Type': 'multipart/form-data'
  }
})

const carregarProdutos = async () => {
  loading.value = true
  try {
    const { data } = await axios.get(`${API_URL}/produtos`)
    produtos.value = data
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
  } finally {
    loading.value = false
  }
}

const login = async () => {
  try {
    const { data } = await axios.post(`${API_URL}/login`, loginForm)
    token.value = data.token
    user.value = data.user
    localStorage.setItem('token', data.token)
    localStorage.setItem('user', JSON.stringify(data.user))
    loginForm.email = ''
    loginForm.password = ''
  } catch (err) {
    alert('Credenciais inválidas.')
  }
}

const logout = async () => {
  try {
    await axios.post(`${API_URL}/logout`, {}, getHeaders())
  } catch (e) {}
  token.value = ''
  user.value = null
  localStorage.removeItem('token')
  localStorage.removeItem('user')
  limparForm()
}

const handleFileChange = (e) => {
  selectedFile.value = e.target.files[0] || null
}

const salvarProduto = async () => {
  const formData = new FormData()
  formData.append('nome', form.nome)
  formData.append('descricao', form.descricao)
  formData.append('preco', form.preco)
  if (selectedFile.value) {
    formData.append('imagem', selectedFile.value)
  }

  try {
    if (editId.value) {
      formData.append('_method', 'PUT')
      await axios.post(`${API_URL}/produtos/${editId.value}`, formData, getHeaders())
    } else {
      await axios.post(`${API_URL}/produtos`, formData, getHeaders())
    }
    limparForm()
    carregarProdutos()
  } catch (err) {
    alert('Erro ao salvar produto.')
  }
}

const prepararEdicao = (item) => {
  editId.value = item.id
  form.nome = item.nome
  form.descricao = item.descricao
  form.preco = item.preco
}

const deletarProduto = async (id) => {
  if (!confirm('Deseja realmente excluir este produto?')) return
  try {
    await axios.delete(`${API_URL}/produtos/${id}`, getHeaders())
    carregarProdutos()
  } catch (err) {
    alert('Erro ao deletar produto.')
  }
}

const limparForm = () => {
  editId.value = null
  form.nome = ''
  form.descricao = ''
  form.preco = ''
  selectedFile.value = null
}

onMounted(() => {
  carregarProdutos()
})
</script>

<style scoped>
.app-container {
  min-height: 100vh;
  background-color: #0f172a;
  color: #f8fafc;
  font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

.navbar {
  background: rgba(30, 41, 59, 0.8);
  backdrop-filter: blur(12px);
  border-bottom: 1px solid rgba(255, 255, 255, 0.08);
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  flex-wrap: wrap;
  gap: 1rem;
}

.nav-brand {
  display: flex;
  align-items: center;
  gap: 0.75rem;
}

.nav-brand h2 {
  font-size: 1.25rem;
  font-weight: 700;
  margin: 0;
}

.badge {
  background: rgba(129, 140, 248, 0.15);
  color: #818cf8;
  font-size: 0.75rem;
  padding: 0.2rem 0.5rem;
  border-radius: 6px;
  border: 1px solid rgba(129, 140, 248, 0.3);
}

.badge-seed {
  background: rgba(110, 231, 183, 0.15);
  color: #6ee7b7;
  font-size: 0.7rem;
  padding: 0.15rem 0.4rem;
  border-radius: 4px;
  margin-left: 0.5rem;
}

.auth-box {
  display: flex;
  gap: 10px;
  align-items: center;
}

.user-status {
  font-size: 0.85rem;
  color: #cbd5e1;
}

.login-inline {
  display: flex;
  gap: 8px;
  align-items: center;
}

.login-inline input {
  padding: 0.45rem 0.75rem;
  font-size: 0.85rem;
  width: 150px;
}

.main-content {
  max-width: 1200px;
  margin: 0 auto;
  padding: 2rem;
}

.page-header {
  margin-bottom: 2rem;
}

.page-header h2 {
  font-size: 1.75rem;
  font-weight: 700;
  margin-bottom: 0.5rem;
}

.page-header p {
  color: #94a3b8;
}

.card {
  background: #1e293b;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 1.75rem;
  margin-bottom: 2rem;
}

.card-header h3 {
  margin: 0 0 1rem 0;
  font-size: 1.1rem;
}

.form-grid {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 1.25rem;
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.4rem;
}

.full-width {
  grid-column: span 2;
}

label {
  font-size: 0.85rem;
  font-weight: 600;
  color: #cbd5e1;
}

input, textarea, .file-input {
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #f8fafc;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

input:focus, textarea:focus {
  outline: none;
  border-color: #818cf8;
  box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
}

.btn-group {
  display: flex;
  gap: 0.75rem;
  margin-top: 0.5rem;
}

.btn {
  padding: 0.6rem 1.2rem;
  border-radius: 8px;
  font-weight: 600;
  font-size: 0.9rem;
  cursor: pointer;
  border: none;
  transition: all 0.2s ease;
}

.btn-primary {
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
}

.btn-primary:hover {
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.btn-secondary {
  background: #334155;
  color: #cbd5e1;
}

.btn-danger {
  background: #ef4444;
  color: #ffffff;
}

.btn-sm {
  padding: 0.4rem 0.8rem;
  font-size: 0.8rem;
}

.alert-info {
  background: rgba(129, 140, 248, 0.1);
  border: 1px solid rgba(129, 140, 248, 0.2);
  color: #cbd5e1;
}

.section-title {
  margin-bottom: 1.5rem;
}

.grid {
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(260px, 1fr));
  gap: 1.5rem;
}

.product-card {
  padding: 0;
  overflow: hidden;
  display: flex;
  flex-direction: column;
}

.img-wrapper {
  background: #0f172a;
  height: 180px;
  overflow: hidden;
}

.product-img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  transition: transform 0.3s ease;
}

.product-card:hover .product-img {
  transform: scale(1.05);
}

.product-body {
  padding: 1.25rem;
  display: flex;
  flex-direction: column;
  flex: 1;
}

.product-body h4 {
  margin: 0 0 0.5rem 0;
  font-size: 1.05rem;
}

.product-desc {
  color: #94a3b8;
  font-size: 0.85rem;
  flex: 1;
  margin-bottom: 1rem;
  line-height: 1.4;
}

.product-price {
  font-size: 1.25rem;
  font-weight: 700;
  color: #6ee7b7;
  margin-bottom: 1rem;
}

.actions {
  display: flex;
  gap: 0.5rem;
}

.loading-box {
  text-align: center;
  padding: 3rem;
}

.spinner {
  width: 28px;
  height: 28px;
  border: 3px solid rgba(255, 255, 255, 0.2);
  border-top-color: #818cf8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
  margin: 0 auto 1rem auto;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
