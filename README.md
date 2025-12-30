# WC Category Hierarchy Fixer

Plugin WordPress que corrige automaticamente a hierarquia de categorias de produtos no WooCommerce.

## Descrição

Este plugin força automaticamente a associação de todas as categorias pai (ancestrais) quando um produto está associado apenas à categoria filha. Inclui funcionalidade de ação em massa para correção de produtos existentes.

## Funcionalidades

- **Correção Automática**: Ao salvar um produto, automaticamente adiciona as categorias pai se estiverem faltando
- **Ação em Massa**: Permite corrigir múltiplos produtos de uma vez através da lista de produtos no admin
- **Preservação de Dados**: Mantém todas as categorias existentes, apenas adiciona as categorias pai necessárias

## Requisitos

- WordPress 6.0+
- WooCommerce 7.0+
- PHP 7.4+

## Instalação

1. Faça upload da pasta do plugin para `/wp-content/plugins/`
2. Ative o plugin através do menu 'Plugins' no WordPress
3. O plugin funcionará automaticamente - não há configurações necessárias

## Como Usar

### Correção Automática
O plugin funciona automaticamente sempre que você salva ou atualiza um produto.

### Ação em Massa
1. Vá para **Produtos > Todos os produtos**
2. Selecione os produtos que deseja corrigir
3. No dropdown "Ações em massa", escolha "Corrigir Hierarquia de Categorias"
4. Clique em "Aplicar"

## Exemplo

Se um produto está associado apenas à categoria "Smartphones Android":
- **Antes**: Produto → Smartphones Android
- **Depois**: Produto → Eletrônicos, Smartphones, Smartphones Android

## Autor

**Dsantos Info**  
Website: [https://dsantosinfo.com.br/](https://dsantosinfo.com.br/)

## Licença

GPL v2 ou posterior