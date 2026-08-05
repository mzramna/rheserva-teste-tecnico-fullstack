import { describe, it, expect } from 'vitest'
import { mount } from '@vue/test-utils'
import App from '../App.vue'

describe('App.vue (RHeserva Store Unificada)', () => {
  it('renderiza o catálogo da loja por padrão', () => {
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
})
