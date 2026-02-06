function togglePassword(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon = document.getElementById(iconId);

    // Create icon element if it doesn't exist (if passed as 'this' from onclick)
    // But better to expect IDs.

    if (input.type === "password") {
        input.type = "text";
        icon.innerHTML = "👁️‍🗨️"; // Open Eye / Slash
    } else {
        input.type = "password";
        icon.innerHTML = "👁️"; // Closed Eye
    }
}
