function initAccessibility() {
    const btnIncrease = document.getElementById('btn-increase-text');
    const btnDecrease = document.getElementById('btn-decrease-text');
    const btnReset = document.getElementById('btn-reset-text');
    const contrastToggle = document.getElementById('contrast-toggle');
    const toggleCircle = document.getElementById('contrast-toggle-circle');

    // Read saved preference from localStorage
    let scale = parseFloat(localStorage.getItem('accessibility-text-scale')) || 1.0;
    let highContrast = localStorage.getItem('accessibility-high-contrast') === 'true';

    // Apply text scale function
    function applyTextScale(newScale) {
        scale = Math.max(0.85, Math.min(1.25, newScale));
        document.documentElement.style.fontSize = scale === 1.0 ? '' : `${scale * 100}%`;
        localStorage.setItem('accessibility-text-scale', scale);
    }

    // Apply contrast mode function
    function applyContrastMode(isContrast) {
        highContrast = isContrast;
        if (highContrast) {
            document.documentElement.classList.add('high-contrast-mode');
            if (contrastToggle) {
                contrastToggle.setAttribute('aria-checked', 'true');
                contrastToggle.classList.remove('bg-[#1C3A5E]');
                contrastToggle.classList.add('bg-amber-600');
            }
            if (toggleCircle) {
                toggleCircle.style.transform = 'translateX(32px)';
            }
        } else {
            document.documentElement.classList.remove('high-contrast-mode');
            if (contrastToggle) {
                contrastToggle.setAttribute('aria-checked', 'false');
                contrastToggle.classList.remove('bg-amber-600');
                contrastToggle.classList.add('bg-[#1C3A5E]');
            }
            if (toggleCircle) {
                toggleCircle.style.transform = 'translateX(4px)';
            }
        }
        localStorage.setItem('accessibility-high-contrast', highContrast);
    }

    // Initial applications
    applyTextScale(scale);
    applyContrastMode(highContrast);

    // Event Listeners
    if (btnIncrease && !btnIncrease.dataset.listenerAdded) {
        btnIncrease.addEventListener('click', () => applyTextScale(scale + 0.05));
        btnIncrease.dataset.listenerAdded = 'true';
    }
    if (btnDecrease && !btnDecrease.dataset.listenerAdded) {
        btnDecrease.addEventListener('click', () => applyTextScale(scale - 0.05));
        btnDecrease.dataset.listenerAdded = 'true';
    }
    if (btnReset && !btnReset.dataset.listenerAdded) {
        btnReset.addEventListener('click', () => applyTextScale(1.0));
        btnReset.dataset.listenerAdded = 'true';
    }
    if (contrastToggle && !contrastToggle.dataset.listenerAdded) {
        contrastToggle.addEventListener('click', () => applyContrastMode(!highContrast));
        contrastToggle.dataset.listenerAdded = 'true';
    }
}

document.addEventListener('DOMContentLoaded', initAccessibility);
document.addEventListener('livewire:navigated', initAccessibility);

// Trigger immediately in case DOM is already loaded or wire navigated fired
initAccessibility();
