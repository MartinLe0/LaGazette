 __________________________________________________________________________________________________________
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