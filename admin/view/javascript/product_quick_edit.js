/**
 * Product Quick Edit - Sistema de Edição Inline
 * Versão: 1.0.0
 * Compatível com: OpenCart 3.x/4.x
 */

class ProductQuickEdit {
    constructor() {
        this.editingElement = null;
        this.originalValue = null;
        this.debounceTimer = null;
        this.cache = new Map();
        this.token = this.getToken();
        this.language = window.quickEditLanguage || {};
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.loadStyles();
        this.createMessageContainer();
        
        // Debug mode
        if (window.quickEditDebug) {
            // console.log('ProductQuickEdit initialized');
        }
    }
    
    bindEvents() {
        // Delegação de eventos para performance
        $(document).on('click', '.quick-edit-icon', this.handleEditClick.bind(this));
        $(document).on('click', '.quick-edit-image', this.handleImageClick.bind(this));
        $(document).on('click', '.quick-edit-status', this.handleStatusClick.bind(this));
        $(document).on('click', '.quick-edit-btn.save', this.handleSaveClick.bind(this));
        $(document).on('click', '.quick-edit-btn.cancel', this.handleCancelClick.bind(this));
        $(document).on('keydown', '.quick-edit-input', this.handleKeyDown.bind(this));
        $(document).on('input', '.quick-edit-input', this.handleInputChange.bind(this));
        $(document).on('click', this.handleDocumentClick.bind(this));
        
        // Eventos para filemanager
        window.quickEditImageCallback = this.handleImageSelected.bind(this);
    }
    
    loadStyles() {
        if (!$('#product-quick-edit-styles').length) {
            $('<link>')
                .attr({
                    id: 'product-quick-edit-styles',
                    rel: 'stylesheet',
                    type: 'text/css',
                    href: 'view/stylesheet/product_quick_edit.css'
                })
                .appendTo('head');
        }
    }
    
    createMessageContainer() {
        if (!$('#quick-edit-messages').length) {
            $('<div id="quick-edit-messages"></div>').appendTo('body');
        }
    }
    
    handleEditClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $icon = $(e.currentTarget);
        const $field = $icon.closest('.quick-edit-field');
        
        if ($field.hasClass('editing')) {
            return;
        }
        
