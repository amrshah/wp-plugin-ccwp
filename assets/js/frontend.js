/**
 * Dynamic Content Pro - Frontend JavaScript
 */

(function($) {
    'use strict';
    
    const DCP_Frontend = {
        
        init: function() {
            this.trackViews();
            this.handleDynamicLoad();
            this.setupCookies();
            this.setupABTests();
        },
        
        /**
         * Track content views for analytics
         */
        trackViews: function() {
            $('.dcp-content[data-track="true"]').each(function() {
                const $el = $(this);
                const postId = $el.data('post-id');
                const variantId = $el.data('variant-id');
                
                if (!postId) return;
                
                // Track view via AJAX
                $.ajax({
                    url: dcpData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'dcp_track_view',
                        post_id: postId,
                        variant_id: variantId,
                        nonce: dcpData.nonce
                    }
                });
            });
        },
        
        /**
         * Handle dynamic content loading
         */
        handleDynamicLoad: function() {
            $('[data-dcp-load="true"]').each(function() {
                const $el = $(this);
                const contentId = $el.data('content-id');
                
                if (!contentId) return;
                
                $el.html('<div class="dcp-loading-container"><div class="dcp-spinner"></div></div>');
                
                // Load content via AJAX
                $.ajax({
                    url: dcpData.ajaxUrl,
                    type: 'POST',
                    data: {
                        action: 'dcp_get_content',
                        content_id: contentId,
                        nonce: dcpData.nonce
                    },
                    success: function(response) {
                        if (response.success) {
                            $el.html(response.data.content).addClass('dcp-fade-in');
                        } else {
                            $el.html('<div class="dcp-error">Failed to load content</div>');
                        }
                    },
                    error: function() {
                        $el.html('<div class="dcp-error">Error loading content</div>');
                    }
                });
            });
        },
        
        /**
         * Setup cookie-based conditions
         */
        setupCookies: function() {
            // Set DCP tracking cookie if not exists
            if (!this.getCookie('dcp_visitor')) {
                this.setCookie('dcp_visitor', this.generateId(), 365);
            }
            
            // Track returning visitors
            const visits = parseInt(this.getCookie('dcp_visits') || '0');
            this.setCookie('dcp_visits', visits + 1, 365);
            
            // Set first visit timestamp
            if (!this.getCookie('dcp_first_visit')) {
                this.setCookie('dcp_first_visit', Date.now(), 365);
            }
        },
        
        /**
         * Setup A/B testing
         */
        setupABTests: function() {
            $('[data-ab-test]').each(function() {
                const $el = $(this);
                const testId = $el.data('ab-test');
                const variantA = $el.data('variant-a');
                const variantB = $el.data('variant-b');
                
                // Get or set variant for this user
                let variant = DCP_Frontend.getCookie('dcp_ab_' + testId);
                
                if (!variant) {
                    variant = Math.random() < 0.5 ? 'A' : 'B';
                    DCP_Frontend.setCookie('dcp_ab_' + testId, variant, 30);
                }
                
                // Show appropriate content
                const content = variant === 'A' ? variantA : variantB;
                $el.html(content).addClass('dcp-fade-in');
                
                // Track which variant was shown
                $el.attr('data-variant-shown', variant);
            });
        },
        
        /**
         * Cookie helper functions
         */
        setCookie: function(name, value, days) {
            let expires = '';
            if (days) {
                const date = new Date();
                date.setTime(date.getTime() + (days * 24 * 60 * 60 * 1000));
                expires = '; expires=' + date.toUTCString();
            }
            document.cookie = name + '=' + (value || '') + expires + '; path=/';
        },
        
        getCookie: function(name) {
            const nameEQ = name + '=';
            const ca = document.cookie.split(';');
            for (let i = 0; i < ca.length; i++) {
                let c = ca[i];
                while (c.charAt(0) === ' ') c = c.substring(1, c.length);
                if (c.indexOf(nameEQ) === 0) return c.substring(nameEQ.length, c.length);
            }
            return null;
        },
        
        deleteCookie: function(name) {
            document.cookie = name + '=; Max-Age=-99999999;';
        },
        
        /**
         * Generate unique ID
         */
        generateId: function() {
            return 'dcp_' + Math.random().toString(36).substr(2, 9) + Date.now().toString(36);
        },
        
        /**
         * Check if element is in viewport
         */
        isInViewport: function(el) {
            const rect = el.getBoundingClientRect();
            return (
                rect.top >= 0 &&
                rect.left >= 0 &&
                rect.bottom <= (window.innerHeight || document.documentElement.clientHeight) &&
                rect.right <= (window.innerWidth || document.documentElement.clientWidth)
            );
        },
        
        /**
         * Lazy load content when in viewport
         */
        lazyLoad: function() {
            const lazyElements = document.querySelectorAll('[data-dcp-lazy="true"]');
            
            if ('IntersectionObserver' in window) {
                const observer = new IntersectionObserver(function(entries) {
                    entries.forEach(function(entry) {
                        if (entry.isIntersecting) {
                            const el = entry.target;
                            const contentId = el.getAttribute('data-content-id');
                            
                            // Load content
                            $.ajax({
                                url: dcpData.ajaxUrl,
                                type: 'POST',
                                data: {
                                    action: 'dcp_get_content',
                                    content_id: contentId,
                                    nonce: dcpData.nonce
                                },
                                success: function(response) {
                                    if (response.success) {
                                        el.innerHTML = response.data.content;
                                        el.classList.add('dcp-fade-in');
                                    }
                                }
                            });
                            
                            observer.unobserve(el);
                        }
                    });
                });
                
                lazyElements.forEach(function(el) {
                    observer.observe(el);
                });
            }
        }
    };
    
    // Initialize on document ready
    $(document).ready(function() {
        DCP_Frontend.init();
        DCP_Frontend.lazyLoad();
    });
    
    // Re-initialize after AJAX content loads (for page builders)
    $(document).on('elementor/popup/show', function() {
        DCP_Frontend.init();
    });
    
})(jQuery);