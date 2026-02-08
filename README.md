# Dashboard AudricSan

Dashboard de gestion pour serveur local WAMP/XAMPP avec architecture MVC, monitoring temps reel et gestion des services.

## Apercu des fonctionnalites

- Monitoring CPU/RAM en temps reel avec graphiques historiques
- Gestion des VirtualHosts Apache (CRUD complet)
- Controle des services (Apache, MySQL, FileZilla, Mercury)
- Visualiseur de logs Apache (erreurs et acces)
- Detection automatique des projets dans htdocs
- Theme sombre/clair automatique
- API JSON pour integration externe
- Compatible Windows, Linux et macOS

## Prerequis

- **PHP** >= 7.4
- **Apache** avec `mod_rewrite` active
- **Composer** (pour l'autoloading PSR-4)
- **MySQL/MariaDB** (optionnel, pour la detection de version)
- **XAMPP** ou **WAMP** (environnement recommande)

## Installation

```bash
# 1. Cloner le projet dans htdocs
cd C:\xampp\htdocs
git clone <url-du-repo> dashboard

# 2. Installer l'autoloader
cd dashboard
composer install
# ou si vendor/ existe deja :
composer dump-autoload

# 3. Configurer les chemins et identifiants
# Editer App/config/Settings.php selon votre environnement

# 4. S'assurer que le dossier storage est accessible en ecriture
# (necessaire pour l'historique de monitoring)

# 5. Acceder au dashboard
# http://localhost/dashboard/
```

## Configuration

### Fichier `App/config/Settings.php`

```php
// --- Base de donnees (optionnel, pour detection de version) ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// --- Chemins ---
define('HTDOCS_PATH', dirname(__DIR__, 3));
define('VHOSTS_CONF', 'C:/xampp/apache/conf/extra/httpd-vhosts.conf');
define('APACHE_LOGS_PATH', 'C:/xampp/apache/logs');

// --- Dossiers exclus de la liste des projets ---
define('EXCLUDED_DIRS', [
    '00_PHPInfo', '00_PHPModels', '00_SQL', 'dashboard',
    'dashboard.back', 'img', 'Localfont', 'POO',
    'webalizer', 'xampp',
]);

// --- Interface ---
define('DASHBOARD_TITLE', 'Audric Server');
define('DASHBOARD_SUBTITLE', 'Apache - MySQL - PHP');
define('REFRESH_INTERVAL', 30000); // Intervalle de rafraichissement en ms
```

## Structure du projet

```
dashboard/
├── index.php                    # Redirection vers Public/
├── composer.json                # Autoloading PSR-4
├── .htaccess                    # Protection des dossiers racine
│
├── App/
│   ├── config/
│   │   └── Settings.php         # Configuration globale
│   ├── Controllers/
│   │   ├── HomeController.php           # Page d'accueil
│   │   ├── VirtualHostController.php    # Gestion VHosts (CRUD)
│   │   ├── ServiceController.php        # Controle des services
│   │   ├── ApacheLogController.php      # Visualiseur de logs
│   │   └── api/
│   │       ├── ServerStatusController.php   # API statut serveur
│   │       └── MonitoringController.php     # API monitoring CPU/RAM
│   ├── Models/
│   │   ├── SystemInfo.php           # Metriques systeme (CPU, RAM, disque)
│   │   ├── Project.php              # Scan des projets htdocs
│   │   ├── VirtualHost.php          # Lecture/ecriture httpd-vhosts.conf
│   │   └── MonitoringHistory.php    # Stockage historique JSON
│   └── Helpers/
│       └── Functions.php            # Utilitaires (escape, icon, redirect, view)
│
├── Views/
│   ├── home.php                     # Vue dashboard principal
│   └── pages/
│       ├── vhost_form.php           # Formulaire VirtualHost
│       ├── services.php             # Page de gestion des services
│       └── logs.php                 # Page visualiseur de logs
│
├── Public/                          # Dossier public (seul accessible via HTTP)
│   ├── index.php                    # Front Controller & Routeur
│   ├── .htaccess                    # Reecriture d'URL
│   └── assets/
│       ├── css/style.css            # Feuille de style principale
│       ├── js/
│       │   ├── main.js              # AJAX, theme, interactions
│       │   └── monitoring.js        # Graphiques Chart.js
│       ├── images/
│       │   ├── logo.png             # Logo mode clair
│       │   └── logoBN.png           # Logo mode sombre
│       └── html/
│           ├── header.php           # En-tete commun
│           └── footer.php           # Pied de page commun
│
├── storage/
│   └── monitoring/
│       └── history.json             # Historique CPU/RAM (JSON)
│
└── vendor/                          # Autoload Composer
```

## Architecture MVC

### Modeles

| Modele | Responsabilite |
|--------|---------------|
| `SystemInfo` | Collecte des metriques systeme : CPU, RAM, disque, versions PHP/Apache/MySQL |
| `Project` | Scan des repertoires htdocs, tri par date, exclusion configurable |
| `VirtualHost` | Parsing et ecriture du fichier `httpd-vhosts.conf` |
| `MonitoringHistory` | Stockage/lecture de l'historique CPU/RAM en JSON (120 points max) |

### Controleurs

| Controleur | Route(s) | Role |
|------------|----------|------|
| `HomeController` | `/` | Affiche le dashboard principal |
| `VirtualHostController` | `/vhosts`, `/vhosts/store` | CRUD des VirtualHosts |
| `ServiceController` | `/services` | Demarrage/arret/redemarrage des services |
| `ApacheLogController` | `/logs` | Affichage des logs Apache |
| `ServerStatusController` | `/api/server-status` | API JSON statut serveur |
| `MonitoringController` | `/api/monitoring/*` | API JSON monitoring temps reel |

### Vues

Les vues contiennent uniquement du HTML/PHP d'affichage. Elles recoivent les donnees via `Functions::view()` qui extrait les variables dans le scope local.

## Fonctionnalites detaillees

### Dashboard principal (`/`)

**Informations systeme :**
- Hostname, OS, espace disque (utilise/libre/total en Go)
- Utilisation RAM avec pourcentage
- Versions Apache, PHP, MySQL/MariaDB avec liens vers la documentation

**Monitoring temps reel :**
- Jauges circulaires animees pour CPU et RAM
- Graphique historique sur 30 minutes (Chart.js)
- Graphique historique sur 1 heure
- Rafraichissement automatique toutes les 30 secondes
- Code couleur : vert (<60%), orange (60-80%), rouge (>80%)

**Liste des projets :**
- Scan automatique du dossier htdocs
- Date de derniere modification (format JJ/MM/AAAA)
- Code couleur pour les 3 projets les plus recents (rouge, jaune, vert)
- Liens directs vers chaque projet
- Exclusion configurable de dossiers systeme

**VirtualHosts :**
- Liste des VHosts configures avec liens d'acces
- Boutons d'edition et suppression rapide

**Outils :**
- phpinfo(), phpMyAdmin, gestion VHosts, services, logs

### Gestion des VirtualHosts (`/vhosts`)

- Creation avec attribution automatique d'IP (127.0.0.X)
- Edition des VHosts existants
- Suppression
- Validation du format IP (regex `^127\.0\.0\.\d{1,3}$`)
- Ecriture directe dans `httpd-vhosts.conf`
- Generation des blocs VirtualHost avec logs d'erreurs/acces

### Gestion des services (`/services`)

Services supportes (Windows) :
- **Apache** (httpd.exe)
- **MySQL/MariaDB** (mysqld.exe)
- **FileZilla FTP** (si installe)
- **Mercury Mail** (si installe)

Operations disponibles :
- Demarrer / Arreter / Redemarrer
- Detection de statut en temps reel (via `tasklist` sous Windows, `pgrep` sous Linux)
- Indicateurs visuels (pastille verte/rouge)

### Visualiseur de logs (`/logs`)

- Basculement entre logs d'erreur et logs d'acces
- Affichage des N dernieres lignes (10 a 1000)
- Taille du fichier affichee (octets/Ko/Mo/Go)
- Coloration par niveau de severite :
  - Rouge : Error, Critical, Alert, Emergency
  - Orange : Warning
  - Bleu : Notice
  - Noir : Info

## API JSON

### `GET /api/server-status`

Retourne un instantane complet du systeme :

```json
{
  "hostname": "DESKTOP-XXX",
  "os": "Windows NT ...",
  "phpVersion": "8.2.12",
  "disk_total_gb": 500.0,
  "disk_free_gb": 250.0,
  "disk_used_gb": 250.0,
  "disk_percent": 50.0,
  "mem_total": "16.00 Go",
  "mem_free": "8.00 Go",
  "mem_percent": 50.0,
  "cpu_percent": 25.5,
  "apacheVersion": "2.4.58",
  "mysqlVersion": "...",
  "mariadbVersion": "..."
}
```

### `GET /api/monitoring/current`

Retourne les valeurs actuelles et l'historique recent :

```json
{
  "current": { "cpu": 25.5, "ram": 50.0 },
  "history": { "cpu": [...], "ram": [...] },
  "timestamp": 1234567890
}
```

### `GET /api/monitoring/history`

Retourne l'historique complet sur 1 heure (120 points a 30s d'intervalle).

