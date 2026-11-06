// HENNÉ MUSLIM — comportements de la galerie (JS ciblé, CDC §11).

// 1. Visionneuse plein écran (élément <dialog> natif, accessible)
const boite = document.querySelector('[data-visionneuse-boite]');

if (boite) {
    document.querySelectorAll('[data-visionneuse]').forEach((declencheur) => {
        declencheur.addEventListener('click', () => boite.showModal());
    });

    boite.querySelector('[data-visionneuse-fermer]')?.addEventListener('click', () => boite.close());

    // Fermeture au clic sur le fond
    boite.addEventListener('click', (evenement) => {
        if (evenement.target === boite) {
            boite.close();
        }
    });
}

// 2. Partage : fonctions natives si disponibles, sinon copie du lien (CDC §6)
document.querySelectorAll('[data-partage]').forEach((bouton) => {
    bouton.addEventListener('click', async () => {
        const donnees = {
            title: bouton.dataset.titre,
            url: bouton.dataset.url,
        };

        if (navigator.share) {
            try {
                await navigator.share(donnees);
            } catch {
                // Partage annulé par l'utilisatrice : rien à faire.
            }

            return;
        }

        try {
            await navigator.clipboard.writeText(donnees.url);
            const libelle = bouton.textContent;
            bouton.textContent = 'Lien copié !';
            setTimeout(() => (bouton.textContent = libelle), 2000);
        } catch {
            window.prompt('Copiez le lien du modèle :', donnees.url);
        }
    });
});
