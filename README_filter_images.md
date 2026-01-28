# Funcionalidade de Imagens para Filtros - OpenCart

## Visão Geral

Esta funcionalidade estende o sistema de filtros do OpenCart para permitir que cada descrição de filtro tenha múltiplas imagens anexadas. Isso é especialmente útil para filtros visuais como marcas de veículos, cores, estilos, etc.

**✨ Nova Funcionalidade**: Integração completa com o Filemanager do OpenCart para seleção visual de imagens!

## Estrutura da Base de Dados

### Nova Tabela: `oc_filter_description_images`

| Campo | Tipo | Descrição |
|-------|------|-----------|
| `filter_image_id` | int(11) | ID único da imagem (AUTO_INCREMENT) |
| `filter_description_id` | int(11) | Referência à descrição do filtro |
| `filter_id` | int(11) | Referência ao filtro |
| `language_id` | int(11) | ID do idioma |
| `image` | varchar(255) | Caminho/nome da imagem |
| `sort_order` | int(3) | Ordem de exibição das imagens |

### Relacionamentos

- `filter_description_id` → `oc_filter_description.filter_id`
- `filter_id` → `oc_filter.filter_id`
- `language_id` → `oc_language.language_id`

## Arquivos Modificados

### Admin

1. **Modelo**: `admin/model/catalog/filter.php`
   - Adicionado suporte a imagens nas operações CRUD
   - Implementada exclusão em cascata de imagens

2. **Template**: `admin/view/template/catalog/filter_form.twig`
   - Adicionada coluna para gerenciar imagens
   - **Integração com Filemanager do OpenCart**
   - Interface para adicionar/remover múltiplas imagens
   - Preview visual das imagens selecionadas
   - Suporte a múltiplos idiomas

3. **Idiomas**: 
   - `admin/language/pt-br/catalog/filter.php`
   - `admin/language/en-gb/catalog/filter.php`

### Catálogo

1. **Modelo**: `catalog/model/catalog/filter.php`
   - Novos métodos para buscar filtros com imagens
   - Suporte a consultas otimizadas
   - **Caminho correto das imagens para exibição**

## **Correção de Duplicação de Imagens** 🔧

### ✅ **Problema Resolvido**
- **Antes**: Cada salvamento duplicava as imagens existentes
- **Depois**: Sistema verifica duplicatas antes de inserir
- **Resultado**: Imagens são salvas corretamente sem duplicatas

### ✅ **Solução Implementada**
```php
// 1. Obter o filter_description_id correto
$filter_description_id = $this->db->getLastId();

// 2. Verificar se a imagem já existe para este filter_description_id
$existing_image = $this->db->query("SELECT COUNT(*) as total FROM " . DB_PREFIX . "filter_description_images 
                                   WHERE filter_description_id = '" . (int)$filter_description_id . "' 
                                   AND image = '" . $this->db->escape($image['image']) . "'");

// 3. Inserir apenas se não existir
if ($existing_image->row['total'] == 0) {
    $this->db->query("INSERT INTO " . DB_PREFIX . "filter_description_images ...");
}
```

### ✅ **Correções Implementadas**

#### **1. Filter Description ID Correto**
- **Antes**: `filter_description_id` era obtido incorretamente
- **Depois**: `$filter_description_id = $this->db->getLastId()` após inserir `filter_description`
- **Resultado**: Relação correta entre `oc_filter_description` e `oc_filter_description_images`

#### **2. Verificação de Duplicatas Inteligente**
- **Verificação**: Por `filter_description_id` + `image`
- **Lógica**: Se já existe para este `filter_description_id`, não insere novamente
- **Performance**: Query simples e eficiente

#### **3. Estrutura de Relacionamento Correta**
```sql
oc_filter_description_images
├── filter_description_id → oc_filter_description.filter_description_id
├── filter_id → oc_filter.filter_id  
├── language_id → oc_language.language_id
├── image → caminho da imagem
└── sort_order → ordem de exibição
```

### ✅ **Fluxo de Funcionamento**

1. **Inserir Filter**: Obtém `filter_id`
2. **Inserir Filter Description**: Obtém `filter_description_id` correto
3. **Verificar Imagem**: Consulta se já existe para este `filter_description_id`
4. **Inserir Imagem**: Apenas se não existir
5. **Resultado**: Sem duplicatas, relacionamentos corretos

