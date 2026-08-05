<script setup>
// ponytail: Unified Vue 3 Full-Stack Application (Catalog, Shopping Cart, CEP Checkout & Admin Product Management)
import { ref, reactive, computed, onMounted } from 'vue'
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

// Estado dos Produtos & Carrinho
const produtos = ref([])
const loadingProdutos = ref(false)
const cart = ref([])

// Novo Produto (Admin)
const newProduto = reactive({ nome: '', descricao: '', preco: '', imagem: '' })
const submittingProduto = ref(false)

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

// Carregar Produtos da API (Público)
const fetchProdutos = async () => {
  loadingProdutos.value = true
  try {
    const response = await axios.get(`${API_URL}/produtos`)
    produtos.value = response.data
  } catch (err) {
    console.error('Erro ao carregar produtos:', err)
  } finally {
    loadingProdutos.value = false
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
    
    // Limpar Carrinho e Formulário após sucesso
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

// Ações Administrativas (Criar/Excluir Produtos)
const handleCreateProduto = async () => {
  submittingProduto.value = true
  try {
    await axios.post(`${API_URL}/produtos`, newProduto, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    Object.keys(newProduto).forEach(k => newProduto[k] = '')
    await fetchProdutos()
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao criar produto.')
  } finally {
    submittingProduto.value = false
  }
}

const handleDeleteProduto = async (id) => {
  if (!confirm('Deseja realmente excluir este produto?')) return

  try {
    await axios.delete(`${API_URL}/produtos/${id}`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    await fetchProdutos()
  } catch (err) {
    alert('Erro ao excluir produto.')
  }
}

onMounted(() => {
  fetchProdutos()
})
</script>

<template>
  <div class="app-wrapper">
    <!-- Navbar Topo -->
    <header class="navbar">
      <div class="brand" @click="currentView = 'store'">RHeserva Store</div>
      
      <nav class="nav-links">
        <button 
          class="nav-btn" 
          :class="{ active: currentView === 'store' }" 
          @click="currentView = 'store'"
        >
          🛍️ Produtos
        </button>

        <button 
          class="nav-btn" 
          :class="{ active: currentView === 'cart' }" 
          @click="currentView = 'cart'"
        >
          🛒 Carrinho <span v-if="totalCartItems > 0" class="badge">{{ totalCartItems }}</span>
        </button>

        <template v-if="user">
          <button 
            class="nav-btn" 
            :class="{ active: currentView === 'admin' }" 
            @click="currentView = 'admin'"
          >
            ⚙️ Painel Admin
          </button>
          <button class="nav-btn" @click="handleLogout">🚪 Sair</button>
        </template>

        <template v-else>
          <button class="nav-btn" @click="showLoginModal = true">🔐 Login Admin</button>
        </template>
      </nav>
    </header>

    <!-- Conteúdo Principal -->
    <main class="main-container">
      
      <!-- VIEW 1: CATÁLOGO DE PRODUTOS (PÚBLICO) -->
      <section v-if="currentView === 'store'">
        <div class="page-header">
          <h2>Catálogo de Produtos</h2>
          <p>Escolha seus itens favoritos e adicione ao carrinho de compras</p>
        </div>

        <div v-if="loadingProdutos" class="header" style="padding: 3rem;">
          <div class="btn-spinner" style="margin: 0 auto; width: 32px; height: 32px;"></div>
          <p style="margin-top: 1rem;">Carregando produtos...</p>
        </div>

        <div v-else-if="produtos.length === 0" class="card" style="text-align: center;">
          <p>Nenhum produto cadastrado no catálogo.</p>
        </div>

        <div v-else class="products-grid">
          <div v-for="produto in produtos" :key="produto.id" class="product-card">
            <img :src="produto.imagem || 'https://via.placeholder.com/300'" :alt="produto.nome" class="product-img" />
            <div class="product-body">
              <h3 class="product-title">{{ produto.nome }}</h3>
              <p class="product-desc">{{ produto.descricao || 'Sem descrição cadastrada.' }}</p>
              <div class="product-footer">
                <span class="product-price">R$ {{ Number(produto.preco).toFixed(2).replace('.', ',') }}</span>
                <button class="btn-add-cart" @click="addToCart(produto)">+ Carrinho</button>
              </div>
            </div>
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
          <button class="btn-submit" style="margin-top: 1rem; width: auto; padding: 0.5rem 1.5rem; margin-left: auto; margin-right: auto;" @click="orderSuccess = null; currentView = 'store'">Continuar Comprando</button>
        </div>

        <div v-else class="checkout-layout">
          <!-- Coluna 1: Itens do Carrinho -->
          <div class="cart-card">
            <h3>Seu Carrinho ({{ totalCartItems }} itens)</h3>
            
            <div v-if="cart.length === 0" style="padding: 2rem 0; text-align: center; color: var(--text-muted);">
              <p>Seu carrinho está vazio.</p>
              <button class="btn-submit" style="margin-top: 1rem;" @click="currentView = 'store'">Ver Produtos</button>
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

      <!-- VIEW 3: PAINEL ADMINISTRATIVO (QUESTÃO 10 - PROTEGIDO POR LOGIN) -->
      <section v-else-if="currentView === 'admin' && user">
        <div class="page-header">
          <h2>Painel Administrativo - Gestão de Produtos</h2>
          <p>Cadastre, edite e gerencie o catálogo de produtos da loja</p>
        </div>

        <div class="admin-card mb-4" style="margin-bottom: 2rem;">
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
            </div>

            <div class="form-group full-width">
              <label>Descrição</label>
              <textarea v-model="newProduto.descricao" rows="3" placeholder="Detalhes do produto..."></textarea>
            </div>

            <div class="full-width">
              <button type="submit" class="btn-submit" :disabled="submittingProduto">
                <span v-if="submittingProduto" class="btn-spinner"></span>
                <span>Salvar Produto</span>
              </button>
            </div>
          </form>
        </div>

        <div class="admin-card">
          <h3>Produtos Cadastrados ({{ produtos.length }})</h3>
          
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
              <tr v-for="p in produtos" :key="p.id">
                <td>#{{ p.id }}</td>
                <td>
                  <img :src="p.imagem || 'https://via.placeholder.com/40'" style="width: 40px; height: 40px; border-radius: 6px; object-fit: cover;" />
                </td>
                <td><strong>{{ p.nome }}</strong></td>
                <td>R$ {{ Number(p.preco).toFixed(2).replace('.', ',') }}</td>
                <td>
                  <button class="btn-danger" @click="handleDeleteProduto(p.id)">Excluir</button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </section>

    </main>

    <!-- MODAL DE LOGIN ADMIN -->
    <div v-if="showLoginModal" class="modal-overlay" @click.self="showLoginModal = false">
      <div class="modal-content">
        <div class="header">
          <h2>Login de Administrador</h2>
          <p>Autentique-se para gerenciar produtos</p>
        </div>

        <div v-if="loginError" class="error-message" style="margin-bottom: 1rem; text-align: center;">
          {{ loginError }}
        </div>

        <form @submit.prevent="handleLogin" style="display: flex; flex-direction: column; gap: 1rem;">
          <div class="form-group">
            <label>E-mail Admin</label>
            <input v-model="loginForm.email" type="email" required />
          </div>

          <div class="form-group">
            <label>Senha</label>
            <input v-model="loginForm.password" type="password" required />
          </div>

          <button type="submit" class="btn-submit" :disabled="loginLoading">
            <span v-if="loginLoading" class="btn-spinner"></span>
            <span>Entrar no Painel</span>
          </button>
        </form>
      </div>
    </div>
  </div>
</template>
