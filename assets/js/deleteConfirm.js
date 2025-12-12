
console.log("JS confirm-delete chargé !");

document.addEventListener("DOMContentLoaded", () => {

    
    const deleteForms = document.querySelectorAll('form[action*="delete"]');

    if (deleteForms.length === 0) {
        console.log("Aucun formulaire de suppression trouvé.");
        return;
    }

    console.log(deleteForms.length + " formulaires détectés pour la suppression.");

    deleteForms.forEach(form => {
        form.addEventListener("submit", function (event) {

          
            const confirmation = confirm("⚠️ Voulez-vous vraiment supprimer cet utilisateur ?");

            if (!confirmation) {
                console.log("Suppression annulée par l'utilisateur.");
                event.preventDefault(); 
            } else {
                console.log("Suppression confirmée !");
            }
        });
    });
});

