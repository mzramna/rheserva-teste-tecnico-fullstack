import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import App from '../App.vue'

describe('RHeserva E-Commerce Unificado (App.vue)', () => {
  it('renderiza o catálogo da loja por padrão com visualização pública', () => {
    const wrapper = mount(App)
    expect(wrapper.text()).toContain('RHeserva Store')
    expect(wrapper.text()).toContain('Catálogo de Produtos')
  })

  it('permite alternar para a aba do carrinho de compras', async () => {
    const wrapper = mount(App)
    const cartBtn = wrapper.findAll('.nav-btn')[1]
    await cartBtn.trigger('click')
    
    expect(wrapper.text()).toContain('Carrinho & Checkout')
  })

  it('exibe modal de login ao tentar acessar o painel admin sem estar autenticado', async () => {
    const wrapper = mount(App)
    const adminBtn = wrapper.findAll('.nav-btn')[2]
    await adminBtn.trigger('click')

    expect(wrapper.text()).toContain('Login de Administrador')
    expect(wrapper.find('.modal-overlay').exists()).toBe(true)
  })

  it('retorna para a aba de produtos ao cancelar o modal de login sem autenticar', async () => {
    const wrapper = mount(App)
    
    // Abre modal clicando no botão Admin
    const adminBtn = wrapper.findAll('.nav-btn')[2]
    await adminBtn.trigger('click')
    expect(wrapper.find('.modal-overlay').exists()).toBe(true)

    // Clica no botão de cancelar
    const cancelBtn = wrapper.find('.btn-cancel-modal')
    await cancelBtn.trigger('click')

    // Modal é fechado e volta para Produtos
    expect(wrapper.find('.modal-overlay').exists()).toBe(false)
    expect(wrapper.text()).toContain('Catálogo de Produtos')
  })
})
