<?php
/**
 * Plugin Name: AI Content Generator
 * Description: Genera articoli utilizzando API AI (ChatGPT, Cloude 4, Deepseek)
 * Version: 1.0.0
 * Author: Il tuo Nome
 * License: GPL2
 */

// Sicurezza: impedisce accesso diretto
defined('ABSPATH') || exit;

// Costanti plugin
define('AICG_VERSION', '1.0.0');
define('AICG_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('AICG_PLUGIN_URL', plugin_dir_url(__FILE__));

// Carica file necessari
require_once AICG_PLUGIN_DIR . 'includes/class-api-handler.php';
require_once AICG_PLUGIN_DIR . 'includes/content-generator.php';
require_once AICG_PLUGIN_DIR . 'admin/class-settings-handler.php';

// Registra hook di attivazione/disattivazione
register_activation_hook(__FILE__, 'aicg_activate');
register_deactivation_hook(__FILE__, 'aicg_deactivate');

function aicg_activate() {
    // Setup iniziale (es. crea tabelle DB)
}

function aicg_deactivate() {
    // Pulizia (es. rimuovi opzioni)
}

// Inizializza il plugin
add_action('plugins_loaded', 'aicg_init');
function aicg_init() {
    // Registra menu admin
    add_action('admin_menu', ['AICG_Settings_Handler', 'register_admin_menu']);
    
    // Carica assets
    add_action('admin_enqueue_scripts', function() {
        wp_enqueue_style('aicg-admin-css', AICG_PLUGIN_URL . 'assets/css/admin.css', [], AICG_VERSION);
        wp_enqueue_script('aicg-admin-js', AICG_PLUGIN_URL . 'assets/js/admin.js', ['jquery'], AICG_VERSION, true);
        
        // Localizza script
        wp_localize_script('aicg-admin-js', 'aicgData', [
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('aicg_nonce')
        ]);
    });
}
