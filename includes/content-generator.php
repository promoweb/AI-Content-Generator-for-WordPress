<?php
if (!defined('ABSPATH')) exit;

class AICG_Content_Generator {
    public static function create_post($title, $content, $category_id) {
        // Crea l'articolo come bozza
        $post_data = [
            'post_title' => $title,
            'post_content' => $content,
            'post_status' => 'draft',
            'post_author' => get_current_user_id(),
            'post_category' => [$category_id]
        ];
        
        $post_id = wp_insert_post($post_data);
        
        if (is_wp_error($post_id)) {
            return $post_id;
        }
        return $post_id;
    }
    
    public static function generate_articles($titles, $instructions, $category_id, $service, $model) {
        $settings = get_option(AICG_Settings_Handler::OPTION_NAME, []);
        $api_key = '';
        
        // Ottieni la chiave API in base al servizio
        switch ($service) {
            case 'openai':
                $api_key = $settings['openai_key'];
                break;
            case 'anthropic':
                $api_key = $settings['anthropic_key'];
                break;
            case 'deepseek':
                $api_key = $settings['deepseek_key'];
                break;
            case 'openrouter':
                $api_key = $settings['openrouter_key'];
                break;
            default:
                return new WP_Error('invalid_service', 'Servizio API non valido');
        }
        
        $results = [];
        
        foreach ($titles as $title) {
            $title = trim($title);
            if (empty($title)) continue;
            
            $content = AICG_API_Handler::generate_content($title, $instructions, $service, $api_key, $model);
            
            if (is_wp_error($content)) {
                $results[] = [
                    'title' => $title,
                    'success' => false,
                    'error' => $content->get_error_message()
                ];
                continue;
            }
            
            $post_id = self::create_post($title, $content, $category_id);
            
            if (is_wp_error($post_id)) {
                $results[] = [
                    'title' => $title,
                    'success' => false,
                    'error' => $post_id->get_error_message()
                ];
            } else {
                $results[] = [
                    'title' => $title,
                    'success' => true,
                    'post_id' => $post_id,
                    'edit_link' => get_edit_post_link($post_id)
                ];
            }
        }
        
        return $results;
    }
}

// Handle model fetching via AJAX
add_action('wp_ajax_aicg_get_models', 'aicg_ajax_get_models');
function aicg_ajax_get_models() {
    // Verify nonce?
    $provider = sanitize_text_field($_POST['provider']);
    $api_key = sanitize_text_field($_POST['api_key']);
    
    $models = AICG_API_Handler::get_models($provider, $api_key);
    
    if (is_wp_error($models)) {
        wp_send_json_error($models->get_error_message());
    } else {
        wp_send_json_success($models);
    }
}