### ✅ **Vantagens da Solução**

- **✅ Relacionamentos Corretos**: `filter_description_id` aponta para o registro correto
- **✅ Sem Duplicatas**: Verificação antes da inserção
- **✅ Performance**: Queries otimizadas e eficientes
- **✅ Integridade**: Dados consistentes na base
- **✅ Manutenibilidade**: Código claro e lógico

### ✅ **Quando Aplicada**

- **`addFilter()`**: Cria relacionamentos corretos desde o início
- **`editFilter()`**: Verifica duplicatas antes de inserir
- **`deleteFilter()`**: Remove todas as imagens relacionadas

## Funcionalidades Implementadas

### ✅ Múltiplas Imagens por Filtro
- Cada descrição de filtro pode ter várias imagens
- Ordenação personalizada das imagens
- Suporte a múltiplos idiomas

### ✅ **Integração com Filemanager** 🆕
- **Modal de seleção de imagem integrado**
- **Busca de imagens por nome**
- **Preview visual das imagens selecionadas**
- **Botão de abertura do filemanager**
- **Seleção visual intuitiva**

### ✅ Interface Administrativa
- Formulário estendido para gerenciar imagens
- Botões para adicionar/remover imagens
- **Campos de imagem com botão de upload**
- **Preview em tempo real**
- Validação e persistência automática

### ✅ Integração com Sistema Existente
- Compatível com filtros existentes
- Não quebra funcionalidades atuais
- Exclusão em cascata automática

### ✅ API de Consulta
- Métodos para buscar filtros com imagens
- Consultas otimizadas com JOINs
- Ordenação por prioridade das imagens
- **Caminho correto das imagens para exibição**

## Como Usar

### 1. Instalação

Execute o script de instalação:
```sql
-- Executar o arquivo filter_images_install.sql
```

### 2. **Uso no Admin com Filemanager** 🆕

1. Acesse **Catálogo > Filtros**
2. Crie ou edite um filtro
3. Na coluna "Imagens", clique em **"Adicionar Imagem"**
4. **Clique no botão 📁 (upload) para abrir o Filemanager**
5. **Navegue e selecione a imagem desejada**
6. **A imagem será automaticamente inserida no campo**
7. **Um preview será exibido abaixo do campo**
8. Ajuste a ordem se necessário
9. Salve o filtro

### 3. Uso no Catálogo

```php
// Buscar filtros com imagens
$filters = $this->model_catalog_filter->getFiltersWithImages([
    'filter_group_id' => 1
]);

// Buscar imagens de um filtro específico
$images = $this->model_catalog_filter->getFilterImages($filter_id);

// Exibir imagem com caminho correto
foreach ($images as $image) {
    echo '<img src="' . $image['image_url'] . '" alt="' . $image['name'] . '" />';
    // image_url já inclui o prefixo 'image/' automaticamente
}
```

## **Exibição Correta das Imagens** 🎯

### ✅ **Caminho Automático**
- **Admin**: As imagens são exibidas com `image/{{ image.image }}`
- **Catálogo**: As imagens são retornadas com `image_url` já formatado
- **Estrutura**: `image/catalog/brands/toyota.png`

### ✅ **Formato de Saída**
```php
// No admin (preview)
<img src="image/catalog/brands/toyota.png" />

// No catálogo (via modelo)
$image['image_url'] = 'image/catalog/brands/toyota.png'
```

### ✅ **Compatibilidade**
- **Funciona com**: `catalog/brands/toyota.png`
- **Resulta em**: `image/catalog/brands/toyota.png`
- **URL final**: `https://seudominio.com/image/catalog/brands/toyota.png`

## Exemplos de Uso

### Filtros de Marca de Veículo
- **Nome**: "Toyota"
- **Imagens**: 
  - `catalog/brands/toyota.png` (ordem: 0)
  - `catalog/brands/toyota_car.jpg` (ordem: 1)
  - `catalog/brands/toyota_brand.jpg` (ordem: 2)

### Filtros de Cor
- **Nome**: "Vermelho"
- **Imagens**:
  - `catalog/colors/red_color.png` (ordem: 0)
  - `catalog/colors/red_sample.jpg` (ordem: 1)

