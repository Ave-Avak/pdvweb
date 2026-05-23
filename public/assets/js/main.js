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
 * Gère l'ajout AJAX au panier depuis le catalogue.
 * Permet d'ajouter un article sans recharger la page :
 *   - Intercepte les formulaires marqués data-panier-ajax
 *   - Envoie en fetch() avec header X-Requested-With
 *   - Met à jour le badge panier dans le header
 *   - Affiche un toast de confirmation
 *   - Animation visuelle du bouton (✓ temporaire)
 */
class PanierAjax {
    init() {
        const formulaires = document.querySelectorAll('form[data-panier-ajax]');
        formulaires.forEach((form) => {
            form.addEventListener('submit', (e) => this.gererSoumission(e, form));
        });
    }

    async gererSoumission(evenement, form) {
        evenement.preventDefault();

        const bouton = form.querySelector('button[type="submit"]');
        if (!bouton) return;

        const texteOriginal = bouton.innerHTML;
        const classesOriginales = bouton.className;

        // État "en cours"
        bouton.disabled = true;
        bouton.innerHTML = '...';
        bouton.style.opacity = '0.6';

        try {
            const formData = new FormData(form);
            const reponse = await fetch(form.action, {
                method: 'POST',
                body: formData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });

            const data = await reponse.json();

            if (data.succes) {
                // Animation succès
                bouton.innerHTML = '✓';
                bouton.className = classesOriginales.replace('bg-primary-600 hover:bg-primary-700', 'bg-success-600');
                bouton.style.opacity = '1';

                // Mettre à jour le badge panier
                this.mettreAJourBadgePanier(data.nb_articles);

                // Afficher toast
                this.afficherToast(data.message, 'success');

                // Restaurer après 1.5s
                setTimeout(() => {
                    bouton.innerHTML = texteOriginal;
                    bouton.className = classesOriginales;
                    bouton.disabled = false;
                }, 1500);
            } else {
                this.afficherToast(data.message, 'error');
                bouton.innerHTML = texteOriginal;
                bouton.className = classesOriginales;
                bouton.style.opacity = '1';
                bouton.disabled = false;
            }
        } catch (err) {
            console.error('Erreur AJAX panier :', err);
            this.afficherToast('Erreur de connexion. Veuillez réessayer.', 'error');
            bouton.innerHTML = texteOriginal;
            bouton.className = classesOriginales;
            bouton.style.opacity = '1';
            bouton.disabled = false;
        }
    }

    mettreAJourBadgePanier(nbArticles) {
        // Trouver l'icône panier dans le header
        const lienPanier = document.querySelector('a[href*="panier.php"]');
        if (!lienPanier) return;

        let badge = lienPanier.querySelector('.absolute');

        if (nbArticles > 0) {
            if (badge) {
                // Mettre à jour le badge existant
                badge.textContent = nbArticles;
            } else {
                // Créer le badge
                badge = document.createElement('span');
                badge.className = 'absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center font-bold';
                badge.textContent = nbArticles;
                lienPanier.appendChild(badge);
            }
            // Animation "bump"
            badge.style.transform = 'scale(1.3)';
            setTimeout(() => { badge.style.transform = 'scale(1)'; }, 200);
        } else if (badge) {
            badge.remove();
        }
    }

