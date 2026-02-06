<!-- Confirmation Modal -->
<div id="confirmModal" class="confirm-modal">
    <div class="confirm-modal-content">
        <p id="confirmMessage" class="confirm-message">Are you sure?</p>
        <div class="confirm-buttons">
            <button id="confirmYes" class="confirm-btn confirm-yes">Yes</button>
            <button id="confirmNo" class="confirm-btn confirm-no">Cancel</button>
        </div>
    </div>
</div>

<style>
/* Confirmation Modal Styles */
.confirm-modal {
    display: none;
    position: fixed;
    z-index: 9999;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0, 0, 0, 0.6);
    justify-content: center;
    align-items: center;
}

.confirm-modal-content {
    background-color: #fff;
    padding: 35px 40px;
    border-radius: 15px;
    min-width: 400px;
    max-width: 90%;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.4);
    animation: confirmFadeIn 0.3s ease-out;
    text-align: center;
}

@keyframes confirmFadeIn {
    from {
        opacity: 0;
        transform: translateY(-30px) scale(0.95);
    }
    to {
        opacity: 1;
        transform: translateY(0) scale(1);
    }
}

.confirm-message {
    font-size: 18px;
    color: #333;
    margin-bottom: 30px;
    font-weight: 600;
    line-height: 1.5;
}

.confirm-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.confirm-btn {
    padding: 12px 35px;
    border: none;
    border-radius: 25px;
    font-size: 15px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
    min-width: 120px;
}

.confirm-yes {
    background: linear-gradient(135deg, #2ecc71, #27ae60);
    color: #fff;
}

.confirm-yes:hover {
    background: linear-gradient(135deg, #27ae60, #229954);
    transform: scale(1.05);
}

.confirm-no {
    background: #000;
    color: #fff;
}

.confirm-no:hover {
    background: #333;
    transform: scale(1.05);
}
</style>

<script>
// Global confirmation function
let confirmCallback = null;

function showConfirm(message, onConfirm) {
    const modal = document.getElementById('confirmModal');
    const messageEl = document.getElementById('confirmMessage');
    
    messageEl.textContent = message;
    confirmCallback = onConfirm;
    
    modal.style.display = 'flex';
}

function closeConfirm() {
    const modal = document.getElementById('confirmModal');
    modal.style.display = 'none';
    confirmCallback = null;
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const yesBtn = document.getElementById('confirmYes');
    const noBtn = document.getElementById('confirmNo');
    const modal = document.getElementById('confirmModal');
    
    yesBtn.addEventListener('click', function() {
        if (confirmCallback) {
            confirmCallback();
        }
        closeConfirm();
    });
    
    noBtn.addEventListener('click', function() {
        closeConfirm();
    });
    
    // Close on outside click
    modal.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeConfirm();
        }
    });
});
</script>
