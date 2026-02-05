# La Gazette

Bienvenue sur le dépôt du projet **La Gazette**. Application web vitrine et blog connectée au CMS Prismic, propulsée par Symfony.

## Prérequis

* **PHP** : 8.2 ou supérieur
* **Composer** : Gestionnaire de dépendances PHP
* **Node.js & NPM**
* **Symfony CLI**

## Installation

1. **Cloner le projet**
    ```bash
    git clone [REPO_URL]
    cd La_Gazette
    ```

2. **Dépendances**
    ```bash
    composer install
    npm install
    ```

3. **Configuration**
    Créer `.env.local` :
    ```dotenv
    APP_ENV=dev
    PRISMIC_REPO_NAME=...
    PRISMIC_ACCESS_TOKEN=...
    ```

4. **Build & Serve**
    ```bash
    npm run dev
    symfony serve -d
    ```
    Le site sera accessible sur `https://127.0.0.1:8000`.

## Commandes Utiles

| Commande | Description |
| :--- | :--- |
| `npm run build` | Compile les assets pour la production (minifiés). |
| `php bin/console cache:clear` | Vide le cache de l'application (y compris cache Prismic). |
| `php bin/console debug:router` | Liste toutes les routes de l'application. |
| `composer require [package]` | Ajoute une nouvelle librairie PHP. |

## Documentation Technique

Pour comprendre les choix d'architecture, la sécurité et le fonctionnement du CMS, référez-vous au fichier [ARCHITECTURE.md](ARCHITECTURE.md).

## Structure

* `assets/` : Fichiers sources (CSS, JS). C'est ici qu'on modifie le style.
* `templates/` : Fichiers HTML (Twig). C'est ici qu'on modifie le HTML.
* `src/` : Code PHP (Logique).

## Sécurité

Ce projet implémente des **Content Security Policies (CSP)** strictes. Si vous ajoutez un script externe ou une source d'image, vous devrez mettre à jour `src/EventSubscriber/SecurityHeadersSubscriber.php`.