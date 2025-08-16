<?php
/* -- Remover etapa de login Checkout -- */
add_filter( 'wc_smart_checkout_steps', function ( $steps ) {
  unset( $steps['login'] );

  $login_step = array_search( 'login', $steps['billing_profile']['allowedSteps'] );

  if ( false !== $login_step ) {
    unset( $steps['billing_profile']['allowedSteps'][ $login_step ] );
  }

  return $steps;
}, 100000 );

add_filter( 'option_wc_smart_checkout_login_type', function () {
  return 'custom_screen';
} );

add_action( 'wp_head', function () { ?>
  <style>
    body.smart-checkout-login-custom_screen .checkout-container #billing_email_field {
      display: block !important;
    }
  </style><?php
}, 1000000 );

add_filter('woocommerce_registration_error_email_exists', '__return_false');
add_filter('woocommerce_checkout_registration_required', '__return_false');


/* -- Placeholder Checkout -- */

add_filter( 'woocommerce_checkout_fields', 'custom_override_checkout_labels_and_placeholders' );

function custom_override_checkout_labels_and_placeholders( $fields ) {
    // Alterando os placeholders dos campos de faturamento
    $fields['billing']['billing_first_name']['placeholder'] = 'ex.: Maria Almeida Cruz';
    $fields['billing']['billing_last_name']['placeholder'] = 'ex.: Almeida Cruz';
    $fields['billing']['billing_email']['placeholder'] = 'ex.: maria@gmail.com';
    $fields['billing']['billing_phone']['placeholder'] = 'ex.: (00) 00000-0000';
    $fields['billing']['billing_cpf']['placeholder'] = '000.000.000-00';
    $fields['shipping']['shipping_address_2']['placeholder'] = 'Apartamento, suíte, sala';
    
    // Alterando os labels dos campos de faturamento
    $fields['billing']['billing_email']['label'] = 'E-mail';

    return $fields;
}

/* Campos personalizados */
add_filter( 'woocommerce_checkout_fields', 'customize_billing_phone_field' );

function customize_billing_phone_field( $fields ) {
    // Modifica o campo de telefone
    $fields['billing']['billing_phone']['class'] = array('form-row-wide'); // Define a classe do campo de entrada
    $fields['billing']['billing_phone']['placeholder'] = '(00) 00000-0000'; // Atualiza o placeholder
    $fields['billing']['billing_phone']['label'] = 'Celular / WhatsApp'; // Atualiza o label
    $fields['billing']['billing_first_name']['label'] = 'Nome completo'; // Atualiza o label

    // Adiciona o prefixo "+55" fora do campo de telefone usando CSS
    add_action( 'woocommerce_after_checkout_billing_form', 'add_prefix_to_phone' );
    return $fields;
}

function add_prefix_to_phone() {
    if ( is_checkout() ) : ?>
        <style>
        
        .phone-input-container {
    display: flex;
    align-items: center;
    overflow: hidden;
}

.country-code {
    background: #f4f6f8;
    border-radius: 5px 0 0 5px;
    border: 1px solid #D0D0D0;
    border-right: none;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 85px;
    font-size: 13px;
    font-weight: 500;
    height: 45px;
    color: #333;
}

.input-text {
  
  
    border-radius: 0px; /* Remove bordas arredondadas */
    outline: none;
    padding: 0.4em; /* Espaçamento interno */
    height: 40px; /* Altura para corresponder ao prefixo */
    font-size: 13px; /* Ajuste do tamanho da fonte */
    line-height: 20px; /* Ajuste da altura da linha */
    box-sizing: border-box; /* Garante que o padding não afete o tamanho total */
}


        </style>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Move o campo de telefone para dentro de um contêiner personalizado
                var phoneField = document.getElementById('billing_phone');
                phoneField.outerHTML = '<div class="phone-input-container"><span class="country-code">+55</span>' + phoneField.outerHTML + '</div>';
            });
        </script>
    <?php endif;
}

add_filter( 'woocommerce_checkout_fields', 'reorder_checkout_fields' );

function reorder_checkout_fields( $fields ) {
    // Muda a ordem do campo CPF e Telefone
    $fields['billing']['billing_cpf'] = $fields['billing']['billing_cpf']; 
    $fields['billing']['billing_phone'] = $fields['billing']['billing_phone'];
    
    return $fields;
}

