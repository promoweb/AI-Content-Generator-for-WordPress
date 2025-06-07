jQuery(document).ready(function($) {
    // Funzione per popolare i modelli disponibili
    function populateModels(provider, apiKey) {
        if (!apiKey) {
            $('#api_model').empty().append('<option value="">Inserisci la chiave API</option>');
            return;
        }
        
        // Verifica se abbiamo modelli pre-caricati per questo provider
        if (aicgData.preloaded_models[provider]) {
            $('#api_model').empty();
            
            // Popola il dropdown con i modelli pre-caricati
            aicgData.preloaded_models[provider].forEach(model => {
                $('#api_model').append(
                    $('<option></option>')
                        .attr('value', model.id)
                        .text(model.name)
                );
            });
            
            // Seleziona il modello salvato se presente
            const savedModel = $('#api_model').data('saved');
            if (savedModel) {
                $('#api_model').val(savedModel);
            }
        } else {
            // Mostra messaggio se non ci sono modelli pre-caricati
            $('#api_model').empty().append('<option value="">Nessun modello disponibile</option>');
        }
    }
    
    // Gestione cambio servizio API
    $('#api_service').on('change', function() {
        const service = $(this).val();
        
        // Mostra/nascondi campi chiave API
        $('.api-key-field').hide();
        $(`#${service}_key`).closest('tr').show();
        
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
    
    // Imposta il modello salvato
    const savedModel = '<?php echo esc_js(AICG_Settings_Handler::get_setting("api_model")); ?>';
    if (savedModel) {
        $('#api_model').data('saved', savedModel);
    }
    
    // Carica modelli al caricamento della pagina se esiste una chiave API
    const initialService = $('#api_service').val();
    const initialApiKey = $(`#${initialService}_key`).val();
    if (initialApiKey) {
        populateModels(initialService, initialApiKey);
    }
    
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
