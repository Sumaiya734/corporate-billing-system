/**
 * Universal Back Button Handler
 * Makes all back buttons navigate to the previous page in browser history
 * 
 * Exceptions:
 * - Buttons with data-action="navigate" will follow their href attribute instead
 */

document.addEventListener('DOMContentLoaded', function () {
    // Find all back buttons by common patterns
    const backButtons = document.querySelectorAll(
        'a[href*="back"], ' +
        'button[onclick*="back"], ' +
        '.btn-back, ' +
        '[data-action="back"]'
    );

    // Also find buttons with specific text content
    const allButtons = document.querySelectorAll('a.btn, button.btn');

    allButtons.forEach(button => {
        const buttonText = button.textContent.trim().toLowerCase();
        const hasBackIcon = button.querySelector('.fa-arrow-left');

        // Skip buttons with data-action="navigate"
        if (button.hasAttribute('data-action') && button.getAttribute('data-action') === 'navigate') {
            return;
        }

        // Check if button contains "back" text or has back arrow icon
        if (buttonText.includes('back') || hasBackIcon) {
            // Prevent default link behavior
            button.addEventListener('click', function (e) {
                e.preventDefault();
                window.history.back();
            });

            // Add visual indicator (optional)
            button.setAttribute('data-back-button', 'true');
        }
    });

    // Handle buttons with data-action="back" attribute
    document.querySelectorAll('[data-action="back"]').forEach(button => {
        button.addEventListener('click', function (e) {
            e.preventDefault();
            window.history.back();
        });
    });
});

/**
 * Alternative: Add this function to make any button a back button
 * Usage: <button onclick="goBack()">Back</button>
 */
function goBack() {
    window.history.back();
}
