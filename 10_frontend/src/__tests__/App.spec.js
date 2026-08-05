import { describe, it, expect, beforeEach } from 'vitest'
import { mount } from '@vue/test-utils'
import App from '../App.vue'

// ponytail: Lean frontend component tests checking render and auth state boundaries
describe('App.vue', () => {
  beforeEach(() => {
    localStorage.clear()
  })

  it('deve renderizar o título principal do CRUD', () => {
    const wrapper = mount(App)
    expect(wrapper.text()).toContain('Gestão de Produtos')
  })

  it('exibe mensagem informativa quando usuário não está autenticado', () => {
    const wrapper = mount(App)
    expect(wrapper.text()).toContain('Apenas usuários autenticados podem cadastrar')
    expect(wrapper.find('form.login-inline').exists()).toBe(true)
  })

  it('esconde formulário de cadastro quando deslogado', () => {
    const wrapper = mount(App)
    expect(wrapper.find('section.card form').exists()).toBe(false)
  })

  it('exibe o formulário de produto quando o token está presente', () => {
    localStorage.setItem('token', 'fake-token-123')
    localStorage.setItem('user', JSON.stringify({ email: 'admin@teste.com' }))
    const wrapper = mount(App)
    expect(wrapper.text()).toContain('Conectado como admin@teste.com')
  })
})
