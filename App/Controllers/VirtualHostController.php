<?php

namespace App\Controllers;

use App\Models\VirtualHost;
use App\Helpers\Functions;

/**
 * Contrôleur pour la gestion des VirtualHosts
 */
class VirtualHostController
{
    /**
     * Affiche le formulaire de gestion des vhosts
     */
    public function index()
    {
        $defaultIp = VirtualHost::getNextAvailableIp();
        $message = '';
        $editVhost = null;

        // Traitement de la suppression
        if (isset($_GET['delete']) && preg_match('/^127\.0\.0\.([0-9]{1,3})$/', $_GET['delete'])) {
            $ipToDelete = $_GET['delete'];
            $redirectTo = $_GET['redirect'] ?? '/vhosts';
            if (VirtualHost::delete($ipToDelete)) {
                Functions::redirect($redirectTo);
            } else {
                $message = "<div style='color:red'>Impossible de supprimer : fichier non accessible en écriture.</div>";
            }
        }

        // Préparation pour édition
        if (isset($_GET['edit']) && preg_match('/^127\.0\.0\.([0-9]{1,3})$/', $_GET['edit'])) {
            $ipToEdit = $_GET['edit'];
            $editVhost = VirtualHost::findByIp($ipToEdit);
        }

        // Message de succès
        if (isset($_GET['success'])) {
            $message = "<div style='color:green'>Vhost ajouté ou modifié avec succès !</div>";
        }

        $existingVhosts = VirtualHost::getAllDetailed();

        // Charger la vue
        Functions::view('pages/vhost_form', [
            'defaultIp' => $defaultIp,
            'message' => $message,
            'editVhost' => $editVhost,
            'existingVhosts' => $existingVhosts,
        ]);
    }

    /**
     * Traite le formulaire de création/édition
     */
    public function store()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            Functions::redirect('/vhosts');
        }

        $servername = trim($_POST['servername'] ?? '');
        $documentroot = trim($_POST['documentroot'] ?? '');
        $ip = trim($_POST['ip'] ?? '');
        $editMode = isset($_POST['edit_mode']);

        if (empty($servername) || empty($documentroot) || empty($ip)) {
            Functions::redirect('/vhosts?error=missing_fields');
        }

        if (!preg_match('/^127\.0\.0\.([0-9]{1,3})$/', $ip)) {
            Functions::redirect('/vhosts?error=invalid_ip');
        }

        if (VirtualHost::save($servername, $documentroot, $ip, $editMode)) {
            Functions::redirect('/vhosts?success=1');
        } else {
            Functions::redirect('/vhosts?error=write_failed');
        }
    }
}