## Routing

Le routeur (`Public/index.php`) utilise `.htaccess` pour des URLs propres :

| Route | Methode | Controleur |
|-------|---------|-----------|
| `/` | GET | `HomeController::index()` |
| `/vhosts` | GET | `VirtualHostController::index()` |
| `/vhosts/store` | POST | `VirtualHostController::store()` |
| `/services` | GET | `ServiceController::index()` |
| `/logs` | GET | `ApacheLogController::index()` |
| `/api/server-status` | GET | `ServerStatusController::index()` |
| `/api/monitoring/current` | GET | `MonitoringController::current()` |
| `/api/monitoring/history` | GET | `MonitoringController::history()` |

Le routeur detecte automatiquement le chemin de base, ce qui permet une installation en sous-dossier (ex: `http://localhost/mon-dossier/dashboard/`).

## Autoloading PSR-4

Le namespace `App\` est mappe au dossier `App/` via Composer :

```json
{
  "autoload": {
    "psr-4": {
      "App\\": "App/"
    }
  }
}
```

Apres modification des namespaces, regenerer l'autoload :

```bash
composer dump-autoload
```

## Helpers disponibles

```php
Functions::escape($text)        // Echappement HTML (htmlspecialchars ENT_QUOTES)
Functions::icon($name)          // Generation d'icone Material Symbols
Functions::redirect($url)       // Redirection HTTP
Functions::view($name, $data)   // Chargement d'une vue avec extraction des donnees
```

## Theme sombre automatique

Le dashboard detecte la preference systeme via CSS `prefers-color-scheme` et adapte :
- Les couleurs de l'interface
- Le logo (logo.png / logoBN.png)
- Les couleurs des graphiques Chart.js

## Securite

- **Acces restreint** : seul `Public/` est accessible via HTTP. Les dossiers `App/`, `Views/`, `vendor/`, `storage/` sont proteges par `.htaccess`
- **Echappement XSS** : toutes les sorties utilisent `Functions::escape()`
- **Validation des entrees** : IP VirtualHost validee par regex, champs de formulaire assainis
- **Base de donnees** : reporting d'erreurs desactive, timeout de connexion de 2 secondes
- **Usage local uniquement** : ce dashboard n'est pas concu pour un deploiement en production (pas d'authentification, exposition d'informations systeme)

## Performances

- **Cache statique** : CSS/JS mis en cache 1 semaine, images 1 mois (via `mod_expires`)
- **Compression** : Gzip active pour HTML/CSS/JS (via `mod_deflate`)
- **Monitoring leger** : seules les donnees JSON sont rechargees (pas de rechargement de page)
- **Historique limite** : 120 points maximum pour eviter la croissance du fichier JSON

## Personnalisation

### Ajouter une route

Editer `Public/index.php` :

```php
$routes = [
    // ...
    '/ma-route' => [MonController::class, 'maMethode'],
];
```

### Creer un controleur

```php
namespace App\Controllers;
use App\Helpers\Functions;

class MonController
{
    public function maMethode()
    {
        $data = ['cle' => 'valeur'];
        Functions::view('ma_vue', $data);
    }
}
```

### Creer un modele

```php
namespace App\Models;

class MonModele
{
    public static function getAll(): array
    {
        // Logique metier...
        return $data;
    }
}
```

### Modifier le style

Editer `Public/assets/css/style.css`. Le layout utilise CSS Grid responsive avec support du mode sombre via des variables CSS.

## Technologies

| Composant | Technologie |
|-----------|-------------|
| Backend | PHP >= 7.4, architecture MVC |
| Frontend | HTML5, CSS3 responsive, JavaScript ES6+ |
| Graphiques | Chart.js 4.4.1 (CDN) |
| Icones | Material Symbols Outlined (Google Fonts) |
| Autoloading | Composer PSR-4 |
| Stockage monitoring | JSON (aucune base de donnees requise) |
| Serveur | Apache avec mod_rewrite |

## Compatibilite multiplateforme

| Fonctionnalite | Windows | Linux | macOS |
|----------------|---------|-------|-------|
| Metriques RAM | PowerShell WMI | /proc/meminfo | sysctl |
| Usage CPU | PowerShell LoadPercentage | /proc/stat | top |
| Statut services | tasklist.exe | pgrep | pgrep |
| Espace disque | disk_total_space() | disk_total_space() | disk_total_space() |

## Licence

MIT - Voir le fichier [LICENSE](LICENSE).

---

**Developpe par AudricSan** - Version 3.0
