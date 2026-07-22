// script.js - comportements JS communs à toute l'application

document.addEventListener('DOMContentLoaded', function () {
    // Confirmation avant toute suppression (livre, etudiant, emprunt)
    document.querySelectorAll('.form-supprimer').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            const confirmation = confirm(
                'Êtes-vous sûr de vouloir supprimer cet element ? Cette action est irreversible.'
            );
            if (!confirmation) {
                e.preventDefault();
            }
        });
    });

    // Fermeture automatique des messages flash après 4 secondes
    document.querySelectorAll('.alert-flash').forEach(function (alerte) {
        setTimeout(function () {
            alerte.classList.add('fade');
            alerte.classList.remove('show');
        }, 4000);
    });
});
