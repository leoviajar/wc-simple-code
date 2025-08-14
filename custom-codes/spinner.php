/**
 * Função principal para adicionar o spinner
 */
function wc_spinner_robusto_init() {
    // Verificar se estamos em páginas relevantes do WooCommerce
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_shop() && 
        !is_product_category() && !is_product_tag() && !is_product()) {
        return;
    }
    
    // Adicionar CSS e JavaScript no footer
    add_action('wp_footer', 'wc_spinner_robusto_output', 999);
}
add_action('wp', 'wc_spinner_robusto_init');

/**
 * Função que gera o CSS e JavaScript do spinner
 */
function wc_spinner_robusto_output() {
    ?>
    <!-- CSS do Spinner WooCommerce -->
    <style id="wc-spinner-css">
        /* CSS para o Spinner do WooCommerce - Desenvolvido do Zero */

        /* Overlay que cobre toda a tela */
        .wc-spinner-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #ffffffda;
            display: none; /* Inicialmente oculto */
            justify-content: center;
            align-items: center;
            z-index: 999999; /* Z-index muito alto para ficar acima de tudo */
            backdrop-filter: blur(2px); /* Efeito de desfoque no fundo */
            opacity: 0;
            transition: opacity 0.3s ease-in-out;
        }

        /* Quando o overlay está ativo, mostra como flex */
        .wc-spinner-overlay.active {
            display: flex;
            opacity: 1;
        }

        /* Container do spinner */
        .wc-spinner-container {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            background-color: #ffffffda;
            transform: scale(0.8);
            transition: transform 0.3s ease-in-out;
        }

        .wc-spinner-overlay.active .wc-spinner-container {
            transform: scale(1);
        }

        /* O spinner circular em si */
        .wc-spinner {
            width: 50px;
            height: 50px;
            border: 6px solid #f3f3f3;
            border-top: 6px solid var(--wd-primary-color); 
            border-radius: 50%;
            animation: wc-spin 1s linear infinite;
            margin-bottom: 15px;
        }

        /* Animação de rotação */
        @keyframes wc-spin {
            0% { 
                transform: rotate(0deg); 
            }
            100% { 
                transform: rotate(360deg); 
            }
        }

        /* Texto de carregamento */
        .wc-spinner-text {
            color: #333;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Oxygen-Sans, Ubuntu, Cantarell, "Helvetica Neue", sans-serif;
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            margin: 0;
            opacity: 0.8;
        }

        /* Responsividade para tablets */
        @media (max-width: 768px) {
            .wc-spinner-container {
                padding: 25px;
                margin: 20px;
            }
            
            .wc-spinner {
                width: 50px;
                height: 50px;
                border-width: 5px;
            }
            
            .wc-spinner-text {
                font-size: 15px;
            }
        }

        /* Responsividade para smartphones */
        @media (max-width: 480px) {
            .wc-spinner-container {
                padding: 20px;
                margin: 15px;
                border-radius: 8px;
            }
            
            .wc-spinner {
                width: 40px;
                height: 40px;
                border-width: 4px;
                margin-bottom: 12px;
            }
            
            .wc-spinner-text {
                font-size: 14px;
            }
        }

        /* Prevenção de scroll quando o spinner está ativo */
        body.wc-spinner-active {
            overflow: hidden;
        }
    </style>

    <!-- JavaScript do Spinner WooCommerce -->
    <script id="wc-spinner-js">
        jQuery(document).ready(function($) {
            'use strict';

            // Variáveis globais para controle do spinner
            var spinnerTimeout = null;
            var spinnerActive = false;
            var spinnerStartTime = null;
            var minDisplayTime = 2000; // Tempo mínimo de exibição: 2 segundos
            var maxDisplayTime = 20000; // Tempo máximo de exibição: 20 segundos

            // Textos que ativam o spinner (em português e inglês)
            var targetTexts = [
                // Português
                'adicionar ao carrinho',
                'adicionar no carrinho',
                'comprar agora',
                'finalizar compra',
                'finalização de compra',
                'continuar para finalização',
                'continuar para checkout',
                'finalizar pedido',
                'fazer pedido',
                'confirmar pedido',
                'processar pedido',
                'atualizar carrinho',
                'prosseguir para checkout',
                'ir para checkout',
                'add to cart',
                'add to basket',
                'proceed to checkout',
                'continue to checkout',
                'place order',
                'complete order',
                'process order',
                'checkout',
                'update cart',
                'purchase'
            ];

            /**
             * Função para mostrar o spinner
             */
            function showSpinner() {
                if (spinnerActive) {
                    return; // Já está ativo
                }

                console.log('WC Spinner: Mostrando spinner');
                
                spinnerActive = true;
                spinnerStartTime = Date.now();
                
                // Adicionar classe ao body para prevenir scroll
                $('body').addClass('wc-spinner-active');
                
                // Mostrar o overlay
                $('#wc-spinner-overlay').addClass('active');
                
                // Limpar timeout anterior se existir
                if (spinnerTimeout) {
                    clearTimeout(spinnerTimeout);
                }
                
                // Definir timeout máximo de segurança
                spinnerTimeout = setTimeout(function() {
                    console.log('WC Spinner: Timeout máximo atingido, escondendo spinner');
                    hideSpinner();
                }, maxDisplayTime);
            }

            /**
             * Função para esconder o spinner
             */
            function hideSpinner() {
                if (!spinnerActive) {
                    return; // Já está inativo
                }

                var elapsedTime = Date.now() - spinnerStartTime;
                
                // Se não passou o tempo mínimo, aguardar
                if (elapsedTime < minDisplayTime) {
                    var remainingTime = minDisplayTime - elapsedTime;
                    console.log('WC Spinner: Aguardando tempo mínimo (' + remainingTime + 'ms)');
                    
                    setTimeout(function() {
                        hideSpinner();
                    }, remainingTime);
                    return;
                }

                console.log('WC Spinner: Escondendo spinner após ' + elapsedTime + 'ms');
                
                spinnerActive = false;
                
                // Remover classe do body
                $('body').removeClass('wc-spinner-active');
                
                // Esconder o overlay
                $('#wc-spinner-overlay').removeClass('active');
                
                // Limpar timeout
                if (spinnerTimeout) {
                    clearTimeout(spinnerTimeout);
                    spinnerTimeout = null;
                }
            }

            /**
             * Função para criar o HTML do spinner
             */
            function createSpinnerHTML() {
                if ($('#wc-spinner-overlay').length > 0) {
                    return; // Já existe
                }

                var spinnerHTML = 
                '<div id="wc-spinner-overlay" class="wc-spinner-overlay">' +
                    '<div class="wc-spinner"></div>'
                '</div>';
               
                $('body').append(spinnerHTML);
                console.log('WC Spinner: HTML do spinner criado');
            }

            /**
             * Função para verificar se o texto do botão corresponde aos textos-alvo
             */
            function isTargetButton(buttonText) {
                if (!buttonText || typeof buttonText !== 'string') {
                    return false;
                }
                
                // Normalizar o texto: minúsculas, remover espaços extras e caracteres especiais
                var normalizedText = buttonText.toLowerCase().trim().replace(/\s+/g, ' ');
                
                // Verificar se algum dos textos-alvo está contido no texto do botão
                return targetTexts.some(function(targetText) {
                    return normalizedText.includes(targetText);
                });
            }

            /**
             * Função para extrair o texto de um botão
             */
            function getButtonText(button) {
                var $button = $(button);
                var text = '';
                
                if ($button.is('input')) {
                    // Para inputs, verificar value, placeholder e title
                    text = $button.val() || $button.attr('value') || $button.attr('placeholder') || $button.attr('title') || '';
                } else {
                    // Para outros elementos, verificar texto, title e aria-label
                    text = $button.text() || $button.attr('title') || $button.attr('aria-label') || '';
                    
                    // Se não encontrou texto, verificar em elementos filhos
                    if (!text.trim()) {
                        text = $button.find('span, i, em, strong').text() || '';
                    }
                }
                
                return text.trim();
            }

            /**
             * Função para adicionar listeners aos botões
             */
            function addButtonListeners() {
                // Seletores abrangentes para todos os tipos de botões
                var buttonSelectors = [
                    'button',
                    'input[type="submit"]',
                    'input[type="button"]',
                    'a.button',
                    '.button',
                    '[role="button"]'
                ];
                
                var allButtons = $(buttonSelectors.join(', '));
                
                allButtons.each(function() {
                    var $button = $(this);
                    var buttonText = getButtonText(this);
                    
                    if (isTargetButton(buttonText)) {
                        // Remover listener anterior para evitar duplicatas
                        $button.off('click.wcspinner');
                        
                        // Adicionar novo listener
                        $button.on('click.wcspinner', function(event) {
                            // Verificar se o botão não está desabilitado
                            if ($(this).is(':disabled') || $(this).hasClass('disabled')) {
                                return;
                            }
                            
                            console.log('WC Spinner: Clique detectado em botão com texto: "' + buttonText + '"');
                            showSpinner();
                        });
                        
                        console.log('WC Spinner: Listener adicionado para botão: "' + buttonText + '"');
                    }
                });
            }

            /**
             * Função para observar mudanças no DOM
             */
            function setupMutationObserver() {
                if (!window.MutationObserver) {
                    console.log('WC Spinner: MutationObserver não suportado');
                    return;
                }
                
                var observer = new MutationObserver(function(mutations) {
                    var shouldRescan = false;
                    
                    mutations.forEach(function(mutation) {
                        if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                            // Verificar se algum dos nós adicionados é um botão ou contém botões
                            for (var i = 0; i < mutation.addedNodes.length; i++) {
                                var node = mutation.addedNodes[i];
                                if (node.nodeType === 1) { // Element node
                                    var $node = $(node);
                                    if ($node.is('button, input[type="submit"], input[type="button"], .button') || 
                                        $node.find('button, input[type="submit"], input[type="button"], .button').length > 0) {
                                        shouldRescan = true;
                                        break;
                                    }
                                }
                            }
                        }
                    });
                    
                    if (shouldRescan) {
                        console.log('WC Spinner: Novos botões detectados, re-escaneando...');
                        setTimeout(addButtonListeners, 100);
                    }
                });
                
                observer.observe(document.body, {
                    childList: true,
                    subtree: true
                });
                
                console.log('WC Spinner: MutationObserver configurado');
            }

            /**
             * Função para configurar eventos de esconder o spinner
             */
            function setupHideEvents() {
                // Esconder spinner antes de descarregar a página
                $(window).on('beforeunload.wcspinner', function() {
                    hideSpinner();
                });
                
                // Esconder spinner em eventos específicos do WooCommerce
                $(document.body).on('added_to_cart.wcspinner', function() {
                    console.log('WC Spinner: Evento added_to_cart detectado');
                    setTimeout(hideSpinner, 1000);
                });
                
                $(document.body).on('updated_cart_totals.wcspinner', function() {
                    console.log('WC Spinner: Evento updated_cart_totals detectado');
                    hideSpinner();
                });
                
                $(document.body).on('checkout_error.wcspinner', function() {
                    console.log('WC Spinner: Evento checkout_error detectado');
                    hideSpinner();
                });
                
                // Esconder spinner em eventos AJAX genéricos (com delay)
                $(document).ajaxComplete(function(event, xhr, settings) {
                    if (spinnerActive) {
                        console.log('WC Spinner: AJAX completado, escondendo spinner em 2s');
                        setTimeout(function() {
                            if (spinnerActive) {
                                hideSpinner();
                            }
                        }, 2000);
                    }
                });
                
                $(document).ajaxError(function(event, xhr, settings) {
                    console.log('WC Spinner: Erro AJAX detectado');
                    setTimeout(hideSpinner, 1500);
                });
                
                // Esconder spinner quando a página for completamente carregada
                $(window).on('load.wcspinner', function() {
                    if (spinnerActive) {
                        console.log('WC Spinner: Página carregada, escondendo spinner');
                        setTimeout(hideSpinner, 1000);
                    }
                });
            }

            /**
             * Função de inicialização
             */
            function init() {
                console.log('WC Spinner: Inicializando...');
                
                // Criar HTML do spinner
                createSpinnerHTML();
                
                // Adicionar listeners aos botões existentes
                addButtonListeners();
                
                // Configurar observador de mutações
                setupMutationObserver();
                
                // Configurar eventos para esconder o spinner
                setupHideEvents();
                
                // Re-escanear botões periodicamente (fallback)
                setInterval(function() {
                    addButtonListeners();
                }, 5000);
                
                console.log('WC Spinner: Inicialização concluída');
            }

            // Inicializar
            init();
        });
    </script>
    <?php
}

/**
 * Versão alternativa mais simples (para casos de emergência)
 * Descomente a linha abaixo se a versão principal não funcionar
 */
function wc_spinner_simples_emergencia() {
    if (!is_woocommerce() && !is_cart() && !is_checkout() && !is_shop() && 
        !is_product_category() && !is_product_tag() && !is_product()) {
        return;
    }
    ?>
    <div id="wc-spinner-simple" style="position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,0.7);display:none;justify-content:center;align-items:center;z-index:999999;">
        <div style="width:60px;height:60px;border:6px solid #f3f3f3;border-top:6px solid #0073aa;border-radius:50%;animation:wc-spin-simple 1s linear infinite;"></div>
    </div>
    
    <style>
        @keyframes wc-spin-simple { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
    </style>
    
    <script>
        jQuery(document).ready(function($) {
            var spinnerTimer;
            
            function showSimpleSpinner() {
                $('#wc-spinner-simple').show();
                if (spinnerTimer) clearTimeout(spinnerTimer);
                spinnerTimer = setTimeout(function() {
                    $('#wc-spinner-simple').hide();
                }, 5000);
            }
            
            function checkButtons() {
                $('button, input[type="submit"], .button').each(function() {
                    var text = ($(this).text() || $(this).val() || '').toLowerCase();
                    if (text.includes('adicionar ao carrinho') || text.includes('finalizar') || 
                        text.includes('checkout') || text.includes('continuar') || text.includes('comprar')) {
                        $(this).off('click.simple-spinner').on('click.simple-spinner', function() {
                            if (!$(this).is(':disabled')) {
                                showSimpleSpinner();
                            }
                        });
                    }
                });
            }
            
            checkButtons();
            setInterval(checkButtons, 3000);
        });
    </script>
    <?php
}