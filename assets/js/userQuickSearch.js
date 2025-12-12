console.log("JS user-quick-search chargé !");

document.addEventListener("DOMContentLoaded", () => {

   
    const searchInput = document.getElementById("user-quick-search");

    if (!searchInput) {
        console.log("Input #user-quick-search introuvable.");
        return;
    }

    
    const table = document.querySelector("table");

    if (!table) {
        console.log("Aucun tableau trouvé pour la recherche rapide.");
        return;
    }

    const rows = table.querySelectorAll("tbody tr");

    if (rows.length === 0) {
        console.log("Aucune ligne d'utilisateur trouvée dans le tableau.");
        return;
    }

    console.log(rows.length + " lignes d'utilisateurs détectées pour la recherche.");

   
    searchInput.addEventListener("input", () => {
        const term = searchInput.value.toLowerCase().trim();

        rows.forEach(row => {
            const text = row.innerText.toLowerCase();

            if (text.includes(term)) {
                row.style.display = "";
            } else {
                row.style.display = "none";
            }
        });
    });
});
