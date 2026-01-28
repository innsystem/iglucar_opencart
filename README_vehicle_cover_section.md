# Módulo Vehicle Cover Section

Este módulo cria uma seção com 3 boxes para exibir informações sobre capas para veículos, incluindo busca de veículos, imagem da capa e benefícios.

## Estrutura dos Arquivos

```
catalog/
├── controller/extension/module/vehicle_cover_section.php
├── language/
│   ├── en-gb/extension/module/vehicle_cover_section.php
│   └── pt-br/extension/module/vehicle_cover_section.php
├── model/extension/module/vehicle_cover_section.php
└── view/theme/default/template/extension/module/vehicle_cover_section.twig

admin/
├── controller/extension/module/vehicle_cover_section.php
├── language/
│   ├── en-gb/extension/module/vehicle_cover_section.php
│   └── pt-br/extension/module/vehicle_cover_section.php
└── view/template/extension/module/vehicle_cover_section.twig
```

## Características

### Box 1: Busca de Veículos
- Campo de busca com autocomplete
- Integração com o sistema de filtros e produtos
- Navegação por marcas de veículos (baseada em filtros)
- Sistema inteligente de identificação de marcas
- Fallback para marcas padrão se não encontrar filtros

### Box 2: Imagem da Capa
- Exibe a imagem `catalog/view/theme/default/image/capa.jpg`
- Título "Capa para Veículos"
- Efeitos de hover e sombras

### Box 3: Pontos de Verificação
- Lista de 7 benefícios com ícones de verificação verde
- Layout responsivo e interativo

## Instalação

### 1. Upload dos Arquivos
Copie todos os arquivos para as respectivas pastas do OpenCart.

### 2. Executar SQL de Instalação
Execute o arquivo `vehicle_cover_section_install.sql` no banco de dados.

### 3. Configurar o Módulo
1. Acesse o painel administrativo
2. Vá em **Extensões > Módulos**
3. Procure por "Seção de Capa para Veículos"
4. Clique em **Instalar** e depois **Editar**
5. Configure o nome e status do módulo
6. Salve as configurações

### 4. Adicionar ao Layout
1. Vá em **Design > Layouts**
2. Edite o layout desejado (ex: Home)
3. Adicione o módulo "Seção de Capa para Veículos" na posição desejada
4. Salve o layout

## Uso

O módulo será exibido automaticamente nas páginas onde foi configurado. Ele inclui:

- **Busca de veículos**: Campo de busca com sugestões automáticas
- **Navegação por marcas**: Lista horizontal de marcas de veículos com imagens e nomes
- **URLs otimizadas**: Gera links específicos como `product/search&search=Audi%20A3&filter=Audi%20A3`
- **Sistema inteligente**: Identifica automaticamente marcas de veículos nos filtros
- **Busca em 3 níveis**: Estratégia otimizada para encontrar marcas de forma eficiente
- **Agrupamento inteligente**: Organiza marcas por grupos de filtros específicos
- **Fallback automático**: Usa marcas padrão se não encontrar filtros específicos
- **Imagem da capa**: Exibição central da imagem do produto
- **Benefícios**: Lista de vantagens com ícones de verificação

## Personalização

### CSS
O módulo inclui CSS responsivo que pode ser personalizado editando o arquivo `vehicle_cover_section.twig`.

### Sistema de Marcas
O módulo inclui um sistema inteligente de identificação de marcas de veículos:

1. **Busca Automática**: Procura por marcas nos filtros do sistema
2. **Identificação Inteligente**: Reconhece mais de 50 marcas de veículos
3. **URLs Otimizadas**: Cria links específicos com modelos populares (ex: Audi A3, BMW Série 3)
4. **Busca em 3 Níveis**: Estratégia otimizada para máxima eficiência
5. **Agrupamento Inteligente**: Organiza marcas por grupos de filtros específicos
6. **Fallback Automático**: Se não encontrar filtros, usa marcas padrão
7. **Configuração Flexível**: Arquivo `vehicle_brands_config.php` para personalização

#### Estratégia de Busca em 3 Níveis

**Nível 1: Busca Direcionada**
- Identifica automaticamente grupos de filtros específicos para marcas
- Busca por nomes como: "Marcas", "Brands", "Fabricantes", "Veículos", "Carros"
- SQL otimizado para encontrar apenas grupos relevantes

**Nível 2: Busca Ampliada**
- Se não encontrar grupos específicos, busca em todos os grupos de filtros
- Aplica filtros inteligentes para identificar marcas de veículos
- Organiza resultados por grupo para melhor visualização

**Nível 3: Fallback Inteligente**
- Se ainda não encontrar marcas, usa lista padrão de marcas populares
- Garante que sempre haja marcas para exibir
- URLs otimizadas com modelos específicos de cada marca

#### Marcas Suportadas
- **Marcas Principais**: Audi, BMW, Mercedes, Volkswagen, Ford, Chevrolet, etc.
- **Marcas de Luxo**: Ferrari, Lamborghini, Porsche, Bentley, Rolls Royce, etc.
- **Marcas Asiáticas**: Toyota, Honda, Nissan, Hyundai, Kia, etc.
- **Marcas Americanas**: Cadillac, Buick, Chrysler, Dodge, Jeep, etc.

### Imagens
- Substitua `capa.jpg` na pasta `catalog/view/theme/default/image/` para alterar a imagem principal
- As imagens das marcas são carregadas da pasta `catalog/view/theme/default/image/brands/`
- Sistema de fallback para marcas padrão se não encontrar imagens específicas

### Textos
Edite os arquivos de idioma para personalizar os textos:
- `catalog/language/pt-br/extension/module/vehicle_cover_section.php`
- `catalog/language/en-gb/extension/module/vehicle_cover_section.php`

## Requisitos

- OpenCart 3.x ou superior
- PHP 7.0 ou superior
- MySQL 5.6 ou superior

## Suporte

Para suporte técnico, entre em contato com a equipe de desenvolvimento.

## Licença

Este módulo é fornecido "como está" para uso em projetos OpenCart.
