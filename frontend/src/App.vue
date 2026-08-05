<script setup>
// ponytail: Unified Vue 3 Full-Stack Application (Catalog, Shopping Cart, CEP Checkout, Admin Management with Image Previews)
import { ref, reactive, computed, watch, onMounted } from 'vue'
import cepPromise from 'cep-promise'
import axios from 'axios'

const API_URL = '/api'

// Navegação de Views: 'store' (Loja), 'cart' (Carrinho/Checkout), 'admin' (Gestão)
const currentView = ref('store')

// Estado do Usuário Autenticado (Admin)
const token = ref(localStorage.getItem('token') || '')
const user = ref(JSON.parse(localStorage.getItem('user') || 'null'))
const showLoginModal = ref(false)
const loginForm = reactive({ email: 'admin@rheserva.com', password: 'password123' })
const loginError = ref('')
const loginLoading = ref(false)

// Estado dos Produtos & Paginação
const produtos = ref([])
const loadingProdutos = ref(false)
const produtosPage = ref(1)
const produtosTotalPages = ref(1)
const produtosTotalItems = ref(0)

// Estado dos Pedidos & Paginação
const checkouts = ref([])
const loadingCheckouts = ref(false)
const checkoutsPage = ref(1)
const checkoutsTotalPages = ref(1)
const checkoutsTotalItems = ref(0)

// Estado dos Modais de Detalhes & Edição
const selectedProduto = ref(null)      // Loja Pública (Detalhes + Adicionar ao Carrinho)
const editingProduto = ref(null)       // Painel Admin (Edição / Exclusão de Produto)
const selectedCheckout = ref(null)     // Painel Admin (Detalhes do Pedido)

// Carrinho de Compras
const cart = ref([])

// Novo Produto (Admin)
const newProduto = reactive({ nome: '', descricao: '', preco: '', imagem: '' })
const submittingProduto = ref(false)
const updatingProduto = ref(false)

// Formulário de Checkout (Público)
const form = reactive({
  nome: '',
  email: '',
  cep: '',
  rua: '',
  bairro: '',
  cidade: '',
  estado: '',
  numero: '',
  complemento: '',
  cartao: '',
  data_validade: ''
})

const touched = reactive({
  nome: false,
  email: false,
  cep: false,
  rua: false,
  bairro: false,
  cidade: false,
  estado: false,
  numero: false,
  cartao: false,
  data_validade: false
})

const loadingCep = ref(false)
const submittingCheckout = ref(false)
const orderSuccess = ref(null)
const cepError = ref('')

// Watcher Garantidor: Se tentar acessar a view admin sem estar logado, O MODAL APARECE SEMPRE!
watch([currentView, user], ([newView, newUser]) => {
  if (newView === 'admin' && !newUser) {
    showLoginModal.value = true
  } else if (newView === 'admin' && newUser) {
    fetchCheckouts()
  }
}, { immediate: true })

// Computed Helpers
const cleanCep = computed(() => form.cep.replace(/\D/g, ''))
const cleanCartao = computed(() => form.cartao.replace(/\D/g, ''))
const totalCartItems = computed(() => cart.value.reduce((sum, item) => sum + item.qtd, 0))
const cartSubtotal = computed(() => cart.value.reduce((sum, item) => sum + (item.preco * item.qtd), 0))

// Validações de Formulário de Checkout
const isNomeValid = computed(() => form.nome.trim().length >= 3)
const isEmailValid = computed(() => /\S+@\S+\.\S+/.test(form.email.trim()))
const isCepValid = computed(() => /^\d{8}$/.test(cleanCep.value))
const isRuaValid = computed(() => form.rua.trim().length > 0)
const isBairroValid = computed(() => form.bairro.trim().length > 0)
const isCidadeValid = computed(() => form.cidade.trim().length > 0)
const isEstadoValid = computed(() => /^[A-Z]{2}$/i.test(form.estado.trim()))
const isNumeroValid = computed(() => /^\d+$/.test(form.numero.trim()))
const isCartaoValid = computed(() => /^\d{16}$/.test(cleanCartao.value))
const isDataValidadeValid = computed(() => /^(0[1-9]|1[0-2])\/\d{2}$/.test(form.data_validade.trim()))

const isFormValid = computed(() => {
  return isNomeValid.value &&
         isEmailValid.value &&
         isCepValid.value &&
         isRuaValid.value &&
         isBairroValid.value &&
         isCidadeValid.value &&
         isEstadoValid.value &&
         isNumeroValid.value &&
         isCartaoValid.value &&
         isDataValidadeValid.value &&
         cart.value.length > 0
})

const markTouched = (field) => {
  touched[field] = true
}

// Troca de Aba com Proteção para Painel Admin
const navigateTo = (viewName) => {
  if (viewName === 'admin' && !user.value) {
    loginError.value = ''
    showLoginModal.value = true
    return
  }
  currentView.value = viewName
}

// Fechamento do Modal sem Login -> Retorna para Produtos
const closeLoginModal = () => {
  showLoginModal.value = false
  if (!user.value) {
    currentView.value = 'store'
  }
}