add_filter('woocommerce_gateway_icon', 'custom_payment_gateway_icons_appmax', 10, 2);

function custom_payment_gateway_icons_appmax( $icon, $gateway_id = '' ) {
    
    // Lista de gateways que devem receber os ícones de cartão
    $target_gateways = array(
        'pagamentos_para_woocommerce_com_appmax_credit_card', // ID correto da Appmax
        'loja5_woo_mercadopago'                               // ID do gateway antigo (para manter a compatibilidade)
    );

    // Verifica se o gateway atual é um dos que queremos modificar
    if ( in_array( $gateway_id, $target_gateways ) ) {
        
        $icon_html = '<span style="float:left; margin-left: -3px; line-height: 1;">';

        // Definir bandeiras com links SVG
        $bandeiras = array(
            'amex'       => 'https://github.bubbstore.com/svg/card-amex.svg',
            'visa'       => 'https://github.bubbstore.com/svg/card-visa.svg',
            'diners'     => 'https://github.bubbstore.com/svg/card-diners.svg',
            'mastercard' => 'https://github.bubbstore.com/svg/card-mastercard.svg',
            'hipercard'  => 'https://github.bubbstore.com/svg/card-hipercard.svg',
            'elo'        => 'https://github.bubbstore.com/svg/card-elo.svg'
         );

        // Iterar pelas bandeiras e gerar o HTML das imagens
        foreach($bandeiras as $bandeira_nome => $link_svg){
            $icon_html .= '<img style="cursor:pointer; float:left; max-height:25px; margin:3px;" title="' . ucfirst($bandeira_nome) . '" class="imagem_bandeira_gateway ' . $bandeira_nome . '" src="' . $link_svg . '" alt="' . ucfirst($bandeira_nome) . '">&nbsp;';
        }

        $icon_html .= '</span>';
        
        // Retorna os novos ícones
        return $icon_html;
    }
    
    // Se não for o gateway da Appmax ou do Loja5, retorna o ícone original
    return $icon;
}


add_filter('woocommerce_gateway_title', 'customize_pix_discount_text_reforçado', 99, 2);

function customize_pix_discount_text_reforçado( $title, $gateway_id ) {
    
    // Alvo: Apenas o gateway de PIX específico
    if ( 'loja5_woo_mercadopago_pix' === $gateway_id ) {
        
        // A expressão regular agora procura por:
        // <small> seguido de (
        // um grupo de números com % (ex: 5%)
        // um espaço e a palavra "off"
        // ) seguido de </small>
        // A flag 'i' no final torna a busca insensível a maiúsculas/minúsculas (case-insensitive).
        $padrao = '/<small>\((\d+%) off\)<\/small>/i';
        
        // O texto de substituição usa o grupo capturado ($1) e adiciona o novo texto.
        $substituicao = '<small>$1 DE DESCONTO</small>';
        
        // Executa a substituição
        $novo_titulo = preg_replace( $padrao, $substituicao, $title );
        
        // Verifica se a substituição ocorreu para evitar retornar um valor nulo em caso de erro
        if ( $novo_titulo !== null ) {
            return $novo_titulo;
        }
    }
    
    // Retorna o título original se não for o gateway correto ou se houver erro
    return $title;
}

add_action( 'wp_footer', 'order_bump_variation_image_update_script', 100 );

function order_bump_variation_image_update_script() {
    if ( is_checkout() ) {
        ?>
        <script type="text/javascript">
            jQuery(document).ready(function($) {
                
                function updateVariationImage(variation) {
                    var newImageSrc = variation.image.src; 
                    var newImageSrcSet = variation.image.srcset; 
                    
                    $('.order-bump-body-product-aside-image img').attr('src', newImageSrc);
                    $('.order-bump-body-product-aside-image img').attr('srcset', newImageSrcSet);
                }

                $(document).on('change', 'select#pa_cor.order-bump-variation-attributes', function() {
                    var $variationForm = $(this).closest('.order-bump-body-main-variation');
                    
                    var productVariations = $variationForm.data('product_variations');
                    
                    var selectedValue = $(this).val();
                    
                    if (selectedValue) {
                        $.each(productVariations, function(index, variation) {
                            if (variation.attributes['attribute_pa_cor'] === selectedValue) { 
                                updateVariationImage(variation); 
                                return false; 
                            }
                        });
                    }
                });
            });
        </script>
        <?php
    }
}

