<script setup>
// ponytail: Clean Vue 3 Composition API for Checkout with numeric-only enforcement on numero
import { ref, reactive, computed } from 'vue'
import cepPromise from 'cep-promise'
import axios from 'axios'

const API_URL = '/api'

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

// Rastreamento de interação do usuário (dirty / touched)
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
const submitting = ref(false)
const orderSuccess = ref(null)
const cepError = ref('')

const cleanCep = computed(() => form.cep.replace(/\D/g, ''))
const cleanCartao = computed(() => form.cartao.replace(/\D/g, ''))

// Validações de tipo e formato individual de campos
const isNomeValid = computed(() => form.nome.trim().length >= 3)
const isEmailValid = computed(() => /\S+@\S+\.\S+/.test(form.email.trim()))
const isCepValid = computed(() => /^\d{8}$/.test(cleanCep.value))
const isRuaValid = computed(() => form.rua.trim().length > 0)
const isBairroValid = computed(() => form.bairro.trim().length > 0)
const isCidadeValid = computed(() => form.cidade.trim().length > 0)
const isEstadoValid = computed(() => /^[A-Z]{2}$/i.test(form.estado.trim()))
const isNumeroValid = computed(() => /^\d+$/.test(form.numero.trim())) // APENAS NÚMEROS
const isCartaoValid = computed(() => /^\d{16}$/.test(cleanCartao.value))
const isDataValidadeValid = computed(() => /^(0[1-9]|1[0-2])\/\d{2}$/.test(form.data_validade.trim()))

// O formulário completo só é considerado válido se TODOS os campos obrigatórios forem válidos
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
         isDataValidadeValid.value
})

const markTouched = (field) => {
  touched[field] = true
}

// Busca automática do CEP via cep-promise
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

// Higienização para permitir APENAS números no campo Número
const onNumeroInput = () => {
  markTouched('numero')
  form.numero = form.numero.replace(/\D/g, '')
}

// Formatação dinâmica do cartão de crédito (#### #### #### ####)
const onCartaoInput = () => {
  markTouched('cartao')
  let val = form.cartao.replace(/\D/g, '').slice(0, 16)
  form.cartao = val.replace(/(\d{4})(?=\d)/g, '$1 ')
}

// Formatação dinâmica da data de validade (MM/AA)
const onValidadeInput = () => {
  markTouched('data_validade')
  let val = form.data_validade.replace(/\D/g, '').slice(0, 4)
  if (val.length >= 3) {
    form.data_validade = `${val.slice(0, 2)}/${val.slice(2)}`
  } else {
    form.data_validade = val
  }
}

// Envio do formulário de checkout
const submitCheckout = async () => {
  Object.keys(touched).forEach(k => touched[k] = true)

  if (!isFormValid.value) return

  submitting.value = true
  orderSuccess.value = null

  try {
    const response = await axios.post(`${API_URL}/checkout`, form)
    orderSuccess.value = response.data
    Object.keys(form).forEach(key => form[key] = '')
    Object.keys(touched).forEach(key => touched[key] = false)
  } catch (err) {
    alert(err.response?.data?.message || 'Erro ao processar checkout.')
  } finally {
    submitting.value = false
  }
}
</script>

<template>
  <div class="container">
    <div class="card">
      <div class="header">
        <h1>Finalizar Checkout</h1>
        <p>Preencha os dados do seu endereço e pagamento para concluir</p>
      </div>

      <!-- Alerta de Sucesso -->
      <div v-if="orderSuccess" class="alert-success">
        <h3>🎉 Checkout Realizado com Sucesso!</h3>
        <p>Número do Pedido: <strong>{{ orderSuccess.pedido_id }}</strong></p>
        <p style="font-size: 0.85rem; margin-top: 0.25rem;">Obrigado por comprar conosco!</p>
      </div>

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

        <!-- Número (APENAS NÚMEROS PERMITIDOS) -->
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
          <button type="submit" class="btn-submit" :disabled="!isFormValid || submitting || loadingCep">
            <span v-if="submitting" class="btn-spinner"></span>
            <span>{{ submitting ? 'Processando Checkout...' : 'Confirmar e Pagar' }}</span>
          </button>
        </div>
      </form>
    </div>
  </div>
</template>
