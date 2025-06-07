jQuery(document).ready(function($) {
    $('#aicg-generate-btn').on('click', function() {
        // Mostra indicatore di progresso
        $('#aicg-progress').show();
        $('#aicg-results').html('');
        
        // Raccoglie i dati dal form
        const titles = $('#titles').val().split('\n').filter(title => title.trim() !== '');
        const category = $('#category').val();
        const instructions = $('#instructions').val();
        const api_service = $('#api_service').val();
        
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
                api_service: api_service
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
