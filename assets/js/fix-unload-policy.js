/**
 * Fix for Permissions Policy Violation: unload is not allowed
 * This script removes the problematic beforeunload event listeners
 * that are causing browser security warnings in modern browsers.
 * 
 * Conservative version - only targets WP Bakery specific issues.
 */

(function() {
    'use strict';
    
    // Safety flag to prevent multiple executions
    if (window.miraUnloadPolicyFixed) {
        return;
    }
    window.miraUnloadPolicyFixed = true;
    
    // Only target WP Bakery specific beforeunload events
    function isWPBakeryRelated(type, events) {
        if (type !== 'beforeunload' && type !== 'unload') return false;
        
        // Check if we have WP Bakery objects
        if (window.vc || window.vc_editor) return true;
        
        // Check if events string contains WP Bakery namespaces
        if (typeof events === 'string' && events.includes('vcSave')) return true;
        
        return false;
    }
    
    // Remove existing WP Bakery beforeunload listeners when the page loads
    document.addEventListener('DOMContentLoaded', function() {
        try {
            // Remove jQuery-based beforeunload listeners used by WP Bakery
            if (window.jQuery) {
                window.jQuery(window).off('beforeunload.vcSave');
            }
            
            // Override the vc setDataChanged function if it exists
            if (window.vc && typeof window.vc.setDataChanged === 'function') {
                var originalSetDataChanged = window.vc.setDataChanged;
                window.vc.setDataChanged = function() {
                    // Call the original function but skip the beforeunload listener
                    if (window.vc.isLayoutChanging) return;
                    
                    if (window.vc.undoRedoApi) {
                        var self = this;
                        if (window._ && typeof window._.defer === 'function') {
                            window._.defer(function() {
                                self.addUndo(window.vc.builder ? window.vc.builder.getContent() : '');
                            });
                        }
                    }
                    
                    // Set data changed flag without adding beforeunload listener
                    this.data_changed = true;
                    
                    console.log('WP Bakery: Data changed state preserved (unload events blocked for browser compatibility)');
                };
            }
            
            console.log('WP Bakery unload policy fix applied');
            
        } catch (error) {
            console.warn('WP Bakery unload policy fix encountered an error:', error);
        }
    });
    
    // Override jQuery's on method for WP Bakery specific events only
    function overrideJQueryMethods() {
        try {
            if (window.jQuery && window.jQuery.fn && window.jQuery.fn.on) {
                var originalJQueryOn = window.jQuery.fn.on;
                window.jQuery.fn.on = function(events, selector, data, handler) {
                    // Only block WP Bakery specific beforeunload events
                    if (typeof events === 'string' && events.includes('beforeunload.vcSave')) {
                        console.warn('Blocked WP Bakery beforeunload.vcSave event (browser policy compliance)');
                        return this;
                    }
                    return originalJQueryOn.call(this, events, selector, data, handler);
                };
            }
        } catch (error) {
            console.warn('Could not override jQuery methods:', error);
        }
    }
    
    // Try to override jQuery methods immediately and after DOM ready
    overrideJQueryMethods();
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(overrideJQueryMethods, 100);
    });
    
})();
