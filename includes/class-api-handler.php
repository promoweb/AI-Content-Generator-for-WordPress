<?php
if (!defined('ABSPATH')) exit;

class AICG_API_Handler {
    const MAX_RETRIES = 3;
    const RETRY_DELAY = 2; // secondi
    
    public static function generate_content($title, $instructions, $service, $api_key, $model) {
        $method = 'generate_' . $service . '_content';
        
        if (!method_exists(__CLASS__, $method)) {
            return new WP_Error('invalid_service', 'Servizio API non supportato');
        }
        
        $retry_count = 0;
        
        while ($retry_count <= self::MAX_RETRIES) {
            $response = call_user_func([__CLASS__, $method], $title, $instructions, $api_key, $model);
            
            if (!is_wp_error($response)) {
                return $response;
            }
            
            $retry_count++;
            sleep(self::RETRY_DELAY * $retry_count);
        }
        
        return $response;
    }
    
    private static function generate_openai_content($title, $instructions, $api_key, $model) {
        $endpoint = 'https://api.openai.com/v1/chat/completions';
        
        $messages = [
            [
                'role' => 'system',
                'content' => "Sei un esperto copywriter. Scrivi un articolo ben strutturato in HTML basandoti sul titolo e le istruzioni fornite."
            ],
            [
                'role' => 'user',
                'content' => "Titolo: $title\nIstruzioni: $instructions"
            ]
        ];
        
        $body = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        return self::make_api_request($endpoint, $api_key, $body);
    }
    
    private static function generate_anthropic_content($title, $instructions, $api_key, $model) {
        $endpoint = 'https://api.anthropic.com/v1/messages';
        
        $prompt = "\n\nHuman: Scrivi un articolo ben strutturato in HTML basandoti su questo titolo e istruzioni:\n"
                . "Titolo: $title\nIstruzioni: $instructions\n\nAssistant:";
        
        $body = [
            'model' => $model,
            'max_tokens' => 2000,
            'temperature' => 0.7,
            'system' => 'Sei un esperto copywriter.',
            'messages' => [
                [
                    'role' => 'user',
                    'content' => $prompt
                ]
            ]
        ];
        
        return self::make_api_request($endpoint, $api_key, $body);
    }
    
    private static function generate_deepseek_content($title, $instructions, $api_key, $model) {
        $endpoint = 'https://api.deepseek.com/v1/chat/completions';
        
        $messages = [
            [
                'role' => 'system',
                'content' => "Sei un esperto copywriter. Scrivi un articolo ben strutturato in HTML basandoti sul titolo e le istruzioni fornite."
            ],
            [
                'role' => 'user',
                'content' => "Titolo: $title\nIstruzioni: $instructions"
            ]
        ];
        
        $body = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        return self::make_api_request($endpoint, $api_key, $body);
    }
    
    private static function generate_openrouter_content($title, $instructions, $api_key, $model) {
        $endpoint = 'https://openrouter.ai/api/v1/chat/completions';
        
        $messages = [
            [
                'role' => 'system',
                'content' => "Sei un esperto copywriter. Scrivi un articolo ben strutturato in HTML basandoti sul titolo e le istruzioni fornite."
            ],
            [
                'role' => 'user',
                'content' => "Titolo: $title\nIstruzioni: $instructions"
            ]
        ];
        
        $body = [
            'model' => $model,
            'messages' => $messages,
            'temperature' => 0.7,
            'max_tokens' => 2000
        ];
        
        return self::make_api_request($endpoint, $api_key, $body);
    }
    
    public static function get_models($provider, $api_key) {
        $endpoints = [
            'openai' => 'https://api.openai.com/v1/models',
            'anthropic' => 'https://api.anthropic.com/v1/models',
            'deepseek' => 'https://api.deepseek.com/v1/models',
            'openrouter' => 'https://openrouter.ai/api/v1/models'
        ];
        
        if (!isset($endpoints[$provider])) {
            return new WP_Error('invalid_provider', 'Provider non valido');
        }
        
        $response = wp_remote_get($endpoints[$provider], [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
                'Content-Type' => 'application/json'
            ],
            'timeout' => 15
        ]);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code !== 200) {
            $error_message = $response_body['error']['message'] ?? $response_body['error'] ?? 'Errore sconosciuto';
            return new WP_Error('api_error', "Errore API ($response_code): $error_message");
        }
        
        // Process models based on provider
        $models = [];
        switch ($provider) {
            case 'openai':
                $models = array_map(function($model) {
                    return [
                        'id' => $model['id'],
                        'name' => str_replace('gpt-', 'GPT-', $model['id'])
                    ];
                }, $response_body['data']);
                break;
            case 'anthropic':
                $models = array_map(function($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name']
                    ];
                }, $response_body['models']);
                break;
            case 'deepseek':
                $models = array_map(function($model) {
                    return [
                        'id' => $model['id'],
                        'name' => strtoupper(str_replace('deepseek-', '', $model['id']))
                    ];
                }, $response_body['data']);
                break;
            case 'openrouter':
                $models = array_map(function($model) {
                    return [
                        'id' => $model['id'],
                        'name' => $model['name']
                    ];
                }, $response_body['data']);
                
                // Add DeepSeek free model
                $models[] = [
                    'id' => 'deepseek-r1-0528:free',
                    'name' => 'DeepSeek R1 0528 (Free)'
                ];
                break;
        }
        
        return $models;
    }
    
    private static function make_api_request($endpoint, $api_key, $body) {
        $args = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'HTTP-Referer' => get_site_url() // Richiesto da OpenRouter
            ],
            'body' => json_encode($body),
            'timeout' => 30
        ];
        
        $response = wp_remote_post($endpoint, $args);
        
        if (is_wp_error($response)) {
            return $response;
        }
        
        $response_code = wp_remote_retrieve_response_code($response);
        $response_body = json_decode(wp_remote_retrieve_body($response), true);
        
        if ($response_code !== 200) {
            $error_message = $response_body['error']['message'] ?? $response_body['error'] ?? 'Errore sconosciuto';
            return new WP_Error('api_error', "Errore API ($response_code): $error_message");
        }
        
        // Estrae il contenuto dalla risposta in base al servizio
        if (strpos($endpoint, 'openai.com') !== false || strpos($endpoint, 'deepseek.com') !== false) {
            return $response_body['choices'][0]['message']['content'] ?? '';
        } elseif (strpos($endpoint, 'anthropic.com') !== false) {
            return $response_body['content'][0]['text'] ?? '';
        }
        
        return '';
    }
}