// Carregar Produtos Paginados da API
const fetchProdutos = async (page = 1) => {
  loadingProdutos.value = true
  produtosPage.value = page
  try {
    const response = await axios.get(`${API_URL}/produtos`, {
      params: { page, per_page: 6 }
    })
    
    if (response.data.data) {
      produtos.value = response.data.data
      produtosTotalPages.value = response.data.last_page
      produtosTotalItems.value = response.data.total
    } else {
      produtos.value = response.data
    }
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
  } finally {
    loadingProdutos.value = false
  }
}

// Carregar Pedidos Paginados da API
const fetchCheckouts = async (page = 1) => {
  if (!user.value || !token.value) return
  loadingCheckouts.value = true
  checkoutsPage.value = page
  try {
    const response = await axios.get(`${API_URL}/checkouts`, {
      params: { page, per_page: 5 },
      headers: { Authorization: `Bearer ${token.value}` }
    })
    
    if (response.data.data) {
      checkouts.value = response.data.data
      checkoutsTotalPages.value = response.data.last_page
      checkoutsTotalItems.value = response.data.total
    } else {
      checkouts.value = response.data
    }
  } catch (err) {
    console.error('Erro ao carregar pedidos:', err)
  } finally {
    loadingCheckouts.value = false
  }
}

// Gerenciamento do Carrinho de Compras
const addToCart = (produto) => {
  const existing = cart.value.find(i => i.id === produto.id)
  if (existing) {
    existing.qtd++
  } else {
    cart.value.push({ ...produto, qtd: 1 })
  }
}

const updateQty = (index, delta) => {
  const item = cart.value[index]
  item.qtd += delta
  if (item.qtd <= 0) {
    cart.value.splice(index, 1)
  }
}

const removeFromCart = (index) => {
  cart.value.splice(index, 1)
}

// Busca Automática do CEP
const buscarCep = async () => {
  markTouched('cep')
  const cep = cleanCep.value
  if (!cep) return

  if (!isCepValid.value) {
    cepError.value = 'CEP deve possuir 8 dígitos.'
    return
  }

  cepError.value = ''
  loadingCep.value = true

  try {
    const dados = await cepPromise(cep)
    form.rua = dados.street || ''
    form.bairro = dados.neighborhood || ''
    form.cidade = dados.city || ''
    form.estado = (dados.state || '').toUpperCase()

    if (form.rua) markTouched('rua')
    if (form.bairro) markTouched('bairro')
    if (form.cidade) markTouched('cidade')
    if (form.estado) markTouched('estado')
  } catch (err) {
    cepError.value = 'CEP não encontrado.'
  } finally {
    loadingCep.value = false
  }
}

const onCepInput = () => {
  markTouched('cep')
  form.cep = form.cep.replace(/[^\d-]/g, '')
  if (cleanCep.value.length === 8) {
    buscarCep()
  }
}

const onNumeroInput = () => {
  markTouched('numero')
  form.numero = form.numero.replace(/\D/g, '')
}

const onCartaoInput = () => {
  markTouched('cartao')
  let val = form.cartao.replace(/\D/g, '').slice(0, 16)
  form.cartao = val.replace(/(\d{4})(?=\d)/g, '$1 ')
}

const onValidadeInput = () => {
  markTouched('data_validade')
  let val = form.data_validade.replace(/\D/g, '').slice(0, 4)
  if (val.length >= 3) {
    form.data_validade = `${val.slice(0, 2)}/${val.slice(2)}`
  } else {
    form.data_validade = val
  }
}

// Submeter Checkout de Compras (Público)
const submitCheckout = async () => {
  Object.keys(touched).forEach(k => touched[k] = true)

  if (!isFormValid.value) return

  submittingCheckout.value = true
  orderSuccess.value = null

  try {
    const payload = {
      ...form,
      itens: cart.value,
      total: cartSubtotal.value
    }
    const response = await axios.post(`${API_URL}/checkout`, payload)
    orderSuccess.value = response.data
    
    cart.value = []
    Object.keys(form).forEach(key => form[key] = '')
    Object.keys(touched).forEach(key => touched[key] = false)
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao processar checkout.')
  } finally {
    submittingCheckout.value = false
  }
}

// Autenticação Admin (Sanctum)
const handleLogin = async () => {
  loginError.value = ''
  loginLoading.value = true

  try {
    const response = await axios.post(`${API_URL}/login`, loginForm)
    token.value = response.data.token
    user.value = response.data.user
    localStorage.setItem('token', token.value)
    localStorage.setItem('user', JSON.stringify(user.value))
    showLoginModal.value = false
    currentView.value = 'admin'
    await fetchCheckouts(1)
  } catch (err) {
    loginError.value = err.response?.data?.message || 'Credenciais inválidas.'
  } finally {
    loginLoading.value = false
  }
}

