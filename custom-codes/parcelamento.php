<?php
/**
 * Código de Parcelamento Personalizado para WooCommerce com Painel de Controle
 *
 * Cria uma página de configurações em "WooCommerce > Parcelamento" para
 * definir as regras de exibição do parcelamento e do Pix para todo o site.
 */

// Previne acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// =================================================================================
// 1. CRIAÇÃO DA PÁGINA DE CONFIGURAÇÕES NO ADMIN
// =================================================================================

/**
 * Adiciona a página de configurações no menu do WooCommerce.
 */
add_action('admin_menu', 'wd_add_payment_settings_page');
function wd_add_payment_settings_page() {
    add_submenu_page(
        'woocommerce',
        'Configurações de Parcelamento e Pix',
        'Parcelamento',
        'manage_woocommerce',
        'wd-payment-settings',
        'wd_render_payment_settings_page'
    );
}

/**
 * Registra todas as configurações.
 */
add_action('admin_init', 'wd_register_payment_settings');
function wd_register_payment_settings() {
    // Grupo de opções
    $option_group = 'wd_payment_options_group';

    // Registra a opção do tipo de parcelamento
    register_setting($option_group, 'wd_installment_default_type', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => '12x'
    ]);

    // Registra a opção do tipo de exibição do Pix
    register_setting($option_group, 'wd_pix_display_type', [
        'type' => 'string',
        'sanitize_callback' => 'sanitize_text_field',
        'default' => 'calculated'
    ]);
}

/**
 * Renderiza o HTML da página de configurações.
 */
function wd_render_payment_settings_page() {
    ?>
    <div class="wrap">
        <h1>Configurações de Parcelamento e Pix</h1>
        <p>Escolha as regras de exibição que serão aplicadas a todos os produtos do site.</p>

        <form method="post" action="options.php">
            <?php
                settings_fields('wd_payment_options_group');
                $installment_option = get_option('wd_installment_default_type', '12x');
                $pix_option = get_option('wd_pix_display_type', 'calculated');
            ?>

            <h2 style="margin-top: 20px;">Configuração do Parcelamento no Cartão</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Regra Padrão</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="wd_installment_default_type" value="12x" <?php checked($installment_option, '12x'); ?> />
                                <span>Parcelamento em até <strong>12x</strong></span>
                            </label>
                              

                            <label>
                                <input type="radio" name="wd_installment_default_type" value="3x" <?php checked($installment_option, '3x'); ?> />
                                <span>Parcelamento em <strong>3x sem juros</strong></span>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <h2 style="margin-top: 20px;">Configuração do Pix</h2>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Exibição do Desconto no Pix</th>
                    <td>
                        <fieldset>
                            <label>
                                <input type="radio" name="wd_pix_display_type" value="calculated" <?php checked($pix_option, 'calculated'); ?> />
                                <span>Exibir valor com desconto (ex: "R$ 95,00 com 5% de desconto")</span>
                            </label>
                              

                            <label>
                                <input type="radio" name="wd_pix_display_type" value="static" <?php checked($pix_option, 'static'); ?> />
                                <span>Exibir apenas texto do desconto (ex: "No Pix (5% de Desconto)")</span>
                            </label>
                        </fieldset>
                    </td>
                </tr>
            </table>

            <?php submit_button('Salvar Alterações'); ?>
        </form>
    </div>
    <?php
}

// =================================================================================
// 2. LÓGICA DE EXIBIÇÃO BASEADA NAS OPÇÕES SALVAS
// =================================================================================

/**
 * Exibe o parcelamento e o pix nos cards da loja.
 */
add_action( 'woocommerce_after_shop_loop_item_title', 'wd_mostrar_info_pagamento_global', 15 );
function wd_mostrar_info_pagamento_global() {
    global $product;
    // Você pode decidir a ordem ou se quer mostrar ambos.
    // Por padrão, mostraremos apenas o parcelamento no grid para não poluir.
    echo wd_get_cartao_html_global( $product );
}

/**
 * Gera o HTML do parcelamento de acordo com a opção global.
 */
function wd_get_cartao_html_global( $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) return '';
    $preco_atual = wd_get_product_price($product);
    if ( empty( $preco_atual ) ) return '';

    $tipo_parcelamento = get_option('wd_installment_default_type', '12x');

    if ( $tipo_parcelamento === '3x' ) {
        $numero_parcelas = 3;
        $valor_parcela = $preco_atual / $numero_parcelas;
        return sprintf(
            '<span class="wd-info-pagamento wd-info-parcelamento">em até <strong>%dx de %s</strong> sem juros</span>',
            $numero_parcelas, wc_price($valor_parcela)
        );
    } else {
        $numero_parcelas = 12;
        $valor_parcela = $preco_atual / $numero_parcelas;
        return sprintf(
            '<span class="wd-info-pagamento wd-info-parcelamento">em até <strong>%dx de %s</strong></span>',
            $numero_parcelas, wc_price($valor_parcela)
        );
    }
}