    afficherToast(message, type = 'success') {
        // Créer un toast en haut à droite
        const toast = document.createElement('div');
        const couleur = type === 'success'
            ? 'bg-success-50 border-success-300 text-success-800'
            : 'bg-danger-50 border-danger-300 text-danger-800';

        toast.className = `fixed top-20 right-6 z-50 max-w-sm px-4 py-3 rounded-lg border shadow-lg ${couleur} transition-all duration-300 transform translate-x-4 opacity-0`;
        toast.style.transition = 'all 0.3s';
        toast.innerHTML = `
            <div class="flex items-start gap-2">
                <span class="text-lg">${type === 'success' ? '✓' : '⚠'}</span>
                <span class="text-sm font-medium">${this.echapper(message)}</span>
            </div>
        `;

        document.body.appendChild(toast);

        // Animation entrée
        requestAnimationFrame(() => {
            toast.style.opacity = '1';
            toast.style.transform = 'translateX(0)';
        });

        // Disparition après 3s
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(20px)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    echapper(texte) {
        const div = document.createElement('div');
        div.textContent = texte;
        return div.innerHTML;
    }
}


/**
 * Recherche autocomplete (live search) sur les champs marqués
 * data-autocomplete-url avec l'URL de l'API.
 *
 * Usage HTML :
 *   <input type="search" name="q"
 *          data-autocomplete-url="/api/recherche.php"
 *          data-autocomplete-type="articles">
 *
 * Comportement :
 *   - Déclenche au bout de 2 caractères
 *   - Debounce 250ms pour éviter les requêtes inutiles
 *   - Affiche un dropdown sous le champ avec les résultats
 *   - Navigation clavier (↑ ↓ Enter Escape)
 *   - Clic dans le dropdown → navigation vers la fiche
 */
class Autocomplete {
    init() {
        const inputs = document.querySelectorAll('input[data-autocomplete-url]');
        inputs.forEach((input) => this.attacher(input));
    }

    attacher(input) {
        const url = input.dataset.autocompleteUrl;
        const type = input.dataset.autocompleteType || 'articles';

        // Créer le conteneur du dropdown
        const wrapper = document.createElement('div');
        wrapper.style.position = 'relative';
        input.parentNode.insertBefore(wrapper, input);
        wrapper.appendChild(input);

        const dropdown = document.createElement('div');
        dropdown.className = 'absolute left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg z-50 hidden max-h-96 overflow-y-auto';
        dropdown.style.top = '100%';
        wrapper.appendChild(dropdown);

        let timeoutId = null;
        let indexSelectionne = -1;
        let resultatsActuels = [];

        // Cacher dropdown au clic extérieur
        document.addEventListener('click', (e) => {
            if (!wrapper.contains(e.target)) {
                dropdown.classList.add('hidden');
            }
        });

        // Saisie au clavier
        input.addEventListener('input', () => {
            clearTimeout(timeoutId);
            const terme = input.value.trim();

            if (terme.length < 2) {
                dropdown.classList.add('hidden');
                return;
            }

            // Debounce 250ms
            timeoutId = setTimeout(async () => {
                await this.rechercher(terme, url, type, dropdown, (r) => {
                    resultatsActuels = r;
                    indexSelectionne = -1;
                });
            }, 250);
        });

        // Navigation clavier
        input.addEventListener('keydown', (e) => {
            const items = dropdown.querySelectorAll('[data-resultat]');
            if (items.length === 0) return;

            if (e.key === 'ArrowDown') {
                e.preventDefault();
                indexSelectionne = Math.min(indexSelectionne + 1, items.length - 1);
                this.mettreEnEvidence(items, indexSelectionne);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                indexSelectionne = Math.max(indexSelectionne - 1, -1);
                this.mettreEnEvidence(items, indexSelectionne);
            } else if (e.key === 'Enter' && indexSelectionne >= 0) {
                e.preventDefault();
                items[indexSelectionne].click();
            } else if (e.key === 'Escape') {
                dropdown.classList.add('hidden');
            }
        });

        // Focus → réafficher si on a des résultats
        input.addEventListener('focus', () => {
            if (resultatsActuels.length > 0) {
                dropdown.classList.remove('hidden');
            }
        });
    }

    async rechercher(terme, url, type, dropdown, callback) {
        try {
            // Loading skeleton
            dropdown.innerHTML = this.skeletonHtml();
            dropdown.classList.remove('hidden');

            const fullUrl = url + '?q=' + encodeURIComponent(terme) + '&type=' + type;
            const response = await fetch(fullUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await response.json();

            if (!data.succes || !data.resultats || data.resultats.length === 0) {
                dropdown.innerHTML = `
                    <div class="p-4 text-center text-sm text-gray-500">
                        Aucun résultat pour "<strong>${this.echapper(terme)}</strong>"
                    </div>
                `;
                callback([]);
                return;
            }

            // Construire les résultats
            const html = data.resultats.map((r) => this.renderItem(r, type, terme)).join('');
            dropdown.innerHTML = html + this.footerHtml(data.nb, terme);

            // Attacher les clics
            dropdown.querySelectorAll('[data-resultat]').forEach((el) => {
                el.addEventListener('click', () => {
                    window.location.href = el.dataset.resultat;
                });
            });

            callback(data.resultats);
        } catch (err) {
            console.error('Erreur autocomplete :', err);
            dropdown.innerHTML = `
                <div class="p-4 text-center text-sm text-danger-600">
                    Erreur de connexion
                </div>
            `;
            callback([]);
        }
    }

    renderItem(r, type, terme) {
        if (type === 'billets') {
            return `
                <a href="${this.echapper(r.url)}" data-resultat="${this.echapper(r.url)}"
                   class="flex gap-3 p-3 hover:bg-primary-50 transition cursor-pointer border-b border-gray-100 last:border-0">
                    <div class="flex-1 min-w-0">
                        <div class="font-semibold text-gray-900 text-sm truncate">${this.surligner(r.titre, terme)}</div>
                        ${r.resume ? `<div class="text-xs text-gray-500 truncate">${this.surligner(r.resume, terme)}</div>` : ''}
                    </div>
                </a>
            `;
        }

        // Articles
        const stockBadge = r.en_stock
            ? '<span class="text-xs text-success-600">✓ Stock</span>'
            : '<span class="text-xs text-danger-600">Épuisé</span>';

        return `
            <a href="${this.echapper(r.url)}" data-resultat="${this.echapper(r.url)}"
               class="flex gap-3 p-3 hover:bg-primary-50 transition cursor-pointer border-b border-gray-100 last:border-0">
                <img src="${this.echapper(r.image_url)}" alt="${this.echapper(r.nom)}"
                     class="w-12 h-12 object-cover rounded-md bg-gray-100 flex-shrink-0"
                     onerror="this.style.opacity=0.3" loading="lazy">
                <div class="flex-1 min-w-0">
                    <div class="font-semibold text-gray-900 text-sm truncate">${this.surligner(r.nom, terme)}</div>
                    <div class="text-xs text-gray-500">${this.echapper(r.categorie)}</div>
                    <div class="flex items-center gap-2 mt-0.5">
                        <span class="text-sm font-bold text-primary-700">${this.echapper(r.prix_format)}</span>
                        ${stockBadge}
                    </div>
                </div>
            </a>
        `;
    }

    /**
     * Surligne les occurrences du terme dans le texte.
     * Insensible à la casse et aux accents simples.
     * Échappe le HTML pour éviter XSS.
     */
    surligner(texte, terme) {
        if (!texte) return '';
        if (!terme || terme.length < 2) return this.echapper(texte);

        const echappe = this.echapper(texte);
        // Échapper les caractères regex spéciaux dans le terme
        const termeRegex = terme.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
        const regex = new RegExp('(' + termeRegex + ')', 'gi');
        return echappe.replace(regex, '<mark class="bg-yellow-200 text-gray-900 font-semibold rounded px-0.5">$1</mark>');
    }

    footerHtml(nb, terme) {
        return `
            <div class="p-2 bg-gray-50 border-t border-gray-100 text-xs text-center text-gray-500">
                ${nb} résultat${nb > 1 ? 's' : ''} — appuyez sur Entrée pour rechercher
            </div>
        `;
    }

    skeletonHtml() {
        return `
            <div class="p-3 space-y-2">
                <div class="h-12 bg-gray-100 rounded animate-pulse"></div>
                <div class="h-12 bg-gray-100 rounded animate-pulse"></div>
                <div class="h-12 bg-gray-100 rounded animate-pulse"></div>
            </div>
        `;
    }

    mettreEnEvidence(items, index) {
        items.forEach((el, i) => {
            if (i === index) {
                el.classList.add('bg-primary-50');
                el.scrollIntoView({ block: 'nearest' });
            } else {
                el.classList.remove('bg-primary-50');
            }
        });
    }

    echapper(texte) {
        if (texte === null || texte === undefined) return '';
        const div = document.createElement('div');
        div.textContent = String(texte);
        return div.innerHTML;
    }
}


/**
 * Carrousel d'images pour les fiches articles avec galerie.
 *
 * HTML attendu :
 *   <div data-carrousel>
 *     <img data-carrousel-image="0" class="opacity-100">
 *     <img data-carrousel-image="1" class="opacity-0">
 *     <button data-carrousel-prev>...</button>
 *     <button data-carrousel-next>...</button>
 *     <span data-carrousel-current>1</span>
 *     <button data-carrousel-thumb="0">...</button>
 *   </div>
 */
class CarrouselImages {
    init() {
        document.querySelectorAll('[data-carrousel]').forEach((c) => this.attacher(c));
    }

