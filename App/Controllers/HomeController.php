<?php

namespace App\Controllers;

use App\Models\SystemInfo;
use App\Models\Project;
use App\Models\VirtualHost;
use App\Helpers\Functions;

/**
 * Contrôleur pour la page d'accueil
 */
class HomeController
{
    /**
     * Affiche la page d'accueil du dashboard
     */
    public function index()
    {
        // Affichage phpinfo
        if (isset($_GET['phpinfo'])) {
            phpinfo();
            exit();
        }

        // Récupérer les données
        $sysInfo = SystemInfo::getAll();
        $projectsData = Project::getAllWithMetadata();
        $projectColors = Project::getProjectColors($projectsData);
        $virtualhosts = VirtualHost::getAllDetailed();

        // Debug temporaire - désactivé
        // echo '<pre>VHOSTS_CONF: ' . (defined('VHOSTS_CONF') ? VHOSTS_CONF : 'NON DÉFINI') . '</pre>';
        // echo '<pre>File exists: ' . (file_exists(VHOSTS_CONF) ? 'OUI' : 'NON') . '</pre>';
        // echo '<pre>VirtualHosts: '; var_dump($virtualhosts); echo '</pre>'; exit;

        // Charger la vue
        Functions::view('home', [
            'sysInfo' => $sysInfo,
            'projectsData' => $projectsData,
            'projectColors' => $projectColors,
            'virtualhosts' => $virtualhosts,
        ]);
    }
}
