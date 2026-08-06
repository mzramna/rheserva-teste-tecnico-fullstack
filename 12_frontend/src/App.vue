<script setup>
import { ref, reactive, computed } from 'vue'
import cepPromise from 'cep-promise'
import { isValidPhoneNumber } from 'libphonenumber-js'

// Q12: sacola hardcoded no front, exatamente como pede o enunciado (não vem de API/backend)
const cartItems = ref([
  { id: 1, nome: 'Produto Lorem', preco: 19.90, imagem: 'https://via.placeholder.com/80', quantidade: 2 },
  { id: 2, nome: 'Produto Ipsum', preco: 49.90, imagem: 'https://via.placeholder.com/80', quantidade: 1 },
])

const form = reactive({
  nome: '',
  email: '',
  telefone: '',
  cep: '',
  rua: '',
  bairro: '',
  cidade: '',
  estado: '',
  numero: '',
  complemento: '',
  cartao: '',
  titular_cartao: '',
  data_validade: '',
  cvc: ''
})

const touched = reactive({
  nome: false,
  email: false,
  telefone: false,
  cep: false,
  rua: false,
  bairro: false,
  cidade: false,
  estado: false,
  numero: false,
  cartao: false,
  titular_cartao: false,
  data_validade: false,
  cvc: false
})

const loadingCep = ref(false)
const submitting = ref(false)
const orderSuccess = ref(null)
const cepError = ref('')

const cleanCep = computed(() => form.cep.replace(/\D/g, ''))
const cleanTelefone = computed(() => form.telefone.replace(/\D/g, ''))
const cleanCartao = computed(() => form.cartao.replace(/\D/g, ''))

// Validações de tipo e formato idênticas ao projeto unificado
const isNomeValid = computed(() => form.nome.trim().length >= 3)
const isEmailValid = computed(() => /\S+@\S+\.\S+/.test(form.email.trim()))
const isTelefoneValid = computed(() => isValidPhoneNumber(cleanTelefone.value, 'BR'))
const isCepValid = computed(() => /^\d{8}$/.test(cleanCep.value))
const isRuaValid = computed(() => form.rua.trim().length > 0)
const isBairroValid = computed(() => form.bairro.trim().length > 0)
const isCidadeValid = computed(() => form.cidade.trim().length > 0)
const isEstadoValid = computed(() => /^[A-Z]{2}$/i.test(form.estado.trim()))
const isNumeroValid = computed(() => /^\d+$/.test(form.numero.trim())) // APENAS NÚMEROS
const isCartaoValid = computed(() => /^\d{16}$/.test(cleanCartao.value))
const isTitularCartaoValid = computed(() => form.titular_cartao.trim().length >= 3)
const isDataValidadeValid = computed(() => /^(0[1-9]|1[0-2])\/\d{2}$/.test(form.data_validade.trim()))
const isCvcValid = computed(() => /^\d{3,4}$/.test(form.cvc.trim()))

const isFormValid = computed(() => {
  return isNomeValid.value &&
         isEmailValid.value &&
         isTelefoneValid.value &&
         isCepValid.value &&
         isRuaValid.value &&
         isBairroValid.value &&
         isCidadeValid.value &&
         isEstadoValid.value &&
         isNumeroValid.value &&
         isCartaoValid.value &&
         isTitularCartaoValid.value &&
         isDataValidadeValid.value &&
         isCvcValid.value
})

const markTouched = (field) => {
  touched[field] = true
}

const valorTotal = computed(() => {
  return cartItems.value.reduce((acc, item) => acc + (Number(item.preco) * (item.quantidade || 1)), 0)
})

const alterarQuantidade = (id, delta) => {
  const item = cartItems.value.find(i => i.id === id)
  if (item) {
    item.quantidade = Math.max(1, (item.quantidade || 1) + delta)
  }
}

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

const onTelefoneInput = () => {
  markTouched('telefone')
  let val = form.telefone.replace(/\D/g, '').slice(0, 11)
  if (val.length > 6) {
    form.telefone = val.replace(/(\d{2})(\d{5})(\d{0,4})/, '($1) $2-$3').trim().replace(/-$/, '')
  } else if (val.length > 2) {
    form.telefone = val.replace(/(\d{2})(\d{4})(\d{0,4})/, '($1) $2-$3').trim().replace(/-$/, '')
  } else if (val.length > 0) {
    form.telefone = val.replace(/(\d{2})(\d{0,5})/, '($1) $2')
  } else {
    form.telefone = val
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

const onCvcInput = () => {
  markTouched('cvc')
  form.cvc = form.cvc.replace(/\D/g, '').slice(0, 4)
}

// Q12: exercício 100% front-end — sucesso é definido apenas pela validade dos campos (sem backend),
// e o objeto final do pedido deve ser exibido no console ao fechar (requisito explícito do enunciado)
const submitCheckout = () => {
  Object.keys(touched).forEach(k => touched[k] = true)
  if (!isFormValid.value) return

  submitting.value = true
  orderSuccess.value = null

  const pedidoFinal = {
    pedido_id: 'PED-' + Math.random().toString(36).slice(2, 10).toUpperCase(),
    ...form,
    itens: cartItems.value,
    total: valorTotal.value
  }
  console.log('Pedido finalizado:', pedidoFinal)

  orderSuccess.value = pedidoFinal
  Object.keys(form).forEach(key => form[key] = '')
  Object.keys(touched).forEach(key => touched[key] = false)
  submitting.value = false
}
</script>

<template>
  <div class="app-container">
    <!-- Header Alinhado com o Sistema de Design Unificado -->
    <header class="navbar">
      <div class="nav-brand">
        <span class="logo-icon">🛍️</span>
        <h2>RHeserva E-Commerce <span class="badge">Questão 12 - Checkout</span></h2>
      </div>
    </header>

    <div class="main-content">
      <div class="page-header">
        <h2>Finalizar Checkout</h2>
        <p>Preencha os dados do seu endereço e pagamento para concluir o pedido</p>
      </div>

      <!-- Alerta de Sucesso -->
      <div v-if="orderSuccess" class="alert-success">
        <h3>🎉 Checkout Realizado com Sucesso!</h3>
        <p>Número do Pedido: <strong>{{ orderSuccess.pedido_id }}</strong></p>
        <p style="font-size: 0.85rem; margin-top: 0.25rem;">Obrigado por comprar conosco!</p>
      </div>

      <div class="checkout-layout">
        <!-- Formulário de Endereço & Pagamento -->
        <div class="card checkout-card">
          <form @submit.prevent="submitCheckout" class="form-grid">
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
            <div class="form-group">
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

            <!-- Telefone com validação libphonenumber-js idêntica ao código unificado -->
            <div class="form-group">
              <label for="telefone">Telefone <span class="required-asterisk">*</span></label>
              <input 
                id="telefone"
                v-model="form.telefone" 
                type="tel" 
                placeholder="(11) 99999-9999"
                @input="onTelefoneInput"
                @blur="markTouched('telefone')"
                :class="{ 'field-error': touched.telefone && !isTelefoneValid }"
              />
              <span v-if="touched.telefone && !isTelefoneValid" class="error-message">Informe um telefone válido com DDD.</span>
            </div>

            <!-- CEP (Busca Automática via cep-promise) -->
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

            <!-- Número (APENAS NÚMEROS) -->
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

            <!-- Titular do Cartão -->
            <div class="form-group full-width">
              <label for="titular_cartao">Titular do Cartão <span class="required-asterisk">*</span></label>
              <input
                id="titular_cartao"
                v-model="form.titular_cartao"
                type="text"
                placeholder="Nome impresso no cartão"
                @blur="markTouched('titular_cartao')"
                @input="markTouched('titular_cartao')"
                :class="{ 'field-error': touched.titular_cartao && !isTitularCartaoValid }"
              />
              <span v-if="touched.titular_cartao && !isTitularCartaoValid" class="error-message">Informe ao menos 3 caracteres.</span>
            </div>

            <!-- Data de Validade -->
            <div class="form-group">
              <label for="data_validade">Validade (MM/AA) <span class="required-asterisk">*</span></label>
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
              <span v-if="touched.data_validade && !isDataValidadeValid" class="error-message">Formato inválido (MM/AA).</span>
            </div>

            <!-- CVC -->
            <div class="form-group">
              <label for="cvc">CVC <span class="required-asterisk">*</span></label>
              <input
                id="cvc"
                v-model="form.cvc"
                @input="onCvcInput"
                @blur="markTouched('cvc')"
                type="text"
                placeholder="123"
                maxlength="4"
                :class="{ 'field-error': touched.cvc && !isCvcValid }"
              />
              <span v-if="touched.cvc && !isCvcValid" class="error-message">3 ou 4 dígitos.</span>
            </div>

            <!-- Botão de Envio (Liberado APENAS quando TODOS os campos estiverem válidos) -->
            <div class="full-width">
              <button type="submit" class="btn-submit" :disabled="!isFormValid || submitting || loadingCep">
                <span v-if="submitting" class="btn-spinner"></span>
                <span>{{ submitting ? 'Processando...' : 'Fechar Pedido' }}</span>
              </button>
            </div>
          </form>
        </div>

        <!-- Sacola de Itens (Hardcoded, conforme o enunciado da Questão 12) -->
        <div class="card summary-card">
          <h3>Sua Sacola</h3>

          <div class="cart-list">
            <div v-for="item in cartItems" :key="item.id" class="cart-item">
              <img :src="item.imagem || 'https://via.placeholder.com/80'" :alt="item.nome" class="cart-img" />
              <div class="cart-info">
                <h4>{{ item.nome }}</h4>
                <div class="cart-price">R$ {{ Number(item.preco).toFixed(2).replace('.', ',') }}</div>
                <div class="qty-control">
                  <button type="button" @click="alterarQuantidade(item.id, -1)">-</button>
                  <span>{{ item.quantidade || 1 }}</span>
                  <button type="button" @click="alterarQuantidade(item.id, 1)">+</button>
                </div>
              </div>
            </div>

            <div class="cart-total-row">
              <span>Total do Pedido:</span>
              <strong>R$ {{ valorTotal.toFixed(2).replace('.', ',') }}</strong>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
:root {
  --bg-main: #0f172a;
  --bg-card: #1e293b;
  --text-main: #f8fafc;
  --text-muted: #94a3b8;
  --primary-color: #818cf8;
  --accent-green: #6ee7b7;
  --border-color: rgba(255, 255, 255, 0.08);
}

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

.checkout-layout {
  display: grid;
  grid-template-columns: 1fr 380px;
  gap: 2rem;
}

@media (max-width: 960px) {
  .checkout-layout {
    grid-template-columns: 1fr;
  }
}

.card {
  background: #1e293b;
  border-radius: 12px;
  border: 1px solid rgba(255, 255, 255, 0.08);
  padding: 1.75rem;
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

.required-asterisk {
  color: #f87171;
}

input, textarea {
  background: #0f172a;
  border: 1px solid rgba(255, 255, 255, 0.15);
  color: #f8fafc;
  padding: 0.75rem 1rem;
  border-radius: 8px;
  font-size: 0.95rem;
  transition: all 0.2s ease;
}

input:focus {
  outline: none;
  border-color: #818cf8;
  box-shadow: 0 0 0 3px rgba(129, 140, 248, 0.2);
}

.field-error {
  border-color: #f87171 !important;
  background-color: rgba(248, 113, 113, 0.05) !important;
}

.error-message {
  color: #f87171;
  font-size: 0.75rem;
}

.cep-wrapper {
  position: relative;
  display: flex;
  align-items: center;
}

.cep-wrapper input {
  width: 100%;
}

.input-spinner {
  position: absolute;
  right: 12px;
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.2);
  border-top-color: #818cf8;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.btn-submit {
  width: 100%;
  padding: 1rem;
  background: linear-gradient(135deg, #6366f1 0%, #4f46e5 100%);
  color: #ffffff;
  border: none;
  border-radius: 8px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  transition: all 0.2s ease;
}

.btn-submit:hover:not(:disabled) {
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(99, 102, 241, 0.4);
}

.btn-submit:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-spinner {
  width: 18px;
  height: 18px;
  border: 2px solid rgba(255, 255, 255, 0.3);
  border-top-color: #ffffff;
  border-radius: 50%;
  animation: spin 0.8s linear infinite;
}

.cart-list {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1rem;
}

.cart-item {
  display: flex;
  gap: 1rem;
  align-items: center;
  background: rgba(15, 23, 42, 0.6);
  padding: 0.75rem;
  border-radius: 8px;
}

.cart-img {
  width: 60px;
  height: 60px;
  object-fit: cover;
  border-radius: 6px;
}

.cart-info {
  flex: 1;
}

.cart-info h4 {
  margin: 0 0 0.25rem 0;
  font-size: 0.9rem;
}

.cart-price {
  color: #6ee7b7;
  font-weight: 700;
  font-size: 0.9rem;
}

.qty-control {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  margin-top: 0.25rem;
}

.qty-control button {
  background: #334155;
  border: none;
  color: white;
  width: 22px;
  height: 22px;
  border-radius: 4px;
  cursor: pointer;
}

.cart-total-row {
  margin-top: 1rem;
  padding-top: 1rem;
  border-top: 1px solid rgba(255, 255, 255, 0.08);
  display: flex;
  justify-content: space-between;
  font-size: 1.1rem;
}

.cart-total-row strong {
  color: #6ee7b7;
}

.alert-success {
  background: rgba(16, 185, 129, 0.15);
  border: 1px solid rgba(16, 185, 129, 0.3);
  color: #34d399;
  padding: 1.25rem;
  border-radius: 10px;
  margin-bottom: 2rem;
}

@keyframes spin {
  to { transform: rotate(360deg); }
}
</style>
