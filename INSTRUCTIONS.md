# Instructions & Recommandations Techniques - La Gazette

Ce document regroupe les recommandations de stack technique et les bonnes pratiques de sécurité pour le projet **La Gazette**.

---

## 1. La Stack Technique Recommandée

| Composant | Technologie | Pourquoi ? |
| :--- | :--- | :--- |
| **Framework** | Symfony 7.x | Version la plus récente, optimisée pour PHP 8.2+. |
| **CMS** | Prismic | Gestion de contenu déconnectée, réduit la surface d'attaque (pas de base de données SQL exposée). |
| **Client API** | Symfony HTTP Client | Plus sécurisé et performant que Guzzle pour consommer l'API de Prismic. |
| **Cache** | Symfony Cache (Redis) | Indispensable pour ne pas appeler Prismic à chaque requête et protéger ton quota API. |
| **Sécurité** | NelmioCorsBundle + CSP | Gestion stricte des en-têtes de sécurité. |

---

## 2. Architecture de Sécurité : Le "Proxy" Backend

L'erreur classique est de laisser le Front-end appeler l'API de Prismic directement avec une clé exposée. Pour une sécurité maximale, utilise Symfony comme passerelle :

- **Isolation des Clés** : Stocke ton `PRISMIC_API_TOKEN` uniquement dans le fichier `.env.local` ou les secrets Symfony (Vault). Jamais côté client.
- **Filtrage des Données** : Dans ton contrôleur Symfony, ne récupère que les champs nécessaires de l'objet Prismic avant de les envoyer à Twig. Cela évite l'exposition accidentelle de métadonnées sensibles du CMS.

---

## 3. Checklist Sécurité Symfony

### ✅ Durcissement du Framework
- **Symfony Security Bundle** : Même pour un site vitrine sans login, configure un firewall strict.
- **Content Security Policy (CSP)** : Utilise le bundle `base-core` ou configure manuellement tes en-têtes pour bloquer l'exécution de scripts tiers non autorisés.
- **Rate Limiter** : Utilise le composant `RateLimiter` de Symfony sur tes routes pour éviter le "scraping" intensif de ton contenu vitrine.

### ✅ Sécurisation de l'intégration Prismic
- **Webhooks Sécurisés** : Prismic peut envoyer un webhook à Symfony pour vider le cache quand un article est publié. Vérifie impérativement le "Secret" envoyé par Prismic dans le header du webhook pour éviter que n'importe qui puisse purger ton cache.
- **Validation des Entrées** : Si tu utilises des paramètres d'URL (slugs) pour requêter Prismic, passe-les toujours par une validation stricte pour éviter les injections de requêtes API.

---

## 4. Performance & Disponibilité

La sécurité, c'est aussi garantir que le site reste en ligne (contre les attaques DoS) :

- **Stale-while-revalidate** : Configure ton cache pour qu'en cas de panne de l'API Prismic, Symfony puisse servir une version "obsolète mais saine" de la page.
- **Composant AssetMapper** : Pour un projet vitrine, évite la complexité de Webpack Encore. AssetMapper (natif Symfony) est plus léger, plus sûr (pas de dépendances `node_modules` massives côté serveur) et moderne.
- **Conseil de "pro"** : Utilise les Secrets Symfony (`php bin/console secrets:set PRISMIC_TOKEN`) plutôt que de simples variables d'environnement pour tes clés de production. Elles seront chiffrées au repos.
