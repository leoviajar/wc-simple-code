<?php

function adicionar_script_auto_variacao_com_sticky() {
    if (is_product()) {
        global $product;
        
        if ($product && $product->is_purchasable()) {
            ?>
            <style>
            .wc-sticky-add-to-cart {
                display: none;
                position: fixed;
                bottom: 0;
                left: 0;
                right: 0;
                z-index: 9999;
                transform: translateY(100%);
                transition: transform 0.3s ease;
                display: flex;
                justify-content: center;
                align-items: center;
            }
            
            .wc-sticky-add-to-cart.show {
                transform: translateY(0);
            }

            @media (min-width: 769px) {
                .wc-sticky-add-to-cart {
                    display: none !important;
                }
            }

            .wc-sticky-button {
                width: 100%!important;
                background-color: var(--btn-accented-bgcolor)!important;
                color: #fff!important;
            }
            </style>
            <script type="text/javascript">
            jQuery(document).ready(function($) {
                function autoSelectVariation($button) {
                    var $form = $button.closest("form.variations_form");
                    var $variations = $form.find("select[name^=\'attribute_\']");
                    var hasUnselectedVariations = false;
                    
                    $variations.each(function() {
                        if ($(this).val() === "") {
                            hasUnselectedVariations = true;
                        }
                    });
                    
                    if (hasUnselectedVariations) {
                        $variations.each(function() {
                            var $select = $(this);
                            if ($select.val() === "") {
                                var firstOption = $select.find("option:not([value=\'\']):first").val();
                                if (firstOption) {
                                    $select.val(firstOption).trigger("change");
                                }
                            }
                        });
                        return true;
                    }
                    return false;
                }
                
                $(".single_add_to_cart_button").on("click", function(e) {
                    var $button = $(this);
                    var hadAutoSelection = autoSelectVariation($button);
                    
                    if (hadAutoSelection) {
                        e.preventDefault();
                        setTimeout(function() {
                            var $form = $button.closest("form.variations_form");
                            var $variations = $form.find("select[name^=\'attribute_\']");
                            var allSelected = true;
                            
                            $variations.each(function() {
                                if ($(this).val() === "") {
                                    allSelected = false;
                                }
                            });
                            
                            if (allSelected) {
                                $form.submit();
                            }
                        }, 500);
                    }
                });
                
                function initializeStickyButton() {
                    if ($(window).width() <= 768) {
                        if ($(".wc-sticky-add-to-cart").length > 0) {
                            $(".wc-sticky-add-to-cart").remove();
                        }
                        
                        var stickyHtml =
                            '<div class="wc-sticky-add-to-cart">' +
                                '<button class="wc-sticky-button" type="button">Adicionar ao Carrinho</button>' +
                            '</div>';
                        
                        $("body").append(stickyHtml);
                        $("body").addClass("has-sticky-cart");
                        
                        var $stickyCart = $(".wc-sticky-add-to-cart");
                        var $originalButton = $(".single_add_to_cart_button");
                        
                        function getOriginalButtonOffset() {
                            var $btn = $(".single_add_to_cart_button");
                            return $btn.length ? $btn.offset().top : 0;
                        }
                        
                        function toggleStickyCart() {
                            var scrollTop = $(window).scrollTop();
                            var windowHeight = $(window).height();
                            var originalButtonOffset = getOriginalButtonOffset();
                            
                            if (scrollTop + windowHeight > originalButtonOffset + 1000) {
                                $stickyCart.addClass("show");
                            } else {
                                $stickyCart.removeClass("show");
                            }
                        }
                        
                        $(window).on("scroll", toggleStickyCart);
                        
                        setTimeout(function() {
                            toggleStickyCart();
                        }, 100);
                        
                        $(".wc-sticky-button").on("click", function(e) {
                            e.preventDefault();
                            var $stickyButton = $(this);
                            var $originalButton = $(".single_add_to_cart_button");
                            
                            if ($originalButton.length) {
                                $stickyButton.addClass("loading");
                                
                                var hadAutoSelection = autoSelectVariation($originalButton);
                                
                                if (hadAutoSelection) {
                                    setTimeout(function() {
                                        var $form = $originalButton.closest("form.variations_form");
                                        var $variations = $form.find("select[name^=\'attribute_\']");
                                        var allSelected = true;
                                        
                                        $variations.each(function() {
                                            if ($(this).val() === "") {
                                                allSelected = false;
                                            }
                                        });
                                        
                                        if (allSelected) {
                                            $originalButton.trigger("click");
                                        }
                                        
                                        $stickyButton.removeClass("loading");
                                    }, 500);
                                } else {
                                    $originalButton.trigger("click");
                                    $stickyButton.removeClass("loading");
                                }
                            }
                        });
                        
                        function syncButtonState() {
                            var $originalButton = $(".single_add_to_cart_button");
                            var $stickyButton = $(".wc-sticky-button");
                            
                            if ($originalButton.length === 0) {
                                setTimeout(syncButtonState, 100);
                                return;
                            }
                            
                            var $form = $originalButton.closest("form.variations_form");
                            var hasVariations = $form.length > 0 && $form.find("select[name^=\'attribute_\']").length > 0;
                            
                            if (hasVariations) {
                                $stickyButton.prop("disabled", false);
                            } else {
                                var isDisabled = $originalButton.is(":disabled") || 
                                               $originalButton.hasClass("disabled") || 
                                               $originalButton.hasClass("wc-variation-selection-needed");
                                
                                if (isDisabled) {
                                    $stickyButton.prop("disabled", true);
                                } else {
                                    $stickyButton.prop("disabled", false);
                                }
                            }
                            
                            var originalText = $originalButton.text().trim();
                            if (originalText && originalText !== "Adicionar ao Carrinho" && originalText !== "") {
                                $stickyButton.text(originalText);
                            } else {
                                $stickyButton.text("Adicionar ao Carrinho");
                            }
                        }
                        
                        function setupObserver() {
                            if (typeof MutationObserver !== "undefined") {
                                var observer = new MutationObserver(function() {
                                    setTimeout(syncButtonState, 50);
                                });
                                
                                var $originalButton = $(".single_add_to_cart_button");
                                if ($originalButton.length) {
                                    observer.observe($originalButton[0], {
                                        attributes: true,
                                        childList: true,
                                        subtree: true
                                    });
                                } else {
                                    setTimeout(setupObserver, 100);
                                }
                            }
                        }
                        
                        setTimeout(function() {
                            syncButtonState();
                            setupObserver();
                        }, 200);
                        
                        setInterval(syncButtonState, 1000);
                        
                        $("form.variations_form").on("hide_variation", function() {
                            setTimeout(function() {
                                $(".wc-sticky-button").prop("disabled", false);
                            }, 50);
                        }).on("show_variation", function() {
                            setTimeout(function() {
                                $(".wc-sticky-button").prop("disabled", false);
                            }, 50);
                        });
                        
                        $(document).on("woocommerce_variation_has_changed", function() {
                            setTimeout(function() {
                                $(".wc-sticky-button").prop("disabled", false);
                            }, 100);
                        });
                    }
                }
                
                setTimeout(initializeStickyButton, 300);
                
                $(window).on("load", function() {
                    setTimeout(initializeStickyButton, 500);
                });
            });
            </script>
            <?php
        }
    }
}
add_action("wp_footer", "adicionar_script_auto_variacao_com_sticky");