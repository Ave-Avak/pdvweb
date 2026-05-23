/* =====================================================================
   public/assets/js/main.js
   ---------------------------------------------------------------------
   JavaScript global du site — Approche orientée objet (POO ES6 / classes).

   Conforme à l'exigence du cahier des charges :
   "contenir un ou plusieurs scripts client (javascript) utilisant
    l'approche orientée objet"

   Architecture en classes ES6 :
   - FlashMessageManager : gère la disparition automatique des messages flash
   - ConfirmationManager : gère les confirmations data-confirm
   - DropdownMenuManager : gère les menus déroulants au clic
   - CharacterCounter   : compteur de caractères en temps réel (minichat)
   - AvatarPreview      : prévisualisation d'avatar à l'upload
   ===================================================================== */


/**
 * Gère la disparition automatique des messages flash après un délai.
 */
class FlashMessageManager {
    constructor(duree = 5000) {
        this.duree = duree;
    }

    init() {
        document.querySelectorAll('[role="alert"]').forEach((msg) => {
            this.programmerDisparition(msg);
        });
    }

    programmerDisparition(element) {
        setTimeout(() => {
            element.style.transition = 'opacity 0.5s ease-out, transform 0.5s ease-out';
            element.style.opacity = '0';
            element.style.transform = 'translateY(-10px)';
            setTimeout(() => element.remove(), 500);
        }, this.duree);
    }
}


/**
 * Gère les confirmations pour les actions destructives.
 * Cible : éléments avec attribut data-confirm="Message".
 */
class ConfirmationManager {
    init() {
        document.addEventListener('click', (e) => this.intercepter(e));
    }

    intercepter(evenement) {
        const cible = evenement.target.closest('[data-confirm]');
        if (!cible) return;
        const message = cible.getAttribute('data-confirm');
        if (!confirm(message)) {
            evenement.preventDefault();
        }
    }
}


/**
 * Gère les menus déroulants au clic.
 *
 * Pattern HTML :
 *   <div data-menu>
 *     <button data-menu-toggle>...</button>
 *     <div data-menu-panel class="hidden">...</div>
 *   </div>
 */
class DropdownMenuManager {
    init() {
        document.addEventListener('click', (e) => this.gererClic(e));
        document.addEventListener('keydown', (e) => this.gererTouche(e));
    }

    gererClic(evenement) {
        const toggle = evenement.target.closest('[data-menu-toggle]');
        if (toggle) {
            const conteneur = toggle.closest('[data-menu]');
            const panel = conteneur ? conteneur.querySelector('[data-menu-panel]') : null;
            if (panel) {
                this.fermerLesAutres(panel);
                panel.classList.toggle('hidden');
                evenement.stopPropagation();
                return;
            }
        }
        if (evenement.target.closest('[data-menu-panel]')) return;
        this.fermerTous();
    }

    gererTouche(evenement) {
        if (evenement.key === 'Escape') {
            this.fermerTous();
        }
    }

    fermerLesAutres(panelCourant) {
        document.querySelectorAll('[data-menu-panel]').forEach((p) => {
            if (p !== panelCourant) p.classList.add('hidden');
        });
    }

    fermerTous() {
        document.querySelectorAll('[data-menu-panel]').forEach((p) => {
            p.classList.add('hidden');
        });
    }
}


/**
 * Compteur de caractères en temps réel pour les textarea.
 * Cible : <textarea id="message"> avec <span id="compteur">
 */
class CharacterCounter {
    init() {
        document.querySelectorAll('textarea#message, textarea[data-counter]').forEach((textarea) => {
            this.attacher(textarea);
        });
    }

    attacher(textarea) {
        const idCompteur = textarea.getAttribute('data-counter') || 'compteur';
        const compteur = document.getElementById(idCompteur);
        if (!compteur) return;

        const maxLength = parseInt(textarea.getAttribute('maxlength') || '0', 10);

        const majCompteur = () => {
            const longueur = textarea.value.length;
            compteur.textContent = longueur;

            if (maxLength > 0 && longueur >= maxLength * 0.9) {
                compteur.style.color = '#f59e0b';
            } else {
                compteur.style.color = '';
            }
        };

        textarea.addEventListener('input', majCompteur);
        majCompteur();
    }
}


/**
 * Prévisualisation d'un avatar avant upload.
 * Cible : <input type="file" id="avatar"> + <img id="avatar_preview">
 */
class AvatarPreview {
    init() {
        const input = document.getElementById('avatar');
        const preview = document.getElementById('avatar_preview');
        if (!input || !preview) return;

        input.addEventListener('change', (e) => this.previsualiser(e, preview));
    }

    previsualiser(evenement, imgPreview) {
        const fichier = evenement.target.files[0];
        if (!fichier) return;

        if (!fichier.type.match(/^image\/(jpeg|gif)$/)) {
            alert('Format non autorisé : utilisez .jpg, .jpeg ou .gif uniquement.');
            evenement.target.value = '';
            return;
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            imgPreview.src = e.target.result;
        };
        reader.readAsDataURL(fichier);
    }
}


/**
 * Application principale. Instancie et démarre tous les managers.
 */
class PDVWebApp {
    constructor() {
        this.managers = [
            new FlashMessageManager(),
            new ConfirmationManager(),
            new DropdownMenuManager(),
            new CharacterCounter(),
            new AvatarPreview(),
        ];
    }

    demarrer() {
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', () => this.initialiserManagers());
        } else {
            this.initialiserManagers();
        }
    }

    initialiserManagers() {
        this.managers.forEach((manager) => {
            try {
                manager.init();
            } catch (erreur) {
                console.error('Erreur dans', manager.constructor.name, erreur);
            }
        });
    }
}


// Point d'entrée
const app = new PDVWebApp();
app.demarrer();
