<?php

namespace App\Controllers\Api;

use App\Models\SystemInfo;

/**
 * Contrôleur API pour le statut du serveur
 */
class ServerStatusController
{
    /**
     * Retourne les informations système en JSON
     */
    public function index()
    {
        header('Content-Type: application/json');
        echo json_encode(SystemInfo::getAll());
        exit();
    }
}
