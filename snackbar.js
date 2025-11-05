// Snackbar/Toast Notification System
// Reusable notification utility for all pages

(function() {
    'use strict';
    
    // Create snackbar container if it doesn't exist
    function createSnackbarContainer() {
        if (!document.getElementById('snackbar-container')) {
            const container = document.createElement('div');
            container.id = 'snackbar-container';
            container.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 10000;
                display: flex;
                flex-direction: column;
                gap: 10px;
                max-width: 400px;
                pointer-events: none;
            `;
            document.body.appendChild(container);
        }
    }
    
    // Initialize container on load
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', createSnackbarContainer);
    } else {
        createSnackbarContainer();
    }
    
    // Main snackbar function
    window.showSnackbar = function(message, type = 'info', duration = 4000) {
        createSnackbarContainer();
        
        const container = document.getElementById('snackbar-container');
        if (!container) return;
        
        // Create snackbar element
        const snackbar = document.createElement('div');
        snackbar.className = `snackbar snackbar-${type}`;
        
        // Set icon based on type
        let icon = 'fa-info-circle';
        let bgColor = '#2196F3'; // blue (info)
        
        switch(type) {
            case 'success':
                icon = 'fa-check-circle';
                bgColor = '#4CAF50'; // green
                break;
            case 'error':
            case 'danger':
                icon = 'fa-exclamation-circle';
                bgColor = '#f44336'; // red
                break;
            case 'warning':
                icon = 'fa-exclamation-triangle';
                bgColor = '#FF9800'; // orange
                break;
            case 'info':
            default:
                icon = 'fa-info-circle';
                bgColor = '#2196F3'; // blue
                break;
        }
        
        // Set styles
        snackbar.style.cssText = `
            background: ${bgColor};
            color: white;
            padding: 16px 20px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 12px;
            min-width: 300px;
            max-width: 400px;
            pointer-events: auto;
            animation: slideInRight 0.3s ease-out;
            word-wrap: break-word;
            font-size: 14px;
            line-height: 1.5;
        `;
        
        // Add icon
        const iconEl = document.createElement('i');
        iconEl.className = `fas ${icon}`;
        iconEl.style.cssText = 'font-size: 20px; flex-shrink: 0;';
        
        // Add message
        const messageEl = document.createElement('span');
        messageEl.textContent = message;
        messageEl.style.cssText = 'flex: 1;';
        
        // Add close button
        const closeBtn = document.createElement('button');
        closeBtn.innerHTML = '&times;';
        closeBtn.style.cssText = `
            background: transparent;
            border: none;
            color: white;
            font-size: 24px;
            cursor: pointer;
            padding: 0;
            margin-left: 10px;
            width: 24px;
            height: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            opacity: 0.8;
            transition: opacity 0.2s;
        `;
        closeBtn.onmouseover = () => closeBtn.style.opacity = '1';
        closeBtn.onmouseout = () => closeBtn.style.opacity = '0.8';
        closeBtn.onclick = () => removeSnackbar(snackbar);
        
        snackbar.appendChild(iconEl);
        snackbar.appendChild(messageEl);
        snackbar.appendChild(closeBtn);
        
        container.appendChild(snackbar);
        
        // Auto-remove after duration
        const timeout = setTimeout(() => {
            removeSnackbar(snackbar);
        }, duration);
        
        // Pause timeout on hover
        snackbar.addEventListener('mouseenter', () => clearTimeout(timeout));
        snackbar.addEventListener('mouseleave', () => {
            setTimeout(() => removeSnackbar(snackbar), duration);
        });
    };
    
    // Remove snackbar with animation
    function removeSnackbar(snackbar) {
        if (!snackbar || !snackbar.parentNode) return;
        
        snackbar.style.animation = 'slideOutRight 0.3s ease-out';
        setTimeout(() => {
            if (snackbar.parentNode) {
                snackbar.parentNode.removeChild(snackbar);
            }
        }, 300);
    }
    
    // Add CSS animations if not already added
    if (!document.getElementById('snackbar-styles')) {
        const style = document.createElement('style');
        style.id = 'snackbar-styles';
        style.textContent = `
            @keyframes slideInRight {
                from {
                    transform: translateX(100%);
                    opacity: 0;
                }
                to {
                    transform: translateX(0);
                    opacity: 1;
                }
            }
            
            @keyframes slideOutRight {
                from {
                    transform: translateX(0);
                    opacity: 1;
                }
                to {
                    transform: translateX(100%);
                    opacity: 0;
                }
            }
            
            @media (max-width: 768px) {
                #snackbar-container {
                    top: 10px;
                    right: 10px;
                    left: 10px;
                    max-width: none;
                }
                
                .snackbar {
                    min-width: auto !important;
                    max-width: none !important;
                }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Replace window.alert with snackbar (optional - can be disabled)
    // window.alert = function(message) {
    //     showSnackbar(message, 'info', 5000);
    // };
    
})();

// Convenience functions
window.showSuccess = function(message, duration) {
    showSnackbar(message, 'success', duration);
};

window.showError = function(message, duration) {
    showSnackbar(message, 'error', duration);
};

window.showWarning = function(message, duration) {
    showSnackbar(message, 'warning', duration);
};

window.showInfo = function(message, duration) {
    showSnackbar(message, 'info', duration);
};



