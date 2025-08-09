<?php
/**
 * Plugin Name: Simple Code
 * Description: Um plugin simples para adicionar códigos personalizados sem modificar o functions.php.
 * Version: 1.0.0
 * Author: Leonardo
 * License: GPL2
 */

require 'plugin-update-checker/plugin-update-checker.php'; 
use YahnisElsts\PluginUpdateChecker\v5\PucFactory; 

$myUpdateChecker = PucFactory::buildUpdateChecker(
    'https://github.com/leoviajar/wc-simple-code',
    __FILE__,
    'simple-code-plugin.php'
);

// Previne acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// Declara compatibilidade com HPOS
add_action( 'before_woocommerce_init', function() {
    if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class ) ) {
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', __FILE__, true );
    }
});

// Inclui todos os arquivos .php do diretório custom-codes
$custom_codes_dir = plugin_dir_path( __FILE__ ) . 'custom-codes/';
if ( is_dir( $custom_codes_dir ) ) {
    foreach ( glob( $custom_codes_dir . '*.php' ) as $file ) {
        require_once( $file );
    }
}
