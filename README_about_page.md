# Página Sobre Nós - OpenCart

Esta é uma implementação completa de uma página "Sobre Nós" para o OpenCart, seguindo os padrões do sistema.

## 📋 Funcionalidades

- **Gerenciamento completo no Admin**: CRUD para páginas Sobre Nós
- **Editor de texto HTML**: Para descrições ricas com formatação
- **Sistema de Depoimentos**: 
  - Nome do cliente
  - Cidade (opcional)
  - Upload de imagem
  - Link de vídeo do YouTube
  - Ordenação personalizada
- **SEO completo**: Meta tags, URLs amigáveis
- **Multilíngue**: Suporte a múltiplos idiomas
- **Layouts personalizáveis**: Integração com sistema de layouts do OpenCart
- **Multi-loja**: Suporte a múltiplas lojas

## 🚀 Instalação

### 1. Executar o Script SQL

Execute o arquivo `about_install.sql` no seu banco de dados MySQL:

```sql
-- Execute o conteúdo do arquivo about_install.sql
```

### 2. Verificar a Instalação

Acesse o admin do OpenCart e navegue para:
**Ferramentas > Teste da Página Sobre Nós**

Este comando verificará se:
- ✅ Todas as tabelas foram criadas
- ✅ Controllers estão funcionando
- ✅ Models estão funcionando

### 3. Acessar o Módulo

Após a instalação, você encontrará o módulo em:
**Catálogo > Sobre Nós**

## 📁 Estrutura de Arquivos

```
admin/
├── controller/catalog/about.php          # Controller Admin
├── model/catalog/about.php               # Model Admin
├── view/template/catalog/
│   ├── about_list.twig                   # Lista de páginas
│   └── about_form.twig                   # Formulário de edição
└── language/pt-br/catalog/about.php      # Idioma PT-BR

catalog/
├── controller/information/about.php      # Controller Frontend
├── model/catalog/about.php               # Model Frontend
├── view/theme/default/template/
│   └── information/about.twig            # Template Frontend
└── language/pt-br/information/about.php  # Idioma PT-BR

admin/controller/tool/test_about.php      # Comando de teste
admin/view/template/tool/test_about.twig  # View do teste
```

## 🗄️ Estrutura do Banco de Dados

### Tabelas Principais

1. **`oc_about`** - Informações básicas da página
2. **`oc_about_description`** - Conteúdo multilíngue
3. **`oc_about_testimonial`** - Depoimentos de clientes
4. **`oc_about_to_store`** - Relacionamento com lojas
5. **`oc_about_to_layout`** - Relacionamento com layouts

## 💻 Como Usar

### No Admin

1. **Acesse**: Catálogo > Sobre Nós
2. **Adicione** uma nova página ou **edite** uma existente
3. **Configure**:
   - Título e descrição em cada idioma
   - Meta tags para SEO
   - Status e ordem de classificação
   - Lojas e layouts

### Depoimentos

1. **Abra** a aba "Depoimentos"
2. **Adicione** depoimentos com:
   - Nome do cliente
   - Cidade (opcional)
   - Imagem (clique em "Procurar")
   - URL do vídeo YouTube
   - Ordem de exibição

### No Frontend

A página será acessível via:
```
index.php?route=information/about&about_id=1
```

## 🎨 Personalização

### Estilos CSS

Os estilos estão incluídos no template `about.twig` e podem ser personalizados:

```css
.testimonial-card {
    /* Personalize os cards de depoimento */
}

.testimonials-section {
    /* Personalize a seção de depoimentos */
}
```

### Layouts

Configure layouts específicos para cada loja na aba "Design" do formulário.

## 🔧 Troubleshooting

### Problemas Comuns

1. **Erro 404**: Verifique se as tabelas foram criadas
2. **Permissões**: Confirme se o usuário tem permissões para "catalog/about"
3. **Cache**: Limpe o cache do OpenCart se necessário

### Verificação

Use o comando de teste para diagnosticar problemas:
**Ferramentas > Teste da Página Sobre Nós**

## 🧹 Limpeza

Após confirmar que tudo está funcionando:

1. **Delete** o arquivo de teste: `admin/controller/tool/test_about.php`
2. **Delete** a view de teste: `admin/view/template/tool/test_about.twig`
3. **Delete** este README se desejar

## 📱 Responsividade

O template é totalmente responsivo e funciona em:
- ✅ Desktop
- ✅ Tablet
- ✅ Mobile

## 🌐 Multilíngue

Suporte completo a múltiplos idiomas:
- Títulos
- Descrições
- Meta tags
- URLs amigáveis

## 🔒 Segurança

- Validação de formulários
- Escape de dados SQL
- Verificação de permissões
- Sanitização de inputs

## 📞 Suporte

Para dúvidas ou problemas:
1. Verifique o comando de teste
2. Confirme se todas as tabelas existem
3. Verifique os logs de erro do OpenCart

---

**Nota**: Esta implementação segue os padrões do OpenCart e é compatível com a versão padrão do sistema.
