<?php
/**
 * Exemplo de arquivo de código personalizado
 * 
 * Adicione seus códigos personalizados aqui.
 * Este arquivo é carregado automaticamente pelo plugin Simple Code Plugin
 */

// Previne acesso direto ao arquivo
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

/**
 * Gera o HTML para o desconto no Pix.
 * @param WC_Product $product O objeto do produto.
 * @return string O HTML da informação do Pix.
 */
function wd_get_pix_html( $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) return '';
    
    // Para produtos variáveis, pegamos o preço da variação padrão ou da mais barata
    if ( $product->is_type('variable') ) {
        $variation_ids = $product->get_children();
        $variation_id = !empty($variation_ids) ? $variation_ids[0] : null;
        $variation = $variation_id ? wc_get_product($variation_id) : null;
        $preco_atual = $variation ? floatval($variation->get_price()) : 0;
    } else {
        $preco_atual = floatval( $product->get_price() );
    }

    if ( empty( $preco_atual ) ) return '';

    $percentual_desconto_pix = 5; // 5%
    $preco_com_desconto = $preco_atual * ( 1 - ( $percentual_desconto_pix / 100 ) );
    $preco_pix_formatado = wc_price( $preco_com_desconto );

    // Usamos uma classe simples para o JS encontrar o elemento, sem ID.
    return sprintf(
        '<span class="wd-info-pagamento wd-info-pix"><strong>%s</strong> com %d%% de desconto no Pix</span>',
        $preco_pix_formatado,
        $percentual_desconto_pix
    );
}

/**
 * Gera o HTML para o parcelamento no cartão.
 * @param WC_Product $product O objeto do produto.
 * @return string O HTML da informação de parcelamento.
 */
function wd_get_cartao_html( $product ) {
    if ( ! is_a( $product, 'WC_Product' ) ) return '';

    // Para produtos variáveis, pegamos o preço da variação padrão ou da mais barata
    if ( $product->is_type('variable') ) {
        $variation_ids = $product->get_children();
        $variation_id = !empty($variation_ids) ? $variation_ids[0] : null;
        $variation = $variation_id ? wc_get_product($variation_id) : null;
        $preco_atual = $variation ? floatval($variation->get_price()) : 0;
    } else {
        $preco_atual = floatval( $product->get_price() );
    }

    if ( empty( $preco_atual ) ) return '';

    $numero_maximo_parcelas = 12;
    $valor_parcela = $preco_atual / $numero_maximo_parcelas;
    $valor_parcela_formatado = wc_price( $valor_parcela );

    // Usamos uma classe simples para o JS encontrar o elemento, sem ID.
    return sprintf(
        '<span class="wd-info-pagamento wd-info-parcelamento">em até <strong>%dx de %s</strong></span>',
        $numero_maximo_parcelas,
        $valor_parcela_formatado
    );
}

// ---------------------------------------------------------------------------------
// 2. EXIBIÇÃO AUTOMÁTICA E SHORTCODES
// ---------------------------------------------------------------------------------

// Exibição automática nos cards da loja
add_action( 'woocommerce_after_shop_loop_item_title', 'wd_mostrar_parcelamento_no_card', 15 );
function wd_mostrar_parcelamento_no_card() {
    global $product;
    echo wd_get_cartao_html( $product );
}

// Função auxiliar para obter o produto para os shortcodes
function wd_get_product_for_shortcode( $atts ) {
    global $product;
    if ( ! empty( $atts['id'] ) ) {
        return wc_get_product( $atts['id'] );
    }
    return $product;
}

// Shortcode para Pagamento no Pix: [pagamento_pix]
add_shortcode( 'pagamento_pix', 'wd_shortcode_pix' );
function wd_shortcode_pix( $atts ) {
    $product_obj = wd_get_product_for_shortcode( shortcode_atts( array( 'id' => null ), $atts ) );
    return wd_get_pix_html( $product_obj );
}

// Shortcode para Pagamento no Cartão: [pagamento_cartao]
add_shortcode( 'pagamento_cartao', 'wd_shortcode_cartao' );
function wd_shortcode_cartao( $atts ) {
    $product_obj = wd_get_product_for_shortcode( shortcode_atts( array( 'id' => null ), $atts ) );
    return wd_get_cartao_html( $product_obj );
}

// ---------------------------------------------------------------------------------
// 3. JAVASCRIPT PARA ATUALIZAÇÃO EM TEMPO REAL (VERSÃO WOODMART)
// ---------------------------------------------------------------------------------
add_action('wp_footer', 'wd_combined_price_update_script');
function wd_combined_price_update_script() {
    if (!is_product()) return;
    
    global $product;
    if (!is_object($product) || !$product instanceof WC_Product) return;

    // Preço original para o caso de resetar a variação
    $original_price = $product->is_type('variable') ? wc_get_product($product->get_children('min')[0])->get_price() : $product->get_price();
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        // Aguarda um pouco para garantir que todos os scripts do WoodMart foram carregados
        setTimeout(function() {
            var $variationForm = $('.variations_form');
            if ($variationForm.length === 0) return;

            // --- INÍCIO DA LÓGICA DE ATUALIZAÇÃO DO PARCELAMENTO ---
            
            // Função para formatar o preço no padrão do WooCommerce (ex: R$ 1.234,56)
            function formatPrice(price) {
                return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(price);
            }

            // Função para atualizar os textos de pagamento
            function updatePaymentInfo(price) {
                if (typeof price !== 'number' || price <= 0) return;

                // Atualiza o parcelamento do Cartão
                var maxInstallments = 12;
                var installmentValue = price / maxInstallments;
                var installmentPriceFormatted = formatPrice(installmentValue);
                var cartaoHtml = 'em até <strong>' + maxInstallments + 'x de ' + installmentPriceFormatted + '</strong>';
                $('.wd-info-parcelamento').html(cartaoHtml);

                // Atualiza o desconto do PIX
                var pixDiscountPercent = 5;
                var pixPrice = price * (1 - (pixDiscountPercent / 100));
                var pixPriceFormatted = formatPrice(pixPrice);
                var pixHtml = '<strong>' + pixPriceFormatted + '</strong> com ' + pixDiscountPercent + '% de desconto no Pix';
                $('.wd-info-pix').html(pixHtml);
            }

            // --- FIM DA LÓGICA DE ATUALIZAÇÃO DO PARCELAMENTO ---

            // Escuta o evento 'show_variation' que o Woodmart usa
            $variationForm.on('show_variation', function(event, variation) {
                // 'variation.display_price' contém o número do preço da variação
                if (variation && typeof variation.display_price !== 'undefined') {
                    updatePaymentInfo(variation.display_price);
                }
            });
            
            // Escuta o evento 'hide_variation' (quando limpa a seleção)
            $variationForm.on('hide_variation', function() {
                // Usa o preço original do produto para resetar
                var originalPrice = <?php echo floatval($original_price); ?>;
                updatePaymentInfo(originalPrice);
            });

        }, 500); // Aguarda 500ms para garantir que tudo foi carregado
    });
    </script>
    <?php
}

add_action( 'wp_head', 'wd_estilo_css_pagamento' );
function wd_estilo_css_pagamento() {
    $css_personalizado = "
    <style>
        .wd-info-pagamento {
            display: block;
            color: black;
            font-size: 13px;
            line-height: 1.4;
        }
        /* Ajuste de margem para o parcelamento no grid */
        .product-grid-item .wd-info-parcelamento {
             margin-top: -10px !important;
        }
        .wd-info-pagamento .woocommerce-Price-amount.amount {
            color: black !important;
        }
    </style>
    ";
    echo $css_personalizado;
}

