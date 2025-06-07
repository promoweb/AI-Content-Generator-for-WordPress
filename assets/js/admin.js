jQuery(document).ready(function($) {
    // Modelli predefiniti per ogni provider
    const predefinedModels = {
        'openai': [
            { id: 'gpt-4o', name: 'GPT-4o' },
            { id: 'gpt-4o-mini', name: 'GPT-4o Mini' },
            { id: 'gpt-4-turbo', name: 'GPT-4 Turbo' },
            { id: 'gpt-4', name: 'GPT-4' },
            { id: 'gpt-3.5-turbo', name: 'GPT-3.5 Turbo' }
        ],
        'anthropic': [
            { id: 'claude-3-5-sonnet-20241022', name: 'Claude 3.5 Sonnet' },
            { id: 'claude-3-opus-20240229', name: 'Claude 3 Opus' },
            { id: 'claude-3-sonnet-20240229', name: 'Claude 3 Sonnet' },
            { id: 'claude-3-haiku-20240307', name: 'Claude 3 Haiku' }
        ],
        'deepseek': [
            { id: 'deepseek-chat', name: 'DeepSeek Chat' },
            { id: 'deepseek-coder', name: 'DeepSeek Coder' }
        ],
        'openrouter': [
            { id: 'openai/gpt-4o', name: 'GPT-4o (OpenRouter)' },
            { id: 'anthropic/claude-3.5-sonnet', name: 'Claude 3.5 Sonnet (OpenRouter)' },
            { id: 'deepseek/deepseek-chat', name: 'DeepSeek Chat (OpenRouter)' },
            { id: 'deepseek/deepseek-r1', name: 'DeepSeek R1 (OpenRouter)' },
            { id: 'meta-llama/llama-3.2-90b-vision-instruct', name: 'Llama 3.2 90B Vision' }
        ]
    };

    // Funzione per popolare i modelli disponibili
    function populateModels(provider, apiKey) {
        console.log('Populating models for provider:', provider, 'API Key present:', !!apiKey);
        
        if (!apiKey) {
            $('#api_model').empty().append('<option value="">Inserisci la chiave API</option>');
            return;
        }
        
        // Usa i modelli predefiniti
        if (predefinedModels[provider]) {
            $('#api_model').empty();
            
            // Popola il dropdown con i modelli predefiniti
            predefinedModels[provider].forEach(model => {
                $('#api_model').append(
                    $('<option></option>')
                        .attr('value', model.id)
                        .text(model.name)
                );
            });
            
            console.log('Loaded', predefinedModels[provider].length, 'models for', provider);
            
            // Seleziona il modello salvato se presente
            const savedModel = $('#api_model').data('saved');
            if (savedModel) {
                $('#api_model').val(savedModel);
                console.log('Selected saved model:', savedModel);
            }
        } else {
            $('#api_model').empty().append('<option value="">Provider non supportato</option>');
        }
    }
    
    // Gestione cambio servizio API
    $('#api_service').on('change', function() {
        const service = $(this).val();
        console.log('Service changed to:', service);
        
        // Mostra/nascondi campi chiave API
        $('.api-key-field').hide();
        $(`.${service}-key`).show();
        
        // Carica modelli se la chiave è presente
        const apiKey = $(`#${service}_key`).val();
        populateModels(service, apiKey);
    });
    
    // Gestione modifica chiavi API
    $('input[id$="_key"]').on('input', function() {
        const provider = $('#api_service').val();
        const apiKey = $(this).val();
        populateModels(provider, apiKey);
    });
    
    // Carica modelli al caricamento della pagina
    $(document).ready(function() {
        const initialService = $('#api_service').val();
        console.log('Initial service:', initialService);
        
        // Mostra solo il campo API key del servizio selezionato
        $('.api-key-field').hide();
        $(`.${initialService}-key`).show();
        
        const initialApiKey = $(`#${initialService}_key`).val();
        if (initialApiKey) {
            populateModels(initialService, initialApiKey);
        } else {
            console.log('No API key found for', initialService);
        }
    });
    
    // Codice esistente per la generazione articoli
    $('#aicg-generate-btn').on('click', function() {
        // Mostra indicatore di progresso
        $('#aicg-progress').show();
        $('#aicg-results').html('');
        
        // Raccoglie i dati dal form
        const titles = $('#titles').val().split('\n').filter(title => title.trim() !== '');
        const category = $('#category').val();
        const instructions = $('#instructions').val();
        const api_service = $('#api_service').val();
        const api_model = $('#api_model').val();
        
        // Verifica dati obbligatori
        if (titles.length === 0) {
            alert('Inserisci almeno un titolo');
            $('#aicg-progress').hide();
            return;
        }
        
        if (!category || category < 1) {
            alert('Seleziona una categoria');
            $('#aicg-progress').hide();
            return;
        }
        
        if (!api_model) {
            alert('Seleziona un modello');
            $('#aicg-progress').hide();
            return;
        }
        
        // Invia richiesta AJAX
        $.ajax({
            url: aicgData.ajax_url,
            type: 'POST',
            data: {
                action: 'aicg_generate_articles',
                nonce: aicgData.nonce,
                titles: titles,
                category: category,
                instructions: instructions,
                api_service: api_service,
                api_model: api_model
            },
            success: function(response) {
                $('#aicg-progress').hide();
                
                if (response.success) {
                    let html = '<div class="notice notice-success"><ul>';
                    response.data.forEach(article => {
                        if (article.success) {
                            html += `<li>Articolo "${article.title}" creato: <a href="${article.edit_link}" target="_blank">Modifica</a></li>`;
                        } else {
                            html += `<li>Errore per "${article.title}": ${article.error}</li>`;
                        }
                    });
                    html += '</ul></div>';
                    $('#aicg-results').html(html);
                } else {
                    $('#aicg-results').html(`<div class="notice notice-error">${response.data}</div>`);
                }
            },
            error: function() {
                $('#aicg-progress').hide();
                $('#aicg-results').html('<div class="notice notice-error">Errore durante la generazione</div>');
            }
        });
    });
});
