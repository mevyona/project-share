document.addEventListener("DOMContentLoaded", () => {

    // Sélection des champs du formulaire
    const firstname = document.querySelector("#registration_form_firstname");
    const lastname = document.querySelector("#registration_form_lastname");
    const adresse = document.querySelector("#registration_form_adresse");
    const ville = document.querySelector("#registration_form_ville");
    const codePostal = document.querySelector("#registration_form_code_postal");
    const email = document.querySelector("#registration_form_email");
    const password = document.querySelector("#registration_form_plainPassword");
    const agreeTerms = document.querySelector("#registration_form_agreeTerms");
    const submitBtn = document.querySelector("button[type='submit']");

    // On désactive le bouton au chargement
    submitBtn.disabled = true;
    submitBtn.classList.add("disabled");

    // Fonction de validation email
    function isValidEmail(value) {
        return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
    }

    // Fonction de validation mot de passe
    function isValidPassword(value) {
        return (
            value.length >= 12 &&
            /[A-Z]/.test(value) &&
            /[a-z]/.test(value) &&
            /[0-9]/.test(value) &&
            /[\W]/.test(value)
        );
    }

    // Fonction principale : vérifie si tout est ok
    function checkForm() {
        const isComplete =
            firstname.value.trim() !== "" &&
            lastname.value.trim() !== "" &&
            adresse.value.trim() !== "" &&
            ville.value.trim() !== "" &&
            codePostal.value.trim() !== "" &&
            email.value.trim() !== "" &&
            password.value.trim() !== "" &&
            agreeTerms.checked;

        const isValid =
            isComplete &&
            isValidEmail(email.value) &&
            isValidPassword(password.value);

        submitBtn.disabled = !isValid;

        if (isValid) {
            submitBtn.classList.remove("disabled");
        } else {
            submitBtn.classList.add("disabled");
        }
    }

    // On écoute les changements dans CHAQUE champ
    [
        firstname,
        lastname,
        adresse,
        ville,
        codePostal,
        email,
        password,
        agreeTerms
    ].forEach((field) => {
        field.addEventListener("input", checkForm);
        field.addEventListener("change", checkForm);
    });
});