    attacher(carrousel) {
        const images = carrousel.querySelectorAll('[data-carrousel-image]');
        const thumbs = carrousel.querySelectorAll('[data-carrousel-thumb]');
        const prevBtn = carrousel.querySelector('[data-carrousel-prev]');
        const nextBtn = carrousel.querySelector('[data-carrousel-next]');
        const counter = carrousel.querySelector('[data-carrousel-current]');
        const nb = images.length;

        if (nb <= 1) return;

        let indexActuel = 0;

        const afficher = (idx) => {
            indexActuel = (idx + nb) % nb;
            images.forEach((img, i) => {
                img.classList.toggle('opacity-100', i === indexActuel);
                img.classList.toggle('opacity-0', i !== indexActuel);
            });
            thumbs.forEach((t, i) => {
                if (i === indexActuel) {
                    t.classList.add('border-primary-500');
                    t.classList.remove('border-transparent');
                } else {
                    t.classList.remove('border-primary-500');
                    t.classList.add('border-transparent');
                }
            });
            if (counter) counter.textContent = (indexActuel + 1);
        };

        if (prevBtn) prevBtn.addEventListener('click', () => afficher(indexActuel - 1));
        if (nextBtn) nextBtn.addEventListener('click', () => afficher(indexActuel + 1));

        thumbs.forEach((t) => {
            t.addEventListener('click', () => afficher(parseInt(t.dataset.carrouselThumb)));
        });

        // Navigation au clavier quand le carrousel a le focus
        carrousel.tabIndex = 0;
        carrousel.addEventListener('keydown', (e) => {
            if (e.key === 'ArrowLeft') { e.preventDefault(); afficher(indexActuel - 1); }
            if (e.key === 'ArrowRight') { e.preventDefault(); afficher(indexActuel + 1); }
        });
    }
}


/**
 * Toggle AJAX des favoris articles.
 * HTML : <form data-favori-ajax action="/favori_toggle.php">
 */
class FavoriAjax {
    init() {
        document.querySelectorAll('form[data-favori-ajax]').forEach((form) => {
            form.addEventListener('submit', (e) => this.gerer(e, form));
        });
    }