## **Interface do Filemanager** 🆕

### Modal de Seleção
- **Tamanho**: Modal responsivo para melhor visualização
- **Busca**: Campo de busca integrado para encontrar imagens rapidamente
- **Navegação**: Interface familiar do OpenCart para navegar pelas pastas
- **Seleção**: Clique simples para selecionar a imagem

### Preview Visual
- **Thumbnail**: Preview de 50x50px para cada imagem
- **Atualização**: Preview atualizado automaticamente ao selecionar
- **Layout**: Organização visual clara com bordas e espaçamento
- **Caminho**: Exibição correta com prefixo `image/`

### Funcionalidades Avançadas
- **Busca por nome**: Filtro de imagens por texto
- **Navegação por pastas**: Estrutura hierárquica de arquivos
- **Seleção múltipla**: Suporte a várias imagens por filtro
- **Ordenação**: Controle da ordem de exibição

## Vantagens da Integração com Filemanager

### 🎯 **Usabilidade**
- **Interface familiar** para usuários do OpenCart
- **Seleção visual** em vez de digitação manual
- **Prevenção de erros** de caminho de arquivo
- **Navegação intuitiva** por pastas e arquivos

### 🔍 **Organização**
- **Busca rápida** por nome de arquivo
- **Estrutura hierárquica** de pastas
- **Visualização prévia** antes da seleção
- **Gestão centralizada** de arquivos

### 🛡️ **Segurança**
- **Validação automática** de caminhos
- **Integração nativa** com sistema de arquivos
- **Controle de permissões** do OpenCart

## Vantagens

1. **Flexibilidade**: Múltiplas imagens por filtro
2. **Organização**: Ordenação personalizada
3. **Multilíngue**: Suporte a múltiplos idiomas
4. **Compatibilidade**: Não quebra funcionalidades existentes
5. **Performance**: Consultas otimizadas com índices
6. **Usabilidade**: Interface visual intuitiva com filemanager
7. **Exibição Correta**: Caminhos automáticos para imagens

## Considerações Técnicas

### Índices da Base de Dados
- `filter_description_id` - Para consultas por descrição
- `filter_id` - Para consultas por filtro
- `language_id` - Para consultas por idioma

### Integridade Referencial
- Exclusão em cascata automática
- Validação de dados
- Prevenção de registros órfãos

### Performance
- JOINs otimizados
- Índices apropriados
- Consultas paginadas

### **Integração com Filemanager**
- **AJAX assíncrono** para carregamento
- **Modal responsivo** para diferentes dispositivos
- **Cache de imagens** para melhor performance
- **Tratamento de erros** robusto

### **Caminhos de Imagem**
- **Prefixo automático**: `image/` adicionado automaticamente
- **Compatibilidade**: Funciona com estrutura padrão do OpenCart
- **URLs corretas**: Gera URLs válidas para exibição
- **Flexibilidade**: Suporta qualquer estrutura de pastas

## Suporte

Para dúvidas ou problemas:
1. Verifique os logs do sistema
2. Teste a funcionalidade no admin
3. Consulte a documentação do OpenCart
4. Verifique a integridade da base de dados
5. **Teste a integração com o filemanager**
6. **Verifique se as imagens são exibidas corretamente**

## Versão

- **OpenCart**: 3.x
- **PHP**: 7.0+
- **MySQL**: 5.6+
- **Filemanager**: Integrado nativamente

## Licença

Esta funcionalidade segue a mesma licença do OpenCart.

---

## **Resumo das Novidades** 🆕

✅ **Filemanager integrado** para seleção visual de imagens  
✅ **Modal responsivo** com busca e navegação  
✅ **Preview em tempo real** das imagens selecionadas  
✅ **Interface intuitiva** para gestão de múltiplas imagens  
✅ **Integração nativa** com sistema de arquivos do OpenCart  
✅ **Botões de upload** para cada campo de imagem  
✅ **Busca avançada** por nome de arquivo  
✅ **Suporte completo** a múltiplos idiomas e ordenação  
✅ **Caminhos corretos** para exibição das imagens  
✅ **URLs automáticas** com prefixo `image/`
