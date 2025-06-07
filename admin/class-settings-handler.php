<?php
if (!defined('ABSPATH')) exit;

class AICG_Settings_Handler {
    const OPTION_NAME = 'aicg_settings';
    
    public static function register_admin_menu() {
        add_menu_page(
            'AI Content Generator',
            'AI Content',
            'manage_options',
            'aicg-settings',
            [__CLASS__, 'render_settings_page'],
            'dashicons-edit'
        );
    }
    
    public static function render_settings_page() {
        // Carica template pagina impostazioni
        include_once AICG_PLUGIN_DIR . 'admin/settings-page.php';
    }
    
    public static function save_settings() {
        // Verifica nonce e permessi
        if (!isset($_POST['aicg_nonce']) || 
            !wp_verify_nonce($_POST['aicg_nonce'], 'aicg_settings')) {
            return;
        }
        
        // Salva API key e impostazioni
        $settings = [
            'api_service' => sanitize_text_field($_POST['api_service']),
            'openai_key' => sanitize_text_field($_POST['openai_key']),
            'anthropic_key' => sanitize_text_field($_POST['anthropic_key']),
            'deepseek_key' => sanitize_text_field($_POST['deepseek_key']),
            'openrouter_key' => sanitize_text_field($_POST['openrouter_key']),
            'openrouter_model' => sanitize_text_field($_POST['openrouter_model']),
            'default_instructions' => sanitize_textarea_field($_POST['default_instructions'])
        ];
        
        update_option(self::OPTION_NAME, $settings);
    }
    
    public static function handle_article_generation() {
        // Verifica nonce e permessi
        if (!isset($_POST['nonce']) || 
            !wp_verify_nonce($_POST['nonce'], 'aicg_nonce')) {
            wp_send_json_error('Nonce non valido', 403);
        }
        
        if (!current_user_can('manage_options')) {
            wp_send_json_error('Permessi insufficienti', 403);
        }
        
        // Recupera i dati dalla richiesta
        $titles = isset($_POST['titles']) ? array_map('sanitize_text_field', $_POST['titles']) : [];
        $category_id = isset($_POST['category']) ? intval($_POST['category']) : 0;
        $instructions = isset($_POST['instructions']) ? sanitize_textarea_field($_POST['instructions']) : '';
        $service = isset($_POST['api_service']) ? sanitize_text_field($_POST['api_service']) : '';
        
        if (empty($titles)) {
            wp_send_json_error('Nessun titolo fornito', 400);
        }
        
        if ($category_id <= 0) {
            wp_send_json_error('Categoria non valida', 400);
        }
        
        if (empty($service)) {
            wp_send_json_error('Servizio non selezionato', 400);
        }
        
        // Genera gli articoli
        $results = AICG_Content_Generator::generate_articles($titles, $instructions, $category_id, $service);
        
        if (is_wp_error($results)) {
            wp_send_json_error($results->get_error_message(), 500);
        }
        
        wp_send_json_success($results);
    }
    
    public static function get_setting($key) {
        $settings = get_option(self::OPTION_NAME, []);
        return $settings[$key] ?? '';
    }
}

// Registra azioni
add_action('admin_post_aicg_save_settings', ['AICG_Settings_Handler', 'save_settings']);
add_action('wp_ajax_aicg_generate_articles', ['AICG_Settings_Handler', 'handle_article_generation']);