    async gerer(e, form) {
        e.preventDefault();

        const bouton = form.querySelector('button[type="submit"]');
        if (!bouton) return;

        const textOrig = bouton.innerHTML;
        bouton.disabled = true;
        bouton.style.opacity = '0.6';

        try {
            const fd = new FormData(form);
            const resp = await fetch(form.action, {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await resp.json();

            if (data.succes) {
                // Mettre à jour le texte du bouton
                if (data.ajoute) {
                    bouton.innerHTML = '❤️ Retirer des favoris';
                } else {
                    bouton.innerHTML = '🤍 Ajouter aux favoris';
                }

                // Toast
                this.toast(data.message, 'success');
            } else {
                this.toast(data.message || 'Erreur', 'error');
                bouton.innerHTML = textOrig;
            }
        } catch (err) {
            console.error(err);
            this.toast('Erreur de connexion', 'error');
            bouton.innerHTML = textOrig;
        } finally {
            bouton.disabled = false;
            bouton.style.opacity = '1';
        }
    }

    toast(message, type) {
        // Réutilise le même système que PanierAjax
        const couleur = type === 'success'
            ? 'bg-success-50 border-success-300 text-success-800'
            : 'bg-danger-50 border-danger-300 text-danger-800';

        const t = document.createElement('div');
        t.className = `fixed top-20 right-6 z-50 max-w-sm px-4 py-3 rounded-lg border shadow-lg ${couleur}`;
        t.style.transition = 'all 0.3s';
        t.style.opacity = '0';
        t.innerHTML = `<div class="flex items-start gap-2"><span class="text-lg">${type === 'success' ? '✓' : '⚠'}</span><span class="text-sm font-medium">${this.echapper(message)}</span></div>`;
        document.body.appendChild(t);

        requestAnimationFrame(() => { t.style.opacity = '1'; });
        setTimeout(() => {
            t.style.opacity = '0';
            setTimeout(() => t.remove(), 300);
        }, 2500);
    }

    echapper(s) {
        const d = document.createElement('div');
        d.textContent = String(s);
        return d.innerHTML;
    }
}


/**
 * Toggle AJAX des likes (billets + commentaires).
 * HTML : <form data-like-ajax action="/like_toggle.php">
 */
class LikeAjax {
    init() {
        document.querySelectorAll('form[data-like-ajax]').forEach((form) => {
            form.addEventListener('submit', (e) => this.gerer(e, form));
        });
    }

    async gerer(e, form) {
        e.preventDefault();
        const bouton = form.querySelector('button[type="submit"]');
        if (!bouton) return;

        const compteurEl = form.querySelector('[data-like-count]');

        bouton.disabled = true;

        try {
            const resp = await fetch(form.action, {
                method: 'POST',
                body: new FormData(form),
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            });
            const data = await resp.json();

            if (data.succes) {
                // Toggle l'apparence du bouton
                if (data.ajoute) {
                    bouton.classList.add('text-red-500');
                } else {
                    bouton.classList.remove('text-red-500');
                }
                // Mettre à jour le compteur
                if (compteurEl) {
                    compteurEl.textContent = data.nb_likes;
                    // Animation bump
                    compteurEl.style.transform = 'scale(1.3)';
                    setTimeout(() => { compteurEl.style.transform = 'scale(1)'; }, 200);
                }
            }
        } catch (err) {
            console.error(err);
        } finally {
            bouton.disabled = false;
        }
    }
}


/**
 * Gestion du menu hamburger sur mobile.
 * Toggle ouverture/fermeture, gestion accessibilité (aria-expanded),
 * fermeture au clic d'un lien, fermeture à la touche Escape.
 */
class MenuMobile {
    init() {
        const btn = document.getElementById('btn-hamburger');
        const menu = document.getElementById('menu-mobile');
        const iconeHamburger = document.getElementById('icone-hamburger');
        const iconeFermer = document.getElementById('icone-fermer');

        if (!btn || !menu) return;

        const fermer = () => {
            menu.classList.add('hidden');
            btn.setAttribute('aria-expanded', 'false');
            btn.setAttribute('aria-label', 'Ouvrir le menu');
            if (iconeHamburger) iconeHamburger.classList.remove('hidden');
            if (iconeFermer) iconeFermer.classList.add('hidden');
        };

        const ouvrir = () => {
            menu.classList.remove('hidden');
            btn.setAttribute('aria-expanded', 'true');
            btn.setAttribute('aria-label', 'Fermer le menu');
            if (iconeHamburger) iconeHamburger.classList.add('hidden');
            if (iconeFermer) iconeFermer.classList.remove('hidden');
        };

        // Toggle au clic du bouton
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const estOuvert = btn.getAttribute('aria-expanded') === 'true';
            estOuvert ? fermer() : ouvrir();
        });

        // Fermer au clic en dehors
        document.addEventListener('click', (e) => {
            if (btn.getAttribute('aria-expanded') === 'true'
                && !menu.contains(e.target)
                && !btn.contains(e.target)) {
                fermer();
            }
        });

        // Fermer avec Escape
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && btn.getAttribute('aria-expanded') === 'true') {
                fermer();
            }
        });