/**
 * Gera o HTML do Pix de acordo com a opção global.
 */
function wd_get_pix_html_global( $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) return '';
    
    $tipo_pix = get_option('wd_pix_display_type', 'calculated');

    if ( $tipo_pix === 'static' ) {
        return '<span class="wd-info-pagamento wd-info-pix">No Pix <strong>(5% de Desconto)</strong></span>';
    } else {
        $preco_atual = wd_get_product_price($product);
        if ( empty( $preco_atual ) ) return '';
        
        $percentual_desconto_pix = 5;
        $preco_com_desconto = $preco_atual * ( 1 - ( $percentual_desconto_pix / 100 ) );
        return sprintf(
            '<span class="wd-info-pagamento wd-info-pix"><strong>%s</strong> com %d%% de desconto no Pix</span>',
            wc_price($preco_com_desconto), $percentual_desconto_pix
        );
    }
}

// =================================================================================
// 3. SHORTCODES E FUNÇÕES AUXILIARES
// =================================================================================

// Shortcode [pagamento_cartao]
add_shortcode( 'pagamento_cartao', 'wd_shortcode_cartao_global' );
function wd_shortcode_cartao_global( $atts ) {
    global $product;
    $p = !empty($atts['id']) ? wc_get_product($atts['id']) : $product;
    return wd_get_cartao_html_global($p);
}

// Shortcode [pagamento_pix]
add_shortcode( 'pagamento_pix', 'wd_shortcode_pix_global' );
function wd_shortcode_pix_global( $atts ) {
    global $product;
    $p = !empty($atts['id']) ? wc_get_product($atts['id']) : $product;
    return wd_get_pix_html_global($p);
}

/**
 * Função auxiliar para pegar o preço de um produto, considerando variações.
 */
function wd_get_product_price( $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) return 0;
    if ( $product->is_type('variable') ) {
        $prices = $product->get_variation_prices(true);
        return !empty($prices['price']) ? floatval(min($prices['price'])) : 0;
    }
    return floatval($product->get_price());
}

// =================================================================================
// 4. JAVASCRIPT E CSS
// =================================================================================

add_action('wp_footer', 'wd_global_price_update_script');
function wd_global_price_update_script() {
    if (!is_product()) return;
    global $product;
    if (!is_object($product) || !$product instanceof WC_Product) return;

    $original_price = wd_get_product_price($product);
    $installment_type = get_option('wd_installment_default_type', '12x');
    $pix_type = get_option('wd_pix_display_type', 'calculated');
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        setTimeout(function() {
            var $variationForm = $('.variations_form');
            if ($variationForm.length === 0) return;

            var installmentType = '<?php echo esc_js($installment_type); ?>';
            var pixType = '<?php echo esc_js($pix_type); ?>';

            function formatPrice(price) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
            }

            function updatePaymentInfo(price) {
                if (typeof price !== 'number' || price <= 0) return;

                // Atualiza parcelamento
                var installments = (installmentType === '3x') ? 3 : 12;
                var installmentValue = price / installments;
                var installmentHtml = 'em até <strong>' + installments + 'x de ' + formatPrice(installmentValue) + '</strong>' + (installmentType === '3x' ? ' sem juros' : '');
                $('.wd-info-parcelamento').html(installmentHtml);

                // Atualiza Pix (apenas se for do tipo calculado)
                if (pixType === 'calculated') {
                    var pixDiscountPercent = 5;
                    var pixPrice = price * (1 - (pixDiscountPercent / 100));
                    var pixHtml = '<strong>' + formatPrice(pixPrice) + '</strong> com ' + pixDiscountPercent + '% de desconto no Pix';
                    $('.wd-info-pix').html(pixHtml);
                }
            }

            $variationForm.on('show_variation', function(event, variation) {
                if (variation && typeof variation.display_price !== 'undefined') {
                    updatePaymentInfo(variation.display_price);
                }
            });
            
            $variationForm.on('hide_variation', function() {
                var originalPrice = <?php echo floatval($original_price); ?>;
                if (originalPrice > 0) {
                    updatePaymentInfo(originalPrice);
                }
            });
        }, 500);
    });
    </script>
    <?php
}

add_action( 'wp_head', 'wd_global_estilo_css' );
function wd_global_estilo_css() {
    echo "
    <style>
        .wd-info-pagamento { display: block; color: black; font-size: 13px; line-height: 1.4; }
        .product-grid-item .wd-info-pagamento { margin-top: -10px !important; }
        .wd-info-pagamento .woocommerce-Price-amount.amount { color: black !important; }
    </style>
    ";
}
