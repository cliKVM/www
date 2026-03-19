// Plesk Panel Emulation - JavaScript Application

(function() {
    'use strict';

    // Panel switching
    document.querySelectorAll('.nav-item').forEach(item => {
        item.addEventListener('click', (e) => {
            e.preventDefault();
            
            // Update active nav item
            document.querySelectorAll('.nav-item').forEach(nav => nav.classList.remove('active'));
            item.classList.add('active');
            
            // Switch panel
            const panelId = item.getAttribute('data-panel');
            document.querySelectorAll('.panel').forEach(panel => panel.classList.remove('active'));
            document.getElementById('panel-' + panelId).classList.add('active');
            
            // Log navigation
            logAction('Navigated to ' + item.textContent.trim());
        });
    });

    // Quick actions
    window.openFileManager = function() {
        logAction('Opening File Manager...');
        document.querySelector('[data-panel="files"]').click();
    };

    window.openPhpSettings = function() {
        logAction('Opening PHP Settings...');
        document.querySelector('[data-panel="php"]').click();
    };

    window.previewSite = function() {
        logAction('Opening website preview...');
        window.open('http://localhost:8080', '_blank');
    };

    // Logging function
    function logAction(message) {
        const logContainer = document.getElementById('deploy-log');
        if (!logContainer) return;
        
        const now = new Date();
        const time = `[${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}]`;
        
        const entry = document.createElement('div');
        entry.className = 'log-entry';
        entry.innerHTML = `
            <span class="time">${time}</span>
            <span class="msg">${message}</span>
        `;
        
        logContainer.appendChild(entry);
        logContainer.scrollTop = logContainer.scrollHeight;
    }

    // Check website status
    async function checkWebsiteStatus() {
        try {
            const response = await fetch('http://localhost:8080', { 
                method: 'HEAD',
                mode: 'no-cors'
            });
            logAction('Website health check: OK');
        } catch (e) {
            logAction('Website health check: Unable to reach (expected in some browsers due to CORS)');
        }
    }

    // Initialize
    document.addEventListener('DOMContentLoaded', () => {
        logAction('Plesk Panel Emulation loaded');
        logAction('Mock Plesk v18.0.50 - Development Environment');
        
        // Check website after a short delay
        setTimeout(checkWebsiteStatus, 1000);
        
        // Add click handlers for buttons without specific actions
        document.querySelectorAll('.btn').forEach(btn => {
            if (!btn.onclick && !btn.getAttribute('href')) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const action = btn.textContent.trim();
                    logAction(`Action: ${action} (mock)`);
                });
            }
        });
    });

    // Expose log function globally
    window.pleskLog = logAction;

})();
