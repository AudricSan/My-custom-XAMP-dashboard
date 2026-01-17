<?php
// Configuration globale du dashboard

// Chemin racine du serveur (htdocs)
define('HTDOCS_PATH', dirname(__DIR__, 3));

// Dossiers à exclure de la liste des projets
define('EXCLUDED_DIRS', [
    '00_PHPInfo',
    '00_PHPModels',
    '00_SQL',
    'dashboard',
    'dashboard.back',
    'img',
    'Localfont',
    'POO',
    'webalizer',
    'xampp',
]);

// Chemin du fichier de configuration des VirtualHosts
// Remonter depuis htdocs vers le dossier XAMPP racine
define('VHOSTS_CONF', dirname(HTDOCS_PATH) . '/apache/conf/extra/httpd-vhosts.conf');

// Chemin du dossier des logs Apache
define('APACHE_LOGS_PATH', dirname(HTDOCS_PATH) . '/apache/logs');

// Configurations de base de données (si nécessaire)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_PORT', '3306');

// Intervalle de rafraîchissement des infos système (en millisecondes)
define('REFRESH_INTERVAL', 30000);

// Titre du dashboard
define('DASHBOARD_TITLE', 'Audric Server');
define('DASHBOARD_SUBTITLE', 'Apache - MySQL - PHP');

// Base URL pour les assets et liens (détection automatique du sous-dossier)
$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
define('BASE_URL', $scriptDir !== '/' ? $scriptDir : '');
