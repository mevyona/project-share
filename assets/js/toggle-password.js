console.log("JS toggle-password chargé !");

document.addEventListener("DOMContentLoaded", () => {
    const passwordInput = document.getElementById("registration_form_plainPassword");
    const toggleBtn = document.getElementById("toggle-password");

    if (!passwordInput || !toggleBtn) {
        console.error("Password toggle: élément introuvable.");
        return;
    }

    toggleBtn.addEventListener("click", () => {
        const isHidden = passwordInput.type === "password";

        passwordInput.type = isHidden ? "text" : "password";
        toggleBtn.textContent = isHidden ? "Cacher" : "Voir";
    });
});
