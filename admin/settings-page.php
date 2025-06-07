<div class="wrap aicg-settings">
    <h1>AI Content Generator</h1>
    
    <form method="post" action="<?php echo admin_url('admin-post.php'); ?>">
        <input type="hidden" name="action" value="aicg_save_settings">
        <?php wp_nonce_field('aicg_settings', 'aicg_nonce'); ?>
        
        <h2>Configurazione API</h2>
        <table class="form-table">
            <tr>
                <th><label for="api_service">Servizio AI</label></th>
                <td>
                    <select name="api_service" id="api_service">
                        <option value="openai" <?php selected(AICG_Settings_Handler::get_setting('api_service'), 'openai'); ?>>OpenAI ChatGPT</option>
                        <option value="anthropic" <?php selected(AICG_Settings_Handler::get_setting('api_service'), 'anthropic'); ?>>Anthropic Cloude 4</option>
                        <option value="deepseek" <?php selected(AICG_Settings_Handler::get_setting('api_service'), 'deepseek'); ?>>DeepSeek</option>
                        <option value="openrouter" <?php selected(AICG_Settings_Handler::get_setting('api_service'), 'openrouter'); ?>>OpenRouter</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="openrouter_key">OpenRouter API Key</label></th>
                <td>
                    <input type="password" name="openrouter_key" id="openrouter_key" 
                           value="<?php echo esc_attr(AICG_Settings_Handler::get_setting('openrouter_key')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="openrouter_model">Modello OpenRouter</label></th>
                <td>
                    <select name="openrouter_model" id="openrouter_model">
                        <option value="mistralai/mistral-7b-instruct:free" <?php selected(AICG_Settings_Handler::get_setting('openrouter_model'), 'mistralai/mistral-7b-instruct:free'); ?>>Mistral 7B (Free)</option>
                        <option value="anthropic/claude-3-haiku" <?php selected(AICG_Settings_Handler::get_setting('openrouter_model'), 'anthropic/claude-3-haiku'); ?>>Claude 3 Haiku</option>
                        <option value="meta-llama/llama-3-70b-instruct" <?php selected(AICG_Settings_Handler::get_setting('openrouter_model'), 'meta-llama/llama-3-70b-instruct'); ?>>Llama 3 70B</option>
                        <option value="openchat/openchat-7b:free" <?php selected(AICG_Settings_Handler::get_setting('openrouter_model'), 'openchat/openchat-7b:free'); ?>>OpenChat 7B (Free)</option>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="openai_key">OpenAI API Key</label></th>
                <td>
                    <input type="password" name="openai_key" id="openai_key" 
                           value="<?php echo esc_attr(AICG_Settings_Handler::get_setting('openai_key')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="anthropic_key">Anthropic API Key</label></th>
                <td>
                    <input type="password" name="anthropic_key" id="anthropic_key" 
                           value="<?php echo esc_attr(AICG_Settings_Handler::get_setting('anthropic_key')); ?>" 
                           class="regular-text">
                </td>
            </tr>
            <tr>
                <th><label for="deepseek_key">DeepSeek API Key</label></th>
                <td>
                    <input type="password" name="deepseek_key" id="deepseek_key" 
                           value="<?php echo esc_attr(AICG_Settings_Handler::get_setting('deepseek_key')); ?>" 
                           class="regular-text">
                </td>
            </tr>
        </table>
        
        <h2>Generazione Articoli</h2>
        <table class="form-table">
            <tr>
                <th><label for="titles">Titoli Articoli (uno per riga)</label></th>
                <td>
                    <textarea name="titles" id="titles" rows="5" cols="50"></textarea>
                    <p class="description">Inserisci un titolo per ogni articolo da generare</p>
                </td>
            </tr>
            <tr>
                <th><label for="category">Categoria</label></th>
                <td>
                    <?php wp_dropdown_categories([
                        'show_option_none' => 'Seleziona categoria',
                        'hide_empty' => 0,
                        'name' => 'category',
                        'id' => 'category'
                    ]); ?>
                </td>
            </tr>
            <tr>
                <th><label for="instructions">Istruzioni Generali</label></th>
                <td>
                    <textarea name="instructions" id="instructions" rows="5" cols="50"><?php 
                        echo esc_textarea(AICG_Settings_Handler::get_setting('default_instructions')); 
                    ?></textarea>
                    <p class="description">Istruzioni per la generazione del contenuto (es. stile, lunghezza, formattazione)</p>
                </td>
            </tr>
        </table>
        
        <?php submit_button('Salva Impostazioni'); ?>
    </form>
    
    <div class="aicg-generate-section">
        <button id="aicg-generate-btn" class="button button-primary">Genera Articoli</button>
        <div id="aicg-progress" style="display:none;">
            <div class="spinner is-active"></div>
            <span>Generazione in corso...</span>
        </div>
        <div id="aicg-results"></div>
    </div>
</div>
