# Instructions & Recommandations Techniques - La Gazette

Ce document regroupe les recommandations de stack technique et les bonnes pratiques de sécurité pour le projet **La Gazette**.

## 1. La Stack Technique Recommandée
 ___________________________________________________________________________________________________________
| Composant         | Technologie            | Pourquoi ?                                                   |
| :---              | :---                   | :---                                                         |
| **Framework**     | Symfony 7.x            | Version la plus récente, optimisée pour PHP 8.2+.            |
| **CMS**           | Prismic                | Gestion de contenu déconnectée, réduit la surface d'attaque. |
| **Client API**    | Symfony HTTP Client    | Plus sécurisé et performant que Guzzle.                      |
| **Sécurité XSS**  | symfony/html-sanitizer | Nettoyage du HTML provenant du CMS avant affichage.          |
| **Images**        | symfony/ux-lazy-image  | Lazy loading pour préserver les performances Lighthouse.     |
| **Cache**         | Symfony Cache (Redis)  | Protection du quota API et performance.                      |
| **Sécurité**      | NelmioCorsBundle + CSP | Gestion stricte des en-têtes de sécurité.                    |
|___________________________________________________________________________________________________________|


## 2. Le Rendu du Rich Text et la Sécurité XSS

Le document mentionne la validation des entrées, mais pas la sortie. Prismic renvoie souvent du HTML ou du JSON structuré (Rich Text) que tu vas devoir afficher en Twig.

- **Le problème** : L'utilisation du filtre `|raw` dans Twig peut introduire des failles XSS si le contenu du CMS est compromis ou malveillant.
- **Recommandation** : Ajoute le `symfony/html-sanitizer`. Même si tu as confiance en Prismic, ne fais jamais un `|raw` aveugle. Utilise le sanitizer pour nettoyer le HTML avant de l'afficher.

---

## 3. La gestion des images

Prismic héberge les images (souvent sur Imgix).

- **Performance** : Sers les URLs Prismic directes (`images.prismic.io/...`). Elles bénéficient déjà d'un CDN performant.
- **Optimisation** : Utilise `symfony/ux-lazy-image` (ou du lazy loading natif via l'attribut `loading="lazy"`) pour ne pas dégrader le score Lighthouse de "La Gazette".

---

## 4. Architecture de Sécurité : Le "Proxy" Backend

L'erreur classique est de laisser le Front-end appeler l'API de Prismic directement avec une clé exposée. Pour une sécurité maximale, utilise Symfony comme passerelle :

- **Isolation des Clés** : Stocke ton `PRISMIC_API_TOKEN` uniquement dans le fichier `.env.local` ou les secrets Symfony (Vault). Jamais côté client.
- **Filtrage des Données** : Dans ton contrôleur Symfony, ne récupère que les champs nécessaires de l'objet Prismic avant de les envoyer à Twig. Cela évite l'exposition accidentelle de métadonnées sensibles du CMS.

---

## 5. Checklist Sécurité Symfony

### ✅ Durcissement du Framework
- **Symfony Security Bundle** : Même pour un site vitrine sans login, configure un firewall strict.
- **Content Security Policy (CSP)** : Utilise le bundle `base-core` ou configure manuellement tes en-têtes pour bloquer l'exécution de scripts tiers non autorisés.
- **Rate Limiter** : Utilise le composant `RateLimiter` de Symfony sur tes routes pour éviter le "scraping" intensif de ton contenu vitrine.

### ✅ Sécurisation de l'intégration Prismic
- **Webhooks Sécurisés** : Prismic peut envoyer un webhook à Symfony pour vider le cache quand un article est publié. Vérifie impérativement le "Secret" envoyé par Prismic dans le header du webhook.
- **Validation des Entrées** : Si tu utilises des paramètres d'URL (slugs), passe-les toujours par une validation stricte pour éviter les injections de requêtes API.

---

## 6. Performance & Disponibilité

- **Stale-while-revalidate** : Configure ton cache pour qu'en cas de panne de l'API Prismic, Symfony puisse servir une version "obsolète mais saine" de la page.
- **Composant AssetMapper** : Pour un projet vitrine, évite la complexité de Webpack Encore. AssetMapper (natif Symfony) est plus léger et moderne.
- **Conseil de "pro"** : Utilise les Secrets Symfony (`php bin/console secrets:set PRISMIC_TOKEN`) plutôt que de simples variables d'environnement pour tes clés de production. Elles seront chiffrées au repos.
