# La Gazette - Gazette Solutions

Ce projet est le site vitrine de **Gazette Solutions**, spécialisé dans les formalités juridiques et les annonces légales.

## 🛠 Stack Technique

- **Framework** : Symfony 7.4
- **CMS** : Prismic (via ``App\Service\PrismicService``)
- **Assets** : Symfony AssetMapper (Vanilla JS & CSS)
- **Templates** : Twig

## 📁 Structure des Assets (CSS)

Les fichiers CSS ont été réorganisés pour une meilleure clarté dans ``assets/css/`` :

- ``common/`` : Styles partagés (globals.css, styleguide.css)
- ``accueil/`` : Styles spécifiques à la page d'accueil
- `annonces_legales/` : Styles dédiés au service d'annonces légales
- ``actualites/`` : Styles pour la section blog et actualités

## 🚀 Installation & Développement

1.  **Pré-requis** : PHP 8.2+, Composer.
2.  **Installation** :
    ```bash
    composer install
    ```
3.  **Variables d'environnement** :
    Configurez votre fichier ``.env.local`` avec vos clés Prismic :
    ```env
    PRISMIC_REPO=votre-repo
    PRISMIC_TOKEN=votre-token
    ```
4.  **Lancement du serveur** :
    ```bash
    symfony server:start
    ```
    Ou via Laragon en pointant le DocumentRoot sur le dossier ``public/``.

## 📖 Documentation du Code

Le code utilise les standards **PHPDoc**. Pour générer la documentation technique, vous pouvez utiliser des outils comme ``phpDocumentor``.
