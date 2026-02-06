// Toast Notification System
// Usage: showToast('Message here', 'success|error|warning|info')

function showToast(message, type = 'info') {
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;

    // Icon based on type
    const icons = {
        success: '✓',
        error: '✕',
        warning: '⚠',
        info: 'ℹ'
    };

    toast.innerHTML = `
        <div class="toast-icon">${icons[type] || icons.info}</div>
        <div class="toast-message">${message}</div>
        <button class="toast-close" onclick="this.parentElement.remove()">×</button>
    `;

    document.body.appendChild(toast);

    // Trigger animation
    setTimeout(() => toast.classList.add('show'), 10);

    // Auto remove after 4 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => toast.remove(), 300);
    }, 4000);
}

// Helper for URL parameters (for redirected success messages)
function checkUrlToast() {
    const params = new URLSearchParams(window.location.search);
    const msg = params.get('toast_msg');
    const type = params.get('toast_type') || 'success';

    if (msg) {
        showToast(decodeURIComponent(msg), type);
        // Clean URL
        const url = new URL(window.location);
        url.searchParams.delete('toast_msg');
        url.searchParams.delete('toast_type');
        window.history.replaceState({}, '', url);
    }
}

// Auto-check on page load
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', checkUrlToast);
} else {
    checkUrlToast();
}