function custom_wc_smart_checkout_order_bump_image( $image, $offer, $item_data ) {
    if ( isset( $item_data['variation_id'] ) && ! empty( $item_data['variation_id'] ) ) {
        $_variation = wc_get_product( $item_data['variation_id'] ); 
        if ( $_variation ) {
            $image = $_variation->get_image();
        }
    }
    
    return $image; 
}

add_filter( 'wc_smart_checkout_order_bump_image', 'custom_wc_smart_checkout_order_bump_image', 10, 3 );

add_filter( 'woocommerce_cart_item_name', function( $product_name, $cart_item, $cart_item_key ) {
    if ( isset( $cart_item['order_bump'], $cart_item['order_bump_sale_price'] ) && isset( $cart_item['data'] ) ) {
        // Recupera o HTML salvo como metadado
        $offer_acquired_html = $cart_item['data']->get_meta( '_offer_acquired_html' );
        
        if ( $offer_acquired_html ) {
            // Adiciona o HTML antes do nome do produto
            $product_name = $offer_acquired_html . $product_name;
        }
    }
    return $product_name;
}, 10, 3 );

// Função para adicionar a mensagem de desconto abaixo do input de telefone no checkout
function add_pix_discount_message_below_phone_field( $checkout ) {
    echo '<div class="discount-info shake">
            <div class="discount-info-icon icon-pix-store-negative"></div>
            <div class="discount-info-description">
                <p>
                    Você ganhou
                    <span class="discount-percentage">
                        5% de desconto
                    </span>
                    <br>
                    pagando com Pix
                </p>
            </div>
          </div>';
}

// Adiciona a mensagem de desconto após o campo de telefone
add_action( 'woocommerce_after_checkout_billing_form', 'add_pix_discount_message_below_phone_field', 10 );


// Remover campo sobrenome
add_filter('woocommerce_checkout_fields', 'remove_billing_last_name_field');

function remove_billing_last_name_field($fields) {
    // Remove o campo de sobrenome da seção de faturamento
    unset($fields['billing']['billing_last_name']);
    return $fields;
}

// Garantir que o campo não seja renderizado no checkout
add_action('wp_enqueue_scripts', 'remove_billing_last_name_with_js');

function remove_billing_last_name_with_js() {
    if (is_checkout()) {
        ?>
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var lastNameField = document.getElementById('billing_last_name');
                if (lastNameField) {
                    lastNameField.closest('.form-row').remove();
                }
            });
        </script>
        <?php
    }
}

// Remover apenas a validação do campo billing_last_name no Smart Checkout
add_filter( 'wc_smart_checkout_steps', function ( $steps ) {
    // Verificar se existe a etapa 'billing_profile'
    if ( isset( $steps['billing_profile'] ) ) {
        // Iterar sobre os campos da etapa 'billing_profile'
        foreach ( $steps['billing_profile']['fields'] as $key => $field ) {
            if ( $field === 'billing_last_name' ) {
                // Remover o campo da validação obrigatória
                if ( isset( $steps['billing_profile']['validations'][ $field ] ) ) {
                    unset( $steps['billing_profile']['validations'][ $field ] );
                }

                // Opcional: Tornar o campo não obrigatório diretamente no array
                $steps['billing_profile']['fields'][$key] = [
                    'id' => 'billing_last_name',
                    'label' => __( 'Sobrenome (opcional)', 'woocommerce' ),
                    'required' => false, // Define como não obrigatório
                ];
            }
        }
    }

    return $steps;
}, 15 );

add_filter( 'woocommerce_coupon_error','coupon_error_message_change',10,3 );

function coupon_error_message_change($err, $err_code, $WC_Coupon) {
    switch ( $err_code ) {
        case $WC_Coupon::E_WC_COUPON_NOT_EXIST:
            $err = 'Cupom não encontrado';
    }
    return $err;
}


?>