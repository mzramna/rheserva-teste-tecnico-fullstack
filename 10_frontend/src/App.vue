<template>
  <div class="container">
    <header class="header">
      <h1>Gestão de Produtos (Fullstack CRUD)</h1>
      
      <!-- Painel de Autenticação Leve (ponytail: no complex auth routing) -->
      <div class="auth-box">
        <template v-if="token">
          <span>Conectado como <strong>{{ user?.email || 'Usuário' }}</strong></span>
          <button class="btn btn-danger" @click="logout">Sair</button>
        </template>
        <template v-else>
          <form @submit.prevent="login" class="login-inline">
            <input v-model="loginForm.email" type="email" placeholder="E-mail" required />
            <input v-model="loginForm.password" type="password" placeholder="Senha" required />
            <button class="btn" type="submit">Entrar</button>
          </form>
        </template>
      </div>
    </header>

    <!-- Formulário de Criação/Edição de Produtos (Visível apenas se Autenticado) -->
    <section v-if="token" class="card">
      <h2>{{ editId ? 'Editar Produto' : 'Novo Produto' }}</h2>
      <form @submit.prevent="salvarProduto">
        <div>
          <label>Nome</label>
          <input v-model="form.nome" type="text" required />
        </div>

        <div>
          <label>Descrição</label>
          <textarea v-model="form.descricao" required></textarea>
        </div>

        <div>
          <label>Preço (R$)</label>
          <input v-model="form.preco" type="number" step="0.01" min="0" required />
        </div>

        <div>
          <label>Imagem</label>
          <input type="file" @change="handleFileChange" accept="image/*" />
        </div>

        <div style="display: flex; gap: 10px; margin-top: 10px;">
          <button type="submit" class="btn">{{ editId ? 'Atualizar' : 'Cadastrar' }}</button>
          <button v-if="editId" type="button" class="btn btn-danger" @click="limparForm">Cancelar</button>
        </div>
      </form>
    </section>
    
    <div v-else class="card alert-info">
      💡 <em>Apenas usuários autenticados podem cadastrar, editar ou excluir produtos.</em>
    </div>

    <!-- Lista de Produtos (Pública para Leitura) -->
    <main>
      <h2>Lista de Produtos</h2>
      <div v-if="loading">Carregando produtos...</div>
      
      <div v-else class="grid">
        <div v-for="item in produtos" :key="item.id" class="card product-card">
          <img :src="item.imagem || 'https://via.placeholder.com/300x180?text=Sem+Imagem'" :alt="item.nome" class="product-img" />
          <h3>{{ item.nome }}</h3>
          <p>{{ item.descricao }}</p>
          <strong class="price">R$ {{ Number(item.preco).toFixed(2) }}</strong>

          <div v-if="token" class="actions">
            <button class="btn" @click="prepararEdicao(item)">Editar</button>
            <button class="btn btn-danger" @click="deletarProduto(item.id)">Excluir</button>
          </div>
        </div>
      </div>
    </main>
  </div>
</template>

<script setup>
// ponytail: Direct composition setup with minimum dependencies
import { ref, reactive, onMounted } from 'vue'
import axios from 'axios'

// Traefik Reverse Proxy: backend e frontend compartilham o mesmo domínio e porta (/api)
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
.header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 2rem;
}

.auth-box {
  display: flex;
  gap: 10px;
  align-items: center;
}

.login-inline {
  display: flex;
  gap: 8px;
}

.login-inline input {
  margin: 0;
}

.product-img {
  width: 100%;
  height: 160px;
  object-fit: cover;
  border-radius: 8px;
}

.price {
  font-size: 1.25rem;
  color: #16a34a;
  display: block;
  margin: 0.5rem 0;
}

.actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

.alert-info {
  background-color: #e0f2fe;
  color: #0369a1;
  border: 1px solid #bae6fd;
}
</style>
