<?php

namespace App\Controllers;

use App\Helpers\Functions;

/**
 * Contrôleur pour la visualisation des logs Apache
 */
class ApacheLogController
{
    /**
     * Affiche la page des logs Apache
     */
    public function index()
    {
        // Chemins des logs Apache (depuis Settings.php)
        $apacheLogsPath = rtrim(APACHE_LOGS_PATH, '/') . '/';

        $errorLogFile = $apacheLogsPath . 'error.log';
        $accessLogFile = $apacheLogsPath . 'access.log';

        // Paramètres de pagination
        $logType = $_GET['type'] ?? 'error'; // 'error' ou 'access'
        $lines = isset($_GET['lines']) ? (int)$_GET['lines'] : 10;
        $lines = max(10, min($lines, 1000)); // Entre 10 et 1000 lignes

        // Déterminer quel fichier lire
        $logFile = $logType === 'access' ? $accessLogFile : $errorLogFile;

        // Lire les logs
        $logContent = $this->readLogFile($logFile, $lines);
        $fileExists = file_exists($logFile);
        $fileSize = $fileExists ? $this->formatFileSize(filesize($logFile)) : 'N/A';

        // Passer les données à la vue
        $data = [
            'logType' => $logType,
            'logContent' => $logContent,
            'lines' => $lines,
            'fileExists' => $fileExists,
            'fileSize' => $fileSize,
            'errorLogPath' => $errorLogFile,
            'accessLogPath' => $accessLogFile,
        ];

        Functions::view('pages/logs', $data);
    }

    /**
     * Lit les dernières lignes d'un fichier de log
     * @param string $filePath Chemin du fichier
     * @param int $lines Nombre de lignes à lire
     * @return array Tableau des lignes
     */
    private function readLogFile($filePath, $lines = 100)
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            return [];
        }

        // Lire les dernières lignes du fichier
        $file = @file($filePath);
        if ($file === false) {
            return [];
        }

        // Prendre les dernières N lignes
        $logLines = array_slice($file, -$lines);

        // Inverser pour avoir les plus récentes en premier
        return array_reverse($logLines);
    }

    /**
     * Formate la taille du fichier en unités lisibles
     * @param int $bytes Taille en octets
     * @return string Taille formatée
     */
    private function formatFileSize($bytes)
    {
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' Go';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' Mo';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' Ko';
        } else {
            return $bytes . ' octets';
        }
    }
}
