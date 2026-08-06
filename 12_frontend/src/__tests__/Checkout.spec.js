import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import App from '../App.vue'

describe('App.vue (Checkout Validation & UI Rules)', () => {
  it('exibe asterisco vermelho (*) ao lado dos rótulos dos campos obrigatórios', () => {
    const wrapper = mount(App)
    const asterisks = wrapper.findAll('.required-asterisk')
    expect(asterisks.length).toBeGreaterThanOrEqual(10)
  })

  it('mantém o botão de submissão BLOQUEADO enquanto o formulário for inválido', () => {
    const wrapper = mount(App)
    const btn = wrapper.find('button.btn-submit')
    expect(btn.element.disabled).toBe(true)
  })

  it('aplica a classe de overlay de erro (field-error) quando um campo for preenchido incorretamente', async () => {
    const wrapper = mount(App)
    const emailInput = wrapper.find('input#email')
    await emailInput.setValue('email-invalido')

    expect(emailInput.classes()).toContain('field-error')
  })

  it('libera o botão de submissão quando TODOS os campos obrigatórios estiverem preenchidos corretamente', async () => {
    const wrapper = mount(App)
    Object.assign(wrapper.vm.form, {
      nome: 'Carlos Silva',
      email: 'carlos@teste.com',
      telefone: '(11) 99999-9999',
      cep: '01001000',
      rua: 'Praça da Sé',
      bairro: 'Sé',
      cidade: 'São Paulo',
      estado: 'SP',
      numero: '100',
      cartao: '1234 5678 9012 3456',
      titular_cartao: 'Carlos Silva',
      data_validade: '12/28',
      cvc: '123'
    })
    await wrapper.vm.$nextTick()

    const btn = wrapper.find('button.btn-submit')
    expect(btn.element.disabled).toBe(false)
  })
})