        // Fermer si on passe en desktop (resize)
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                fermer();
            }
        });
    }
}


/**
 * Affiche une animation confetti quand un élément [data-confetti] est
 * présent dans la page (typiquement la confirmation de commande).
 *
 * Pure CSS/JS sans dépendance externe.
 * Génère N particules colorées qui tombent en tournant.
 */
class Confetti {
    init() {
        if (!document.querySelector('[data-confetti]')) return;
        this.lancer();
    }

    lancer() {
        const couleurs = ['#6366f1', '#22c55e', '#f59e0b', '#ec4899', '#3b82f6', '#a855f7'];
        const nb = 80;

        // Conteneur en position fixed
        const container = document.createElement('div');
        container.style.cssText = 'position:fixed;top:0;left:0;width:100%;height:100%;pointer-events:none;z-index:9999;overflow:hidden';
        document.body.appendChild(container);

        for (let i = 0; i < nb; i++) {
            this.creerParticule(container, couleurs);
        }

        // Nettoyer après 5 secondes
        setTimeout(() => container.remove(), 5000);
    }

    creerParticule(container, couleurs) {
        const p = document.createElement('div');
        const taille = 6 + Math.random() * 8;
        const couleur = couleurs[Math.floor(Math.random() * couleurs.length)];
        const gauche = Math.random() * 100;
        const duree = 2.5 + Math.random() * 2;
        const delai = Math.random() * 0.5;
        const rotationFin = Math.random() * 720 - 360;

        p.style.cssText = `
            position:absolute;
            top:-20px;
            left:${gauche}%;
            width:${taille}px;
            height:${taille}px;
            background:${couleur};
            border-radius:${Math.random() > 0.5 ? '50%' : '2px'};
            opacity:0.9;
            animation:confetti-fall ${duree}s ${delai}s cubic-bezier(0.4, 0, 0.6, 1) forwards;
            --rot-fin:${rotationFin}deg;
        `;

        container.appendChild(p);
    }
}


/**
 * Bouton "Retour en haut" qui apparaît quand on a scrollé.
 * S'insère automatiquement dans la page, pas besoin de HTML.
 */
class BoutonRetourHaut {
    init() {
        // Créer le bouton
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.setAttribute('aria-label', 'Retour en haut de la page');
        btn.innerHTML = `
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/>
            </svg>
        `;
        btn.className = 'fixed bottom-6 right-6 z-40 w-12 h-12 bg-primary-600 text-white rounded-full shadow-lg flex items-center justify-center hover:bg-primary-700 transition-all duration-300';
        btn.style.opacity = '0';
        btn.style.transform = 'translateY(20px)';
        btn.style.pointerEvents = 'none';
        document.body.appendChild(btn);

        // Apparaît après 300px de scroll
        const seuil = 300;
        let visible = false;

        const verifier = () => {
            const doitEtreVisible = window.scrollY > seuil;
            if (doitEtreVisible !== visible) {
                visible = doitEtreVisible;
                if (visible) {
                    btn.style.opacity = '1';
                    btn.style.transform = 'translateY(0)';
                    btn.style.pointerEvents = 'auto';
                } else {
                    btn.style.opacity = '0';
                    btn.style.transform = 'translateY(20px)';
                    btn.style.pointerEvents = 'none';
                }
            }
        };

        // Throttle léger (50ms)
        let timeoutId = null;
        window.addEventListener('scroll', () => {
            if (timeoutId) return;
            timeoutId = setTimeout(() => {
                verifier();
                timeoutId = null;
            }, 50);
        });

        // Clic = scroll smooth vers le haut
        btn.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        verifier();
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
            new PanierAjax(),
            new Autocomplete(),
            new CarrouselImages(),
            new FavoriAjax(),
            new LikeAjax(),
            new MenuMobile(),
            new Confetti(),
            new BoutonRetourHaut(),
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
