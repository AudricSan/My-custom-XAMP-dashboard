# Dashboard AudricSan - Documentation

Dashboard personnalisé pour serveur local WAMP/XAMPP avec une architecture MVC moderne et propre.

## Structure du projet (Architecture MVC)

```
dashboard/
├── index.php                           # Redirection vers Public/
├── composer.json                       # Configuration Composer & Autoloading PSR-4
├── .htaccess                          # Protection dossiers racine
├── App/
│   ├── Config/
│   │   └── Settings.php               # Configuration globale (constantes)
│   ├── Controllers/
│   │   ├── HomeController.php         # Contrôleur page d'accueil
│   │   ├── VirtualHostController.php  # Contrôleur gestion VHosts
│   │   └── Api/
│   │       └── ServerStatusController.php  # API status serveur
│   ├── Models/
│   │   ├── SystemInfo.php             # Modèle infos système
│   │   ├── Project.php                # Modèle gestion projets
│   │   └── VirtualHost.php            # Modèle gestion VHosts
│   └── Helpers/
│       └── Functions.php              # Fonctions utilitaires
├── Views/
│   ├── layouts/
│   │   └── main.php                   # Layout principal (optionnel)
│   ├── home.php                       # Vue page d'accueil
│   └── pages/
│       └── vhost_form.php             # Vue gestion VHosts
├── Public/                            # Dossier public (DocumentRoot)
│   ├── index.php                      # Front Controller (routing)
│   ├── .htaccess                      # Réécriture URL
│   └── assets/
│       ├── css/
│       │   └── style.css              # CSS principal
│       ├── js/
│       │   └── main.js                # JavaScript
│       └── images/
│           ├── logo.png               # Logo mode clair
│           └── logoBN.png             # Logo mode sombre
└── vendor/                            # Dépendances Composer (autoload)
```

## Architecture MVC

### Modèles (Models)
Les modèles gèrent la logique métier et l'accès aux données :
- **SystemInfo** : Récupération des informations système (CPU, RAM, disque, versions)
- **Project** : Gestion et listage des projets dans htdocs
- **VirtualHost** : Manipulation des VirtualHosts Apache

### Contrôleurs (Controllers)
Les contrôleurs orchestrent le flux de l'application :
- **HomeController** : Affiche la page d'accueil du dashboard
- **VirtualHostController** : Gère la création/édition/suppression des VHosts
- **Api/ServerStatusController** : API JSON pour rafraîchissement temps réel

### Vues (Views)
Les vues contiennent uniquement le code HTML/PHP d'affichage :
- **home.php** : Page d'accueil complète
- **pages/vhost_form.php** : Formulaire de gestion des VHosts
- **layouts/main.php** : Layout de base (optionnel)

### Front Controller
Le fichier `Public/index.php` est le point d'entrée unique qui :
1. Charge la configuration
2. Initialise l'autoloader PSR-4
3. Route les requêtes vers les bons contrôleurs
4. Gère les erreurs 404

## Fonctionnalités

### Page d'accueil (/)
- **Informations système** : Hostname, OS, espace disque, mémoire
- **Versions des services** : Apache, PHP, MySQL/MariaDB
- **Statuts en temps réel** : Apache, MySQL, phpMyAdmin
- **Liste des projets** : Avec dates de modification et code couleur (3 plus récents)
- **Liste des VirtualHosts** : Accès rapide aux vhosts configurés
- **Outils** : phpinfo(), phpMyAdmin, xdebug_info(), gestion VHosts

### Gestion VirtualHosts (/vhosts)
- Création de nouveaux VirtualHosts
- Édition de VirtualHosts existants
- Suppression de VirtualHosts
- Attribution automatique d'IP (127.0.0.X)

### API (/api/server-status)
- Endpoint JSON pour récupération des infos système
- Utilisé par le JavaScript pour rafraîchissement automatique toutes les 30s

## Configuration

### Fichier App/Config/Settings.php

Modifiez ce fichier pour personnaliser :

```php
// Dossiers à exclure de la liste des projets
define('EXCLUDED_DIRS', [...]);

// Chemin vers le fichier de configuration des VirtualHosts
define('VHOSTS_CONF', '...');

// Identifiants MySQL/MariaDB
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');

// Intervalle de rafraîchissement auto (en ms)
define('REFRESH_INTERVAL', 30000);

// Titres du dashboard
define('DASHBOARD_TITLE', 'Audric Server');
define('DASHBOARD_SUBTITLE', 'Apache - MySQL - PHP');
```

## Installation

1. Cloner le projet dans votre htdocs
2. Exécuter `composer install` (ou `composer dump-autoload`)
3. Configurer `App/Config/Settings.php` selon vos besoins
4. Accéder à http://localhost/dashboard/

## Routing (URLs propres)

Le système de routing utilise `.htaccess` pour des URLs propres :

- `/` → HomeController::index()
- `/vhosts` → VirtualHostController::index()
- `/vhosts/store` → VirtualHostController::store()
- `/api/server-status` → ServerStatusController::index()

## Autoloading PSR-4

Le projet utilise l'autoloading PSR-4 via Composer :
- Namespace `App\` → Dossier `App/`
- Ex: `App\Models\SystemInfo` → `App/Models/SystemInfo.php`

Après modification du namespace, régénérer l'autoload :
```bash
composer dump-autoload
```

## Helpers disponibles

La classe `App\Helpers\Functions` fournit des méthodes utilitaires :

```php
Functions::escape($text)        // Échapper HTML
Functions::icon($name)          // Générer icône Material
Functions::isXdebugEnabled()    // Vérifier Xdebug
Functions::redirect($url)       // Redirection HTTP
Functions::view($name, $data)   // Charger une vue
```

## Thème sombre automatique

Le dashboard détecte automatiquement la préférence de thème du système (via CSS `prefers-color-scheme`) et s'ajuste en conséquence. Le logo change également (logo.png ↔ logoBN.png).

## Sécurité

- Le fichier `.htaccess` redirige toutes les requêtes vers le Front Controller
- Seul le dossier `Public/` est accessible directement
- Toutes les sorties utilisent `Functions::escape()` pour prévenir XSS
- Validation des entrées utilisateur (IP, formulaires)
- Les dossiers `App/`, `Views/`, `vendor/` ne sont pas accessibles via HTTP

## Rafraîchissement automatique

Les informations système sont rafraîchies toutes les 30 secondes via l'API `/api/server-status` sans rechargement de la page (AJAX).

## Personnalisation

### Ajouter une nouvelle route

Éditez `Public/index.php` :

```php
$routes = [
    // ...
    '/ma-route' => [MonController::class, 'maMethode'],
];
```

### Créer un nouveau contrôleur

```php
namespace App\Controllers;
use App\Helpers\Functions;

class MonController
{
    public function maMethode()
    {
        // Logique...
        Functions::view('ma_vue', ['data' => $data]);
    }
}
```

### Créer un nouveau modèle

```php
namespace App\Models;

class MonModele
{
    public static function getAll()
    {
        // Logique métier...
        return $data;
    }
}
```

### Modifier le style

Éditez `Public/assets/css/style.css` pour personnaliser l'apparence.

## Dépendances

- **PHP** : >= 7.4
- **Apache** : mod_rewrite activé
- **Composer** : Autoloading PSR-4
- **MySQL/MariaDB** : Optionnel (pour stats DB)

## Support

Pour toute question ou problème :
- Consultez la documentation Apache/PHP
- Ouvrez une issue sur GitHub
- Contactez l'administrateur système

---

**Développé par AudricSan**
Version 3.0 - Architecture MVC avec autoloading PSR-4
Licence : MIT
