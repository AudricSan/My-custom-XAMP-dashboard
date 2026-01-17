<?php

namespace App\Controllers\Api;

use App\Models\SystemInfo;
use App\Models\MonitoringHistory;

/**
 * API pour le monitoring CPU/RAM
 */
class MonitoringController
{
    /**
     * Retourne les données actuelles + historique récent
     */
    public function current()
    {
        header('Content-Type: application/json');

        $sysInfo = SystemInfo::getAll();

        // Enregistrer le point dans l'historique
        MonitoringHistory::addPoint(
            $sysInfo['cpu_percent'],
            $sysInfo['mem_percent']
        );

        // Récupérer les 60 derniers points (30 minutes)
        $history = MonitoringHistory::getRecent(60);

        echo json_encode([
            'current' => [
                'cpu' => $sysInfo['cpu_percent'],
                'ram' => $sysInfo['mem_percent'],
            ],
            'history' => $history,
            'timestamp' => time(),
        ]);
        exit();
    }

    /**
     * Retourne tout l'historique (120 points = 1 heure)
     */
    public function history()
    {
        header('Content-Type: application/json');
        echo json_encode(MonitoringHistory::getAll());
        exit();
    }
}