const handleLogout = async () => {
  try {
    await axios.post(`${API_URL}/logout`, {}, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
  } catch (err) {
    // Silent catch
  } finally {
    token.value = ''
    user.value = null
    localStorage.removeItem('token')
    localStorage.removeItem('user')
    currentView.value = 'store'
  }
}

// Ações Administrativas Protegidas
const handleCreateProduto = async () => {
  if (!user.value || !token.value) {
    showLoginModal.value = true
    return
  }

  submittingProduto.value = true
  try {
    await axios.post(`${API_URL}/produtos`, newProduto, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    Object.keys(newProduto).forEach(k => newProduto[k] = '')
    await fetchProdutos(1)
    alert('Produto cadastrado com sucesso no banco de dados!')
  } catch (err) {
    alert(err.response?.data?.message || 'Erro 401: Ação não autorizada pelo servidor.')
  } finally {
    submittingProduto.value = false
  }
}

// Abrir Modal de Edição de Produto (Admin)
const openEditProdutoModal = (produto) => {
  editingProduto.value = { ...produto }
}

// Salvar Edição de Produto (PUT /api/produtos/{id})
const handleUpdateProduto = async () => {
  if (!user.value || !token.value || !editingProduto.value) return

  updatingProduto.value = true
  try {
    await axios.put(`${API_URL}/produtos/${editingProduto.value.id}`, editingProduto.value, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    editingProduto.value = null
    await fetchProdutos(produtosPage.value)
    alert('Produto atualizado com sucesso!')
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao atualizar produto.')
  } finally {
    updatingProduto.value = false
  }
}

// Excluir Produto (DELETE /api/produtos/{id})
const handleDeleteProduto = async (id) => {
  if (!user.value || !token.value) {
    showLoginModal.value = true
    return
  }

  if (!confirm('Deseja realmente excluir este produto?')) return

  try {
    await axios.delete(`${API_URL}/produtos/${id}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    if (editingProduto.value?.id === id) {
      editingProduto.value = null
    }
    await fetchProdutos(produtosPage.value)
  } catch (err) {
    alert('Erro 401: Exclusão não autorizada pelo servidor.')
  }
}

onMounted(() => {
  fetchProdutos(1)
})
</script>

<template>
  <div class="app-wrapper">
    <!-- Navbar Topo -->
    <header class="navbar">
      <div class="brand" @click="navigateTo('store')">RHeserva Store</div>
      
      <nav class="nav-links">
        <button 
          class="nav-btn" 
          :class="{ active: currentView === 'store' }" 
          @click="navigateTo('store')"
        >
          🛍️ Produtos
        </button>

        <button 
          class="nav-btn" 
          :class="{ active: currentView === 'cart' }" 
          @click="navigateTo('cart')"
        >
          🛒 Carrinho <span v-if="totalCartItems > 0" class="badge">{{ totalCartItems }}</span>
        </button>

        <template v-if="user">
          <button 
            class="nav-btn" 
            :class="{ active: currentView === 'admin' }" 
            @click="navigateTo('admin')"
          >
            ⚙️ Painel Admin
          </button>
          <button class="nav-btn" @click="handleLogout">🚪 Sair ({{ user.name }})</button>
        </template>

        <template v-else>
          <button class="nav-btn" @click="showLoginModal = true">🔐 Painel Admin</button>
        </template>
      </nav>
    </header>

    <!-- Conteúdo Principal -->
    <main class="main-container">
      
      <!-- VIEW 1: CATÁLOGO DE PRODUTOS (PÚBLICO E PAGINADO) -->
      <section v-if="currentView === 'store'">
        <div class="page-header">
          <h2>Catálogo de Produtos</h2>
          <p>Escolha seus itens favoritos e adicione ao carrinho de compras (Clique no item para detalhes)</p>
        </div>

        <div v-if="loadingProdutos" class="header" style="padding: 3rem; text-align: center;">
          <div class="btn-spinner" style="margin: 0 auto; width: 32px; height: 32px;"></div>
          <p style="margin-top: 1rem;">Carregando produtos...</p>
        </div>

        <div v-else-if="produtos.length === 0" class="card" style="text-align: center;">
          <p>Nenhum produto cadastrado no catálogo.</p>
        </div>

        <div v-else>
          <div class="products-grid">
            <div v-for="produto in produtos" :key="produto.id" class="product-card" @click="selectedProduto = produto">
              <img :src="produto.imagem || 'https://via.placeholder.com/300'" :alt="produto.nome" class="product-img" />
              <div class="product-body">
                <h3 class="product-title">{{ produto.nome }}</h3>
                <p class="product-desc">{{ produto.descricao || 'Sem descrição cadastrada.' }}</p>
                <div class="product-footer">
                  <span class="product-price">R$ {{ Number(produto.preco).toFixed(2).replace('.', ',') }}</span>
                  <button class="btn-add-cart" @click.stop="addToCart(produto)">+ Carrinho</button>
                </div>
              </div>
            </div>
          </div>

          <!-- Paginação do Catálogo -->
          <div v-if="produtosTotalPages > 1" class="pagination-bar" style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 2rem;">
            <button class="nav-btn" :disabled="produtosPage <= 1" @click="fetchProdutos(produtosPage - 1)">◀ Anterior</button>
            <span style="font-size: 0.9rem; color: var(--text-muted);">Página {{ produtosPage }} de {{ produtosTotalPages }} (Total: {{ produtosTotalItems }} produtos)</span>
            <button class="nav-btn" :disabled="produtosPage >= produtosTotalPages" @click="fetchProdutos(produtosPage + 1)">Próxima ▶</button>
          </div>
        </div>
      </section>

      <!-- VIEW 2: CARRINHO DE COMPRAS & CHECKOUT (QUESTÃO 12 - PÚBLICO) -->
      <section v-else-if="currentView === 'cart'">
        <div class="page-header">
          <h2>Carrinho & Checkout</h2>
          <p>Confira os itens do seu pedido e preencha os dados de entrega</p>
        </div>

        <!-- Alerta de Sucesso de Pedido -->
        <div v-if="orderSuccess" class="alert-success">
          <h3>🎉 Checkout Realizado com Sucesso!</h3>
          <p>Número do Pedido: <strong>{{ orderSuccess.pedido_id }}</strong></p>
          <p style="font-size: 0.85rem; margin-top: 0.25rem;">O pedido foi registrado no banco de dados com sucesso!</p>
          <button class="btn-submit" style="margin-top: 1rem; width: auto; padding: 0.5rem 1.5rem; margin-left: auto; margin-right: auto;" @click="orderSuccess = null; navigateTo('store')">Continuar Comprando</button>
        </div>

        <div v-else class="checkout-layout">
          <!-- Coluna 1: Itens do Carrinho -->
          <div class="cart-card">
            <h3>Seu Carrinho ({{ totalCartItems }} itens)</h3>
            
            <div v-if="cart.length === 0" style="padding: 2rem 0; text-align: center; color: var(--text-muted);">
              <p>Seu carrinho está vazio.</p>
              <button class="btn-submit" style="margin-top: 1rem;" @click="navigateTo('store')">Ver Produtos</button>
            </div>

            <div v-else>
              <div v-for="(item, index) in cart" :key="item.id" class="cart-item">
                <img :src="item.imagem || 'https://via.placeholder.com/60'" :alt="item.nome" class="cart-item-img" />
                <div class="cart-item-details">
                  <div class="cart-item-title">{{ item.nome }}</div>
                  <div class="cart-item-price">R$ {{ Number(item.preco).toFixed(2).replace('.', ',') }}</div>
                </div>
                <div class="qty-controls">
                  <button class="btn-qty" @click="updateQty(index, -1)">-</button>
                  <span>{{ item.qtd }}</span>
                  <button class="btn-qty" @click="updateQty(index, 1)">+</button>
                  <button class="btn-qty" style="color: #ef4444;" @click="removeFromCart(index)">✕</button>
                </div>
              </div>

              <div class="cart-summary">
                <div class="summary-row">
                  <span>Subtotal</span>
                  <span>R$ {{ cartSubtotal.toFixed(2).replace('.', ',') }}</span>
                </div>
                <div class="summary-row">
                  <span>Frete</span>
                  <span style="color: #10b981;">Grátis</span>
                </div>
                <div class="summary-row total">
                  <span>Total</span>
                  <span>R$ {{ cartSubtotal.toFixed(2).replace('.', ',') }}</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Coluna 2: Formulário de Checkout com CEP e Cartão -->
          <div class="checkout-card">
            <h3>Dados de Entrega & Pagamento</h3>
            
            <form @submit.prevent="submitCheckout" class="form-grid" style="margin-top: 1rem;">
              <!-- Nome Completo -->
              <div class="form-group full-width">
                <label for="nome">Nome Completo <span class="required-asterisk">*</span></label>
                <input 
                  id="nome"
                  v-model="form.nome" 
                  type="text" 
                  placeholder="Ex: João da Silva"
                  @blur="markTouched('nome')"
                  @input="markTouched('nome')"
                  :class="{ 'field-error': touched.nome && !isNomeValid }"
                />
                <span v-if="touched.nome && !isNomeValid" class="error-message">Informe ao menos 3 caracteres.</span>
              </div>

              <!-- E-mail -->
              <div class="form-group full-width">
                <label for="email">E-mail <span class="required-asterisk">*</span></label>
                <input 
                  id="email"
                  v-model="form.email" 
                  type="email" 
                  placeholder="joao@exemplo.com"
                  @blur="markTouched('email')"
                  @input="markTouched('email')"
                  :class="{ 'field-error': touched.email && !isEmailValid }"
                />
                <span v-if="touched.email && !isEmailValid" class="error-message">Formato de e-mail inválido.</span>
              </div>

              <!-- CEP -->
              <div class="form-group full-width">
                <label for="cep">CEP <span class="required-asterisk">*</span> (Busca Automática)</label>
                <div class="cep-wrapper">
                  <input 
                    id="cep"
                    v-model="form.cep" 
                    type="text" 
                    maxlength="9"
                    placeholder="00000-000"
                    @input="onCepInput"
                    @blur="buscarCep"
                    :class="{ 'field-error': touched.cep && (!isCepValid || cepError) }"
                  />
                  <div v-if="loadingCep" class="input-spinner" title="Buscando endereço..."></div>
                </div>
                <span v-if="touched.cep && (cepError || !isCepValid)" class="error-message">{{ cepError || 'CEP deve ter 8 dígitos.' }}</span>
              </div>

              <!-- Rua -->
              <div class="form-group full-width">
                <label for="rua">Logradouro / Rua <span class="required-asterisk">*</span></label>
                <input 
                  id="rua"
                  v-model="form.rua" 
                  type="text" 
                  placeholder="Rua das Flores"
                  @blur="markTouched('rua')"
                  @input="markTouched('rua')"
                  :class="{ 'field-error': touched.rua && !isRuaValid }"
                />
                <span v-if="touched.rua && !isRuaValid" class="error-message">Logradouro é obrigatório.</span>
              </div>

              <!-- Bairro -->
              <div class="form-group">
                <label for="bairro">Bairro <span class="required-asterisk">*</span></label>
                <input 
                  id="bairro"
                  v-model="form.bairro" 
                  type="text" 
                  placeholder="Centro"
                  @blur="markTouched('bairro')"
                  @input="markTouched('bairro')"
                  :class="{ 'field-error': touched.bairro && !isBairroValid }"
                />
                <span v-if="touched.bairro && !isBairroValid" class="error-message">Bairro é obrigatório.</span>
              </div>

              <!-- Cidade -->
              <div class="form-group">
                <label for="cidade">Cidade <span class="required-asterisk">*</span></label>
                <input 
                  id="cidade"
                  v-model="form.cidade" 
                  type="text" 
                  placeholder="São Paulo"
                  @blur="markTouched('cidade')"
                  @input="markTouched('cidade')"
                  :class="{ 'field-error': touched.cidade && !isCidadeValid }"
                />
                <span v-if="touched.cidade && !isCidadeValid" class="error-message">Cidade é obrigatória.</span>
              </div>

              <!-- Estado / UF -->
              <div class="form-group">
                <label for="estado">Estado (UF) <span class="required-asterisk">*</span></label>
                <input 
                  id="estado"
                  v-model="form.estado" 
                  type="text" 
                  maxlength="2"
                  placeholder="SP"
                  @blur="markTouched('estado')"
                  @input="markTouched('estado')"
                  :class="{ 'field-error': touched.estado && !isEstadoValid }"
                  style="text-transform: uppercase;"
                />
                <span v-if="touched.estado && !isEstadoValid" class="error-message">UF é obrigatório.</span>
              </div>

              <!-- Número -->
              <div class="form-group">
                <label for="numero">Número <span class="required-asterisk">*</span></label>
                <input 
                  id="numero"
                  v-model="form.numero" 
                  type="text" 
                  inputmode="numeric"
                  placeholder="123"
                  @input="onNumeroInput"
                  @blur="markTouched('numero')"
                  :class="{ 'field-error': touched.numero && !isNumeroValid }"
                />
                <span v-if="touched.numero && !isNumeroValid" class="error-message">Informe apenas números.</span>
              </div>

              <!-- Complemento -->
              <div class="form-group full-width">
                <label for="complemento">Complemento (Opcional)</label>
                <input 
                  id="complemento"
                  v-model="form.complemento" 
                  type="text" 
                  placeholder="Apto 42, Bloco B"
                />
              </div>

              <!-- Cartão de Crédito -->
              <div class="form-group full-width">
                <label for="cartao">Cartão de Crédito <span class="required-asterisk">*</span></label>
                <input 
                  id="cartao"
                  v-model="form.cartao"
                  @input="onCartaoInput"
                  @blur="markTouched('cartao')"
                  type="text" 
                  placeholder="0000 0000 0000 0000"
                  maxlength="19"
                  :class="{ 'field-error': touched.cartao && !isCartaoValid }"
                />
                <span v-if="touched.cartao && !isCartaoValid" class="error-message">Cartão deve conter 16 dígitos.</span>
              </div>

              <!-- Data de Validade -->
              <div class="form-group full-width">
                <label for="data_validade">Data de Validade (MM/AA) <span class="required-asterisk">*</span></label>
                <input 
                  id="data_validade"
                  v-model="form.data_validade"
                  @input="onValidadeInput"
                  @blur="markTouched('data_validade')"
                  type="text" 
                  placeholder="12/28"
                  maxlength="5"
                  :class="{ 'field-error': touched.data_validade && !isDataValidadeValid }"
                />
                <span v-if="touched.data_validade && !isDataValidadeValid" class="error-message">Formato de data inválido (MM/AA).</span>
              </div>

              <!-- Botão de Envio (Liberado APENAS quando TODOS os campos estiverem válidos) -->
              <div class="full-width">
                <button type="submit" class="btn-submit" :disabled="!isFormValid || submittingCheckout || loadingCep">
                  <span v-if="submittingCheckout" class="btn-spinner"></span>
                  <span>{{ submittingCheckout ? 'Processando Checkout...' : 'Confirmar e Pagar' }}</span>
                </button>
              </div>
            </form>
          </div>
        </div>
      </section>

      <!-- VIEW 3: PAINEL ADMINISTRATIVO (QUESTÃO 10 - GESTÃO PAGINADA DE PRODUTOS & PEDIDOS) -->
      <section v-else-if="currentView === 'admin' && user">
        <div class="page-header">
          <h2>Painel Administrativo</h2>
          <p>Gestão paginada de produtos e pedidos (Autenticado como {{ user.name }})</p>
        </div>

        <!-- Seção 1: Pedidos Realizados (Checkouts Paginados com Modal de Detalhes) -->
        <div class="admin-card" style="margin-bottom: 2rem;">
          <div style="display: flex; justify-content: space-between; align-items: center;">
            <h3>📦 Pedidos Realizados (Total: {{ checkoutsTotalItems }})</h3>
            <button class="nav-btn" @click="fetchCheckouts(checkoutsPage)" style="font-size: 0.8rem; padding: 0.3rem 0.75rem;">🔄 Atualizar</button>
          </div>

          <div v-if="loadingCheckouts" style="padding: 2rem; text-align: center;">
            <div class="btn-spinner" style="margin: 0 auto; width: 24px; height: 24px;"></div>
            <p style="margin-top: 0.5rem; font-size: 0.85rem;">Carregando pedidos do MySQL...</p>
          </div>

          <div v-else-if="checkouts.length === 0" style="padding: 2rem; text-align: center; color: var(--text-muted);">
            <p>Nenhum pedido de checkout registrado até o momento.</p>
          </div>

          <div v-else>
            <table class="admin-table">
              <thead>
                <tr>
                  <th>ID Pedido</th>
                  <th>Cliente</th>
                  <th>Cidade/UF</th>
                  <th>Total</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="c in checkouts" :key="c.id" style="cursor: pointer;" @click="selectedCheckout = c">
                  <td><strong style="color: #818cf8;">{{ c.pedido_id }}</strong></td>
                  <td>
                    <div><strong>{{ c.nome }}</strong></div>
                    <div style="font-size: 0.8rem; color: var(--text-muted);">{{ c.email }}</div>
                  </td>
                  <td style="font-size: 0.85rem;">{{ c.cidade }}/{{ c.estado }}</td>
                  <td><strong style="color: #6ee7b7;">R$ {{ Number(c.total).toFixed(2).replace('.', ',') }}</strong></td>
                  <td>
                    <button class="nav-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem;">🔍 Ver Detalhes</button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Paginação de Pedidos -->
            <div v-if="checkoutsTotalPages > 1" class="pagination-bar" style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem;">
              <button class="nav-btn" :disabled="checkoutsPage <= 1" @click="fetchCheckouts(checkoutsPage - 1)">◀ Anterior</button>
              <span style="font-size: 0.85rem; color: var(--text-muted);">Página {{ checkoutsPage }} de {{ checkoutsTotalPages }}</span>
              <button class="nav-btn" :disabled="checkoutsPage >= checkoutsTotalPages" @click="fetchCheckouts(checkoutsPage + 1)">Próxima ▶</button>
            </div>
          </div>
        </div>

        <!-- Seção 2: Cadastrar Novo Produto (com Preview da Imagem) -->
        <div class="admin-card" style="margin-bottom: 2rem;">
          <h3>Cadastrar Novo Produto</h3>
          
          <form @submit.prevent="handleCreateProduto" class="form-grid" style="margin-top: 1rem;">
            <div class="form-group">
              <label>Nome do Produto *</label>
              <input v-model="newProduto.nome" type="text" required placeholder="Ex: Tênis Esportivo" />
            </div>

            <div class="form-group">
              <label>Preço (R$) *</label>
              <input v-model="newProduto.preco" type="number" step="0.01" required placeholder="199.90" />
            </div>

            <div class="form-group full-width">
              <label>URL da Imagem</label>
              <input v-model="newProduto.imagem" type="text" placeholder="https://exemplo.com/imagem.jpg" />
              
              <!-- Live Preview da Imagem -->
              <div v-if="newProduto.imagem" style="margin-top: 0.75rem; display: flex; align-items: center; gap: 1rem; background: rgba(15, 23, 42, 0.6); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color);">
                <img :src="newProduto.imagem" alt="Preview Novo Produto" style="width: 70px; height: 70px; object-fit: cover; border-radius: 6px;" @error="$event.target.style.display='none'" />
                <span style="font-size: 0.85rem; color: var(--text-muted);">🖼️ Pré-visualização da imagem do produto</span>
              </div>
            </div>

            <div class="form-group full-width">
              <label>Descrição</label>
              <textarea v-model="newProduto.descricao" rows="3" placeholder="Detalhes do produto..."></textarea>
            </div>

            <div class="full-width">
              <button type="submit" class="btn-submit" :disabled="submittingProduto || !user || !token">
                <span v-if="submittingProduto" class="btn-spinner"></span>
                <span>Salvar Produto</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Seção 3: Produtos Cadastrados (Paginados - Clique para Editar/Excluir) -->
        <div class="admin-card">
          <h3>Produtos Cadastrados (Total: {{ produtosTotalItems }})</h3>
          
          <table class="admin-table">
            <thead>
              <tr>
                <th>ID</th>
                <th>Imagem</th>
                <th>Nome</th>
                <th>Preço</th>
                <th>Ações</th>
              </tr>
            </thead>
            <tbody>
              <tr v-for="p in produtos" :key="p.id" style="cursor: pointer;" @click="openEditProdutoModal(p)">
                <td>#{{ p.id }}</td>
                <td>
                  <img :src="p.imagem || 'https://via.placeholder.com/40'" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;" />
                </td>
                <td><strong>{{ p.nome }}</strong></td>
                <td>R$ {{ Number(p.preco).toFixed(2).replace('.', ',') }}</td>
                <td>
                  <button class="nav-btn" style="padding: 0.3rem 0.6rem; font-size: 0.75rem; margin-right: 0.5rem;" @click.stop="openEditProdutoModal(p)">✏️ Editar / Deletar</button>
                  <button class="btn-danger" @click.stop="handleDeleteProduto(p.id)">Excluir</button>
                </td>
              </tr>
            </tbody>
          </table>

          <!-- Paginação de Produtos Admin -->
          <div v-if="produtosTotalPages > 1" class="pagination-bar" style="display: flex; justify-content: center; align-items: center; gap: 1rem; margin-top: 1.5rem;">
            <button class="nav-btn" :disabled="produtosPage <= 1" @click="fetchProdutos(produtosPage - 1)">◀ Anterior</button>
            <span style="font-size: 0.85rem; color: var(--text-muted);">Página {{ produtosPage }} de {{ produtosTotalPages }}</span>
            <button class="nav-btn" :disabled="produtosPage >= produtosTotalPages" @click="fetchProdutos(produtosPage + 1)">Próxima ▶</button>
          </div>
        </div>
      </section>

    </main>

    <!-- MODAL LOJA PÚBLICA: DETALHES DO PRODUTO (VER / COMPRAR) -->
    <div v-if="selectedProduto && currentView !== 'admin'" class="modal-overlay" @click.self="selectedProduto = null">
      <div class="modal-content" style="max-width: 500px;">
        <div class="header" style="position: relative;">
          <button @click="selectedProduto = null" style="position: absolute; right: 0; top: 0; background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">✕</button>
          <h2>{{ selectedProduto.nome }}</h2>
          <p style="color: #818cf8; font-weight: bold; font-size: 1.2rem; margin-top: 0.25rem;">R$ {{ Number(selectedProduto.preco).toFixed(2).replace('.', ',') }}</p>
        </div>

        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem;">
          <img :src="selectedProduto.imagem || 'https://via.placeholder.com/400'" :alt="selectedProduto.nome" style="width: 100%; height: 220px; object-fit: cover; border-radius: 12px;" />
          
          <div>
            <strong>Descrição do Produto:</strong>
            <p style="color: var(--text-muted); font-size: 0.95rem; margin-top: 0.4rem; line-height: 1.5;">{{ selectedProduto.descricao || 'Nenhuma descrição detalhada informada.' }}</p>
          </div>

          <div style="font-size: 0.8rem; color: var(--text-muted); border-top: 1px solid var(--border-color); padding-top: 0.75rem;">
            ID do Registro: #{{ selectedProduto.id }}
          </div>

          <button class="btn-submit" @click="addToCart(selectedProduto); selectedProduto = null; currentView = 'cart'">
            + Adicionar ao Carrinho e Comprar
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL PAINEL ADMIN: EDIÇÃO E EXCLUSÃO DO PRODUTO (COM PREVIEW DA IMAGEM) -->
    <div v-if="editingProduto" class="modal-overlay" @click.self="editingProduto = null">
      <div class="modal-content" style="max-width: 520px;">
        <div class="header" style="position: relative;">
          <button @click="editingProduto = null" style="position: absolute; right: 0; top: 0; background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">✕</button>
          <h2>Editar Produto <span style="color: #818cf8;">#{{ editingProduto.id }}</span></h2>
          <p>Altere os dados do produto no banco de dados ou exclua o registro</p>
        </div>

        <form @submit.prevent="handleUpdateProduto" class="form-grid" style="margin-top: 1rem;">
          <div class="form-group full-width">
            <label>Nome do Produto *</label>
            <input v-model="editingProduto.nome" type="text" required />
          </div>

          <div class="form-group full-width">
            <label>Preço (R$) *</label>
            <input v-model="editingProduto.preco" type="number" step="0.01" required />
          </div>

          <div class="form-group full-width">
            <label>URL da Imagem</label>
            <input v-model="editingProduto.imagem" type="text" />
            
            <!-- Live Preview da Imagem no Modal de Edição -->
            <div v-if="editingProduto.imagem" style="margin-top: 0.75rem; display: flex; align-items: center; gap: 1rem; background: rgba(15, 23, 42, 0.6); padding: 0.75rem; border-radius: 8px; border: 1px solid var(--border-color);">
              <img :src="editingProduto.imagem" alt="Preview Produto Edição" style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px;" @error="$event.target.style.display='none'" />
              <div>
                <strong style="font-size: 0.85rem; color: #818cf8;">🖼️ Pré-visualização ao vivo</strong>
                <p style="font-size: 0.75rem; color: var(--text-muted); margin-top: 0.2rem;">Atualizada dinamicamente ao alterar a URL acima.</p>
              </div>
            </div>
          </div>

          <div class="form-group full-width">
            <label>Descrição</label>
            <textarea v-model="editingProduto.descricao" rows="3"></textarea>
          </div>

          <div class="full-width" style="display: flex; gap: 1rem; margin-top: 0.5rem;">
            <button type="submit" class="btn-submit" style="flex: 1;" :disabled="updatingProduto">
              <span v-if="updatingProduto" class="btn-spinner"></span>
              <span>Salvar Alterações</span>
            </button>
            <button type="button" class="btn-danger" style="padding: 0.875rem 1rem; border-radius: 8px;" @click="handleDeleteProduto(editingProduto.id)">
              🗑️ Excluir
            </button>
          </div>
        </form>
      </div>
    </div>

    <!-- MODAL DETALHES DO PEDIDO (AO CLICAR NO CHECKOUT EM ADMIN) -->
    <div v-if="selectedCheckout" class="modal-overlay" @click.self="selectedCheckout = null">
      <div class="modal-content" style="max-width: 550px;">
        <div class="header" style="position: relative;">
          <button @click="selectedCheckout = null" style="position: absolute; right: 0; top: 0; background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">✕</button>
          <h2>Detalhes do Pedido <span style="color: #818cf8;">{{ selectedCheckout.pedido_id }}</span></h2>
          <p style="color: #6ee7b7; font-weight: bold;">Total: R$ {{ Number(selectedCheckout.total).toFixed(2).replace('.', ',') }}</p>
        </div>

        <div style="margin-top: 1rem; display: flex; flex-direction: column; gap: 1rem; font-size: 0.9rem;">
          <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
            <h4 style="color: #818cf8; margin-bottom: 0.5rem;">👤 Dados do Cliente</h4>
            <p><strong>Nome:</strong> {{ selectedCheckout.nome }}</p>
            <p><strong>E-mail:</strong> {{ selectedCheckout.email }}</p>
            <p><strong>Cartão Utiliado:</strong> <span style="font-family: monospace;">{{ selectedCheckout.cartao_final }}</span> (Validade: {{ selectedCheckout.data_validade }})</p>
          </div>

          <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
            <h4 style="color: #818cf8; margin-bottom: 0.5rem;">📍 Endereço de Entrega</h4>
            <p><strong>Logradouro:</strong> {{ selectedCheckout.rua }}, Nº {{ selectedCheckout.numero }}</p>
            <p v-if="selectedCheckout.complemento"><strong>Complemento:</strong> {{ selectedCheckout.complemento }}</p>
            <p><strong>Bairro:</strong> {{ selectedCheckout.bairro }}</p>
            <p><strong>Cidade/UF:</strong> {{ selectedCheckout.cidade }}/{{ selectedCheckout.estado }}</p>
            <p><strong>CEP:</strong> {{ selectedCheckout.cep }}</p>
          </div>

          <div style="background: rgba(15, 23, 42, 0.6); padding: 1rem; border-radius: 8px; border: 1px solid var(--border-color);">
            <h4 style="color: #818cf8; margin-bottom: 0.5rem;">📦 Itens do Pedido</h4>
            <ul style="list-style: none; padding: 0;">
              <li v-for="item in (selectedCheckout.itens_json || [])" :key="item.id" style="display: flex; justify-content: space-between; padding: 0.4rem 0; border-bottom: 1px dashed var(--border-color);">
                <span>{{ item.qtd }}x <strong>{{ item.nome }}</strong></span>
                <span>R$ {{ (item.preco * item.qtd).toFixed(2).replace('.', ',') }}</span>
              </li>
            </ul>
          </div>

          <button class="nav-btn" style="width: 100%; justify-content: center; margin-top: 0.5rem;" @click="selectedCheckout = null">
            Fechar Detalhes
          </button>
        </div>
      </div>
    </div>

    <!-- MODAL DE LOGIN ADMIN (Aparece sempre que não estiver logado ao tentar acessar Admin) -->
    <div v-if="showLoginModal && !user" class="modal-overlay" @click.self="closeLoginModal">
      <div class="modal-content">
        <div class="header" style="position: relative;">
          <button @click="closeLoginModal" style="position: absolute; right: 0; top: 0; background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer;">✕</button>
          <h2>Login de Administrador</h2>
          <p>Informe usuário e senha para acessar o painel de produtos</p>
        </div>

        <div v-if="loginError" class="error-message" style="margin-bottom: 1rem; text-align: center;">
          {{ loginError }}
        </div>

        <form @submit.prevent="handleLogin" style="display: flex; flex-direction: column; gap: 1rem;">
          <div class="form-group">
            <label>E-mail Admin *</label>
            <input v-model="loginForm.email" type="email" required placeholder="admin@rheserva.com" />
          </div>

          <div class="form-group">
            <label>Senha *</label>
            <input v-model="loginForm.password" type="password" required placeholder="••••••••" />
          </div>

          <button type="submit" class="btn-submit" :disabled="loginLoading">
            <span v-if="loginLoading" class="btn-spinner"></span>
            <span>Entrar e Acessar Painel</span>
          </button>

          <button type="button" class="nav-btn btn-cancel-modal" style="width: 100%; justify-content: center;" @click="closeLoginModal">
            Cancelar e Voltar aos Produtos
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
