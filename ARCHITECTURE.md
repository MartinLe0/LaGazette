# Architecture Technique - La Gazette

Ce document explique les choix techniques, l'architecture du code et les stratégies de sécurité mises en place pour le projet **La Gazette**.

## 1. Stack Technique

| Composant | Technologie | Justification |
| :--- | :--- | :--- |
| **Framework** | **Symfony 7.2** | Standard industriel, robuste, typé, et sécurisé. |
| **Langage** | **PHP 8.2+** | Performance et typage fort. |
| **Frontend** | **Twig** + **CSS Natif** | Moteur de template puissant. Pas de framework JS lourd (React/Vue) pour maximiser le SEO et la performance (Lighthouse). |
| **Asset Manager** | **Webpack Encore** | Compilation et minification des assets SCSS/JS. |
| **Sécurité XSS** | **HtmlSanitizer** | Nettoyage du HTML provenant du CMS. |
| **Images** | **LazyImage** | Optimisation du chargement des images (Lighthouse). |
| **CMS** | **Prismic (Headless)** | Gestion de contenu déconnectée. Sécurité accrue (pas de base de données locale vulnérable type SQL Injection via le CMS). |
| **Cache** | **Symfony Cache (Filesystem/Redis)** | Performance critique pour éviter les appels API Prismic répétés. |

## 2. Structure du Projet

L'organisation suit le standard Symfony avec quelques spécificités :

```
/
├── assets/             # Sources SCSS, JS, Images
├── config/             # Configuration Symfony (routes, services, packages)
├── public/             # Point d'entrée (index.php) et assets compilés
├── src/
│   ├── Controller/     # Logique de requête (Pages, Articles, Juridique)
│   ├── EventSubscriber/# Gestion globale (ex: En-têtes de sécurité)
│   ├── Service/        # Logique métier (ex: PrismicService)
│   └── Twig/           # Extensions pour le rendu (Filtres Prismic)
└── templates/          # Vues Twig (Layouts, Pages, Components)
```

## 3. Sécurité & Performance

### 3.1 Content Security Policy (CSP) & XSS
Un `SecurityHeadersSubscriber` injecte des en-têtes stricts pour prévenir les attaques XSS et l'injection de code.
De plus, `symfony/html-sanitizer` est utilisé pour filtrer tout contenu HTML provenant du CMS avant affichage.
*   **Autorisé** : Scripts locaux, Google Fonts, Images Prismic/Anima.
*   **Bloqué** : `frame-ancestors 'none'` (anti-clickjacking), `eval()` (sauf strict nécessaire).

### 3.2 Gestion du CMS (Prismic)
L'intégration Prismic se fait via `PrismicService`.
*   **Pattern Proxy** : Le front n'appelle jamais l'API Prismic directement (pas de token public). Tout passe par le backend Symfony.
*   **Smart Caching** : Les réponses API sont mises en cache.
*   **Webhooks** : Un endpoint sécurisé (`/webhook/prismic`) permet à Prismic de vider le cache Symfony lors d'une publication.

### 3.3 Performance
*   **Lazy Loading** : Supporté via `symfony/ux-lazy-image`.
*   **Assets** : Versioning et minification pour la production.
*   **Rate Limiting** : Protection contre le scraping intempestif sur les routes publiques.

## 4. Flux de Données

1.  **Requête Utilisateur** -> `PageController`.
2.  **Controller** -> Demande le contenu à `PrismicService`.
3.  **PrismicService** -> Vérifie le **Cache**.
    *   *Si Hit* : Retourne le JSON caché.
    *   *Si Miss* : Appelle l'API Prismic, stocke en cache, retourne le JSON.
4.  **Controller** -> Envoie les données à **Twig**.
5.  **Twig** -> Rend le HTML, utilise `PrismicExtension` pour transformer le RichText en HTML.
6.  **Subscriber** -> Ajoute les headers de sécurité avant l'envoi au navigateur.

## 5. Déploiement

Le projet est "Stateless" (pas de base de données locale critique), ce qui facilite le déploiement sur n'importe quel conteneur (Docker, VPS, PaaS).
Seules les **variables d'environnement** (`.env.local` ou Secrets) sont nécessaires pour la configuration (Clés API Prismic, Mode App).
