console.log("JS dark-mode chargé !");

document.addEventListener("DOMContentLoaded", () => {

    const button = document.getElementById("dark-mode-toggle");

    if (!button) {
        console.error("Bouton du mode sombre introuvable !");
        return;
    }

    const body = document.body;

    
    if (localStorage.getItem("dark-mode") === "enabled") {
        body.classList.add("dark-mode");
        button.textContent = "☀️ Mode clair";
    }

    button.addEventListener("click", () => {
        const isDark = body.classList.toggle("dark-mode");

        if (isDark) {
            localStorage.setItem("dark-mode", "enabled");
            button.textContent = "☀️ Mode clair";
        } else {
            localStorage.setItem("dark-mode", "disabled");
            button.textContent = "🌙 Mode sombre";
        }
    });
});
