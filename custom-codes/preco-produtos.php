<?php
// 1. Modificar a exibição inicial do preço (remover faixa de preço)
add_filter('woocommerce_variable_price_html', 'woodmart_custom_variable_price_display', 20, 2);
function woodmart_custom_variable_price_display($price, $product) {
    // Pega o preço da variação mais barata
    $min_price_variation_id = $product->get_children('min')[0];
    $variation = wc_get_product($min_price_variation_id);
    
    if (!$variation) {
        return $price;
    }
    
    // Retorna apenas o preço da variação mais barata
    return $variation->get_price_html();
}

// 2. Script JavaScript robusto para atualização de preço
add_action('wp_footer', 'woodmart_robust_price_update_script');
function woodmart_robust_price_update_script() {
    if (!is_product()) return;
    
    global $product;
    if (!is_object($product) || !$product instanceof WC_Product || !$product->is_type('variable')) return;
    
    ?>
    <script type="text/javascript">
    jQuery(document).ready(function($) {
        console.log('WoodMart Price Update Script - Iniciado');
        
        // Aguarda um pouco para garantir que todos os scripts do WoodMart foram carregados
        setTimeout(function() {
            
            // Encontra o formulário de variações
            var $variationForm = $('.variations_form');
            
            if ($variationForm.length === 0) {
                console.log('Formulário de variações não encontrado');
                return;
            }
            
            console.log('Formulário de variações encontrado');
            
            // Tenta encontrar o elemento de preço principal usando os mesmos seletores do WoodMart
            var $mainPrice = null;
            
            // Seletor 1: Para layouts normais
            $mainPrice = $variationForm.parent().find('> .price, > div > .price, > .price > .price').first();
            
            // Seletor 2: Para layouts com builder
            if ($mainPrice.length === 0 || $('.wd-content-layout').hasClass('wd-builder-on')) {
                $mainPrice = $variationForm.parents('.single-product-page').find('.wd-single-price .price').first();
            }
            
            // Seletor 3: Fallback para outros layouts
            if ($mainPrice.length === 0) {
                $mainPrice = $('.summary .price').first();
            }
            
            // Seletor 4: Último fallback
            if ($mainPrice.length === 0) {
                $mainPrice = $('.product-summary .price').first();
            }
            
            if ($mainPrice.length === 0) {
                console.log('Elemento de preço principal não encontrado');
                return;
            }
            
            console.log('Elemento de preço principal encontrado:', $mainPrice);
            
            // Guarda o HTML original do preço
            var originalPriceHtml = $mainPrice.html();
            console.log('Preço original guardado:', originalPriceHtml);
            
            // Escuta o evento show_variation
            $variationForm.on('show_variation', function(event, variation) {
                console.log('Evento show_variation disparado', variation);
                
                if (variation.price_html && variation.price_html.length > 1) {
                    console.log('Atualizando preço principal com:', variation.price_html);
                    $mainPrice.html(variation.price_html);
                } else {
                    console.log('price_html não disponível ou vazio');
                }
            });
            
            // Escuta o evento hide_variation (quando limpa a seleção)
            $variationForm.on('hide_variation', function() {
                console.log('Evento hide_variation disparado - restaurando preço original');
                $mainPrice.html(originalPriceHtml);
            });
            
            // Escuta cliques no botão "Limpar"
            $variationForm.on('click', '.reset_variations', function() {
                console.log('Botão limpar clicado - restaurando preço original');
                setTimeout(function() {
                    $mainPrice.html(originalPriceHtml);
                }, 50);
            });
            
            console.log('Event listeners configurados com sucesso');
            
        }, 500); // Aguarda 500ms para garantir que tudo foi carregado
        
    });
    </script>
    <?php
}

// 3. CSS para esconder o preço redundante da variação
add_action('wp_head', 'hide_variation_price_css');
function hide_variation_price_css() {
    if (is_product()) {
        ?>
        <style>
        .single-product .woocommerce-variation-price {
            display: none !important;
        }
        </style>
        <?php
    }
}