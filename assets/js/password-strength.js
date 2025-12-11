console.log("JS password-strength chargé !");

document.addEventListener("DOMContentLoaded", () => {

    const passwordInput = document.getElementById("registration_form_plainPassword");
    const strengthBar = document.getElementById("password-strength-bar");
    const strengthText = document.getElementById("password-strength-text");

    if (!passwordInput || !strengthBar || !strengthText) {
        console.warn("Password strength: élément introuvable.");
        return;
    }

    passwordInput.addEventListener("input", () => {
        const value = passwordInput.value;
        let score = 0;

        if (value.length >= 6) score++;
        if (/[0-9]/.test(value)) score++;
        if (/[A-Z]/.test(value)) score++;
        if (/[^A-Za-z0-9]/.test(value)) score++;
        strengthBar.style.height = "6px";

        switch (score) {
            case 0:
            case 1:
                strengthBar.style.width = "33%";
                strengthBar.style.backgroundColor = "red";
                strengthText.textContent = "Faible";
                break;

            case 2:
            case 3:
                strengthBar.style.width = "66%";
                strengthBar.style.backgroundColor = "orange";
                strengthText.textContent = "Moyen";
                break;

            case 4:
                strengthBar.style.width = "100%";
                strengthBar.style.backgroundColor = "green";
                strengthText.textContent = "Fort";
                break;
        }
    });
});