        this.cancelCurrentEdit();
        this.startEdit($field);
    }
    
    handleImageClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $image = $(e.currentTarget);
        const productId = $image.data('product-id');
        
        // console.log('Clique na imagem detectado');
        // console.log('Product ID:', productId);
        // console.log('Elemento imagem:', $image);
        
        if (!productId) {
            this.showMessage('Erro: ID do produto não encontrado', 'error');
            console.error('Product ID não encontrado no elemento:', $image);
            return;
        }
        
        this.currentImageProductId = productId;
        // console.log('Product ID armazenado:', this.currentImageProductId);
        
        this.openFileManager();
    }
    
    handleStatusClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        const $button = $(e.currentTarget);
        const productId = $button.data('product-id');
        
        if ($button.hasClass('loading') || !productId) {
            return;
        }
        
        this.toggleStatus(productId, $button);
    }
    
    handleSaveClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        this.saveCurrentEdit();
    }
    
    handleCancelClick(e) {
        e.preventDefault();
        e.stopPropagation();
        
        this.cancelCurrentEdit();
    }
    
    handleKeyDown(e) {
        switch (e.keyCode) {
            case 13: // Enter
                e.preventDefault();
                this.saveCurrentEdit();
                break;
            case 27: // Escape
                e.preventDefault();
                this.cancelCurrentEdit();
                break;
        }
    }
    
    handleInputChange(e) {
        const $input = $(e.currentTarget);
        const value = $input.val();
        const field = $input.data('field');
        
        // Debounce validation
        clearTimeout(this.debounceTimer);
        this.debounceTimer = setTimeout(() => {
            this.validateInput(field, value, $input);
        }, 300);
    }
    
    handleDocumentClick(e) {
        // Cancelar edição se clicar fora
        if (!$(e.target).closest('.quick-edit-field, .quick-edit-controls').length) {
            this.cancelCurrentEdit();
        }
    }
    
    startEdit($field) {
        const field = $field.data('field');
        const currentValue = $field.find('.field-value').text().trim();
        
        this.editingElement = $field;
        this.originalValue = currentValue;
        
        // Criar input
        const $input = this.createInput(field, currentValue);
        
        // Substituir conteúdo
        $field.addClass('editing').find('.field-value').hide();
        $field.find('.quick-edit-icon').hide();
        $field.append($input);
        
        // Criar controles
        const $controls = this.createControls();
        $field.append($controls);
        
        // Focar no input
        setTimeout(() => {
            $input.focus().select();
        }, 10);
        
        // Adicionar classe ao body para smooth scroll
        $('body').addClass('editing-mode');
    }
    
    createInput(field, value) {
        let inputType = 'text';
        let inputClass = 'quick-edit-input';
        let $inputElement;
        
        switch (field) {
            case 'price':
                inputType = 'number';
                inputClass += ' currency-input';
                $inputElement = $('<input>').attr({ type: inputType, class: inputClass, value: this.getInputValue(field, value) });
                $inputElement.prop({ min: 0, step: 0.01 });
                break;
            case 'quantity':
                inputType = 'number';
                inputClass += ' integer-input';
                $inputElement = $('<input>').attr({ type: inputType, class: inputClass, value: this.getInputValue(field, value) });
                $inputElement.prop({ min: 0 });
                break;
            case 'name':
                // Usar textarea para nomes potencialmente longos
                $inputElement = $('<textarea>').attr({ class: inputClass, maxlength: 100 });
                $inputElement.val(this.getInputValue(field, value));
                break;
            default:
                $inputElement = $('<input>').attr({ type: inputType, class: inputClass, value: this.getInputValue(field, value) });
                break;
        }
        
        $inputElement.attr('data-field', field); // Adicionar data-field a ambos input e textarea
        return $inputElement;
    }
    
    createControls() {
        return $('<div class="quick-edit-controls">')
            .append(
                $('<button class="quick-edit-btn save">Salvar</button>'),
                $('<button class="quick-edit-btn cancel">Cancelar</button>')
            );
    }
    
    getInputValue(field, displayValue) {
        switch (field) {
            case 'price':
                // Extrair apenas números e pontos/vírgulas
                return displayValue.replace(/[^\d.,]/g, '').replace(',', '.');
            case 'quantity':
                // Extrair apenas números
                return displayValue.replace(/\D/g, '');
            default:
                return displayValue;
        }
    }
    
    validateInput(field, value, $input) {
        const $field = $input.closest('.quick-edit-field');
        let isValid = true;
        let message = '';
        
        // Remover mensagens anteriores
        $field.find('.quick-edit-validation-message').remove();
        $field.removeClass('invalid');
        
        switch (field) {
            case 'name':
                if (value.length < 3) {
                    isValid = false;
                    message = 'Nome deve ter pelo menos 3 caracteres';
                } else if (value.length > 255) {
                    isValid = false;
                    message = 'Nome não pode ter mais de 255 caracteres';
                }
                break;
                
            case 'model':
                if (value.length > 64) {
                    isValid = false;
                    message = 'Modelo não pode ter mais de 64 caracteres';
                }
                break;
                
            case 'price':
                const price = parseFloat(value);
                if (isNaN(price) || price < 0) {
                    isValid = false;
                    message = 'Preço deve ser um número maior ou igual a zero';
                }
                break;
                
            case 'quantity':
                const quantity = parseInt(value);
                if (isNaN(quantity) || quantity < 0) {
                    isValid = false;
                    message = 'Quantidade deve ser um número inteiro maior ou igual a zero';
                }
                break;
        }
        
        if (!isValid) {
            $field.addClass('invalid');
            $field.append(`<div class="quick-edit-validation-message">${message}</div>`);
        }
        
        return isValid;
    }
    
    saveCurrentEdit() {
        if (!this.editingElement) {
            return;
        }
        
        const $field = this.editingElement;
        const $input = $field.find('.quick-edit-input');
        const field = $input.data('field');
        const value = $input.val().trim();
        const productId = $field.data('product-id');
        
        // Validar antes de salvar
        if (!this.validateInput(field, value, $input)) {
            return;
        }
        
        // Verificar se houve mudança
        if (value === this.getInputValue(field, this.originalValue)) {
            this.cancelCurrentEdit();
            return;
        }
        
        this.performSave(productId, field, value, $field);
    }
    
    performSave(productId, field, value, $field) {
        $field.addClass('saving').removeClass('editing invalid');
        $field.find('.quick-edit-controls').hide();
        $field.append('<span class="quick-edit-loading"></span>');
        
        const requestData = {
            product_id: productId,
            field: field,
            value: value,
            user_token: this.token
        };
        
        // Cache key para evitar requisições duplicadas
        const cacheKey = `${productId}-${field}-${value}`;
        
        if (this.cache.has(cacheKey)) {
            this.handleSaveSuccess(this.cache.get(cacheKey), $field);
            return;
        }
        
        $.ajax({
            url: 'index.php?route=catalog/product_quick_edit/updateField&user_token=' + this.token,
            type: 'POST',
            data: requestData,
            dataType: 'json',
            timeout: 10000,
            beforeSend: (xhr) => {
                // Adicionar headers personalizados
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
            }
        })
        .done((response) => {
            if (response.success) {
                this.cache.set(cacheKey, response);
                this.handleSaveSuccess(response, $field);
            } else {
                this.handleSaveError(response.error || 'Erro desconhecido', $field);
            }
        })
        .fail((xhr, status, error) => {
            let message = 'Erro de conexão';
            
            if (status === 'timeout') {
                message = 'Tempo limite excedido';
            } else if (xhr.status === 403) {
                message = 'Sem permissão para esta operação';
            } else if (xhr.status === 500) {
                message = 'Erro interno do servidor';
            }
            
            this.handleSaveError(message, $field);
        });
    }
    
    handleSaveSuccess(response, $field) {
        const data = response.data;
        const field = data.field;
        
        $field.removeClass('saving editing').addClass('success');
        $field.find('.quick-edit-loading, .quick-edit-controls, .quick-edit-input').remove();
        
        // Atualizar valor exibido
        const $valueElement = $field.find('.field-value');
        
        if (field === 'quantity') {
            // Para quantidade, preservar as classes label baseadas no valor
            const quantity = parseInt(data.value);
            let labelClass = 'label-success';
            
            if (quantity <= 0) {
                labelClass = 'label-warning';
            } else if (quantity <= 5) {
                labelClass = 'label-danger';
            }
            
            $valueElement.html(`<span class="label ${labelClass}">${quantity}</span>`).show();
        } else {
            // Para outros campos, usar o valor formatado
            $valueElement.html(data.formatted_value || data.value).show();
        }
        
        $field.find('.quick-edit-icon').show();
        
        // Remover classe de sucesso após animação
        setTimeout(() => {
            $field.removeClass('success');
        }, 2000);
        
        this.showMessage('Atualizado com sucesso!', 'success');
        this.editingElement = null;
        this.originalValue = null;
        $('body').removeClass('editing-mode');
    }
    
    handleSaveError(message, $field) {
        $field.removeClass('saving').addClass('error');
        $field.find('.quick-edit-loading').remove();
        $field.find('.quick-edit-controls').show();
        
        // Remover classe de erro após animação
        setTimeout(() => {
            $field.removeClass('error');
        }, 3000);
        
        this.showMessage(message, 'error');
    }
    
    cancelCurrentEdit() {
        if (!this.editingElement) {
            return;
        }
        
        const $field = this.editingElement;
        
        $field.removeClass('editing saving error invalid');
        $field.find('.quick-edit-input, .quick-edit-controls, .quick-edit-loading, .quick-edit-validation-message').remove();
        $field.find('.field-value, .quick-edit-icon').show();
        
        this.editingElement = null;
        this.originalValue = null;
        $('body').removeClass('editing-mode');
    }
    
    openFileManager() {
        $('#modal-image').remove();
        
        // Definir o nome da função callback que o filemanager chamará
        const callbackName = 'quickEditImageSelectedCallback'; // Nome claro e único

        // Atribuir a função callback ao objeto window.parent (ou window.top)
        // O filemanager no iframe acessará window.parent[callbackName]
        window[callbackName] = (imagePath) => {
            // console.log('Callback recebido do filemanager com:', imagePath);
            // Chamar o método da instância ProductQuickEdit
            this.handleImageSelected(imagePath);
            
            // O modal é fechado pelo script do filemanager
            // Limpar a função callback global após o uso para evitar poluição do escopo global
            // Usar um pequeno delay para garantir que o script do filemanager termine
            setTimeout(() => {
                 if (window[callbackName]) {
                    delete window[callbackName];
                    // console.log('Callback global limpo:', callbackName);
                 }
            }, 50);
        };
        
        // Log para debug
        // console.log('Preparando para abrir filemanager com callback:', callbackName);
        // console.log('ProductId armazenado:', this.currentImageProductId);
        
        // Abrir o filemanager, passando o nome da função callback no parâmetro target
        $.ajax({
            url: 'index.php?route=common/filemanager&user_token=' + this.token + '&target=' + callbackName,
            dataType: 'html',
            beforeSend: () => {
                this.showMessage('Carregando gerenciador de arquivos...', 'info');
            },
            success: (html) => {
                // console.log('Filemanager carregado com sucesso');
                $('body').append('<div id="modal-image" class="modal">' + html + '</div>');
                $('#modal-image').modal('show');
                
                // Log quando o modal for aberto
                $('#modal-image').on('shown.bs.modal', () => {
                    // console.log('Modal do filemanager aberto');
                });
                
                // Cleanup básico se o modal for fechado sem selecionar imagem
                 $('#modal-image').on('hidden.bs.modal', () => {
                    // console.log('Modal do filemanager fechado');
                     // Se o callback ainda existir, significa que não foi chamado (imagem não selecionada)
                     if (window[callbackName]) {
                        //  console.log('Modal fechado sem seleção, limpando callback.');
                         delete window[callbackName];
                     }
                    $('#modal-image').remove();
                 });
            },
            error: (xhr, status, error) => {
                console.error('Erro ao carregar filemanager:', status, error);
                this.showMessage('Erro ao carregar gerenciador de arquivos: ' + error, 'error');
                // Garantir que o callback seja limpo em caso de erro de carregamento
                 if (window[callbackName]) {
                     delete window[callbackName];
                    //  console.log('Erro no carregamento do filemanager, limpando callback:', callbackName);
                 }
            }
        });
    }
    
    handleImageSelected(imagePath) {
        // Primeiro, adicione um log para CONFIRMAR se esta função está sendo chamada
        // console.log('handleImageSelected called with:', imagePath);

        if (!this.currentImageProductId || !imagePath) {
            this.showMessage('Erro: dados da imagem não encontrados', 'error');
            console.error('handleImageSelected: missing productId or imagePath', { productId: this.currentImageProductId, imagePath: imagePath });
            return; // Sai da função se faltarem dados
        }

        const productId = this.currentImageProductId;
        const $image = $(`.quick-edit-image[data-product-id="${productId}"]`);

        // Verifique se $image foi encontrado
        if (!$image.length) {
             this.showMessage('Erro interno: elemento de imagem não encontrado na página.', 'error');
             console.error('handleImageSelected: image element not found for productId', productId);
             return; // Sai da função se o elemento não for encontrado
        }


        $image.addClass('loading');
        this.showMessage('Atualizando imagem...', 'info');

        const requestData = {
            product_id: productId,
            image_path: imagePath,
            user_token: this.token
        };

        // console.log('Sending AJAX request to update image:', requestData); // Log antes da requisição

        $.ajax({
            url: 'index.php?route=catalog/product_quick_edit/updateImage&user_token=' + this.token,
            type: 'POST',
            data: requestData,
            dataType: 'json',
            timeout: 15000
        })
        .done((response) => {
            // console.log('AJAX success response:', response); // Log da resposta de sucesso
            if (response.success && response.data) {
                // Atualizar imagem
                let $img = $image.find('img');
                if ($img.length) {
                    const newSrc = response.data.image_url + '?t=' + Date.now();
                    $img.attr('src', newSrc);
                } else {
                    // Substituir o ícone por uma imagem
                    $image.html(`<img src="${response.data.image_url}?t=${Date.now()}" alt="Product Image" class="img-thumbnail" />`);
                }
                this.showMessage('Imagem atualizada com sucesso!', 'success');
            } else {
                // Log do erro retornado pelo servidor
                console.error('AJAX response error:', response.error);
                this.showMessage(response.error || 'Erro ao atualizar imagem', 'error');
            }
        })
        .fail((xhr, status, error) => {
            // Logs de falha da requisição
            console.error('AJAX request failed:', status, error, xhr.responseText);
            let message = 'Erro desconhecido na atualização da imagem';
            if (status === 'timeout') {
                message = 'Tempo limite excedido ao atualizar imagem';
            } else if (xhr.status === 403) {
                 message = 'Permissão negada para atualizar imagem';
            } else if (xhr.status === 500) {
                 message = 'Erro interno do servidor ao atualizar imagem';
            } else if (error) {
                 message = 'Erro na requisição: ' + error;
            }
            this.showMessage(message, 'error');
        })
        .always(() => {
            // console.log('AJAX request completed.'); // Log ao final da requisição (sucesso ou falha)
            $image.removeClass('loading');
            this.currentImageProductId = null;
        });
    }
    
    toggleStatus(productId, $button) {
        $button.addClass('loading');
        
        $.ajax({
            url: 'index.php?route=catalog/product_quick_edit/toggleStatus&user_token=' + this.token,
            type: 'POST',
            data: {
                product_id: productId
            },
            dataType: 'json',
            timeout: 10000
        })
        .done((response) => {
            if (response.success) {
                const data = response.data;
                
                // Atualizar botão
                $button
                    .removeClass('btn-success btn-danger')
                    .addClass(data.button_class)
                    .text(data.status_text)
                    .data('status', data.status);
                
                this.showMessage('Status atualizado com sucesso!', 'success');
            } else {
                this.showMessage(response.error || 'Erro ao atualizar status', 'error');
            }
        })
        .fail(() => {
            this.showMessage('Erro ao atualizar status', 'error');
        })
        .always(() => {
            $button.removeClass('loading');
        });
    }
    
    showMessage(message, type = 'info') {
        const $message = $(`
            <div class="quick-edit-message ${type}">
                ${message}
            </div>
        `);
        
        $('#quick-edit-messages').append($message);
        
        // Animar entrada
        setTimeout(() => {
            $message.addClass('show');
        }, 10);
        
        // Remover após 5 segundos
        setTimeout(() => {
            $message.removeClass('show');
            setTimeout(() => {
                $message.remove();
            }, 300);
        }, 5000);
    }
    
    getToken() {
        // Tentar obter token de várias fontes possíveis
        let token = '';
        
        // 1. Input hidden no formulário
        token = $('input[name="user_token"]').val();
        if (token) return token;
        
        // 2. Variável global JavaScript
        if (window.user_token) return window.user_token;
        
        // 3. Meta tag
        token = $('meta[name="user_token"]').attr('content');
        if (token) return token;
        
        // 4. Extrair da URL atual
        const urlParams = new URLSearchParams(window.location.search);
        token = urlParams.get('user_token');
        if (token) return token;
        
        // 5. Extrair de qualquer link que contenha user_token
        const linkWithToken = $('a[href*="user_token="]').first().attr('href');
        if (linkWithToken) {
            const match = linkWithToken.match(/user_token=([^&]+)/);
            if (match) return match[1];
        }
        
        console.warn('ProductQuickEdit: Token não encontrado!');
        return '';
    }
    
    // Método público para outras integrações
    updateProduct(productId, field, value) {
        return new Promise((resolve, reject) => {
            $.ajax({
                url: 'index.php?route=catalog/product_quick_edit/updateField',
                type: 'POST',
                data: {
                    product_id: productId,
                    field: field,
                    value: value,
                    user_token: this.token
                },
                dataType: 'json'
            })
            .done((response) => {
                if (response.success) {
                    resolve(response);
                } else {
                    reject(new Error(response.error));
                }
            })
            .fail((xhr, status, error) => {
                reject(new Error(`HTTP ${xhr.status}: ${error}`));
            });
        });
    }
    
    // Cleanup
    destroy() {
        $(document).off('.quick-edit');
        this.cache.clear();
        $('body').removeClass('editing-mode');
        $('#quick-edit-messages').remove();
        $('#product-quick-edit-styles').remove();
    }
}

// Inicialização automática quando o DOM estiver pronto
$(document).ready(function() {
    // Verificar se já foi inicializado
    if (window.productQuickEdit) {
        return;
    }
    
    // Definir callbacks globais para diferentes versões do OpenCart
    window.quickEditImageCallback = function(imagePath) {
        // console.log('Global callback (quickEditImageCallback) chamado com:', imagePath);
        if (window.productQuickEdit) {
            window.productQuickEdit.handleImageSelected(imagePath);
        }
    };
    
    // Criar instância global
    window.productQuickEdit = new ProductQuickEdit();
    
    // Debug info
    if (window.quickEditDebug) {
        // console.log('ProductQuickEdit ready', window.productQuickEdit);
        // console.log('Token obtained:', window.productQuickEdit.token);
        // console.log('Callbacks globais definidos: quickEditImageCallback');
    }
});

// Função de teste para verificar se o filemanager consegue chamar callbacks
window.testQuickEditCallback = function(imagePath) {
    // console.log('Função de teste chamada com:', imagePath);
    alert('Callback funcionando! Imagem: ' + imagePath);
}; 