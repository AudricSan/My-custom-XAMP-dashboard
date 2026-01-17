<?php

namespace App\Models;

/**
 * Gestion de l'historique de monitoring CPU/RAM
 */
class MonitoringHistory
{
    private const HISTORY_FILE = __DIR__ . '/../../storage/monitoring/history.json';
    private const MAX_POINTS = 120; // 1 heure à 30s d'intervalle

    /**
     * Enregistre un nouveau point de données
     * @param float|null $cpu Pourcentage CPU
     * @param float|null $ram Pourcentage RAM
     * @return array L'historique complet après ajout
     */
    public static function addPoint($cpu, $ram)
    {
        $history = self::load();
        $timestamp = time();

        // Ajouter les nouveaux points
        if ($cpu !== null) {
            $history['cpu'][] = ['timestamp' => $timestamp, 'value' => $cpu];
        }
        if ($ram !== null) {
            $history['ram'][] = ['timestamp' => $timestamp, 'value' => $ram];
        }

        // Limiter la taille (garder les MAX_POINTS plus récents)
        $history['cpu'] = array_slice($history['cpu'], -self::MAX_POINTS);
        $history['ram'] = array_slice($history['ram'], -self::MAX_POINTS);

        self::save($history);
        return $history;
    }

    /**
     * Récupère tout l'historique
     * @return array
     */
    public static function getAll()
    {
        return self::load();
    }

    /**
     * Récupère les N derniers points
     * @param int $limit Nombre de points à récupérer
     * @return array
     */
    public static function getRecent($limit = 60)
    {
        $history = self::load();
        return [
            'cpu' => array_slice($history['cpu'], -$limit),
            'ram' => array_slice($history['ram'], -$limit),
        ];
    }

    /**
     * Charge l'historique depuis le fichier
     * @return array
     */
    private static function load()
    {
        self::ensureFileExists();

        $json = @file_get_contents(self::HISTORY_FILE);
        $data = @json_decode($json, true);

        if (!is_array($data) || !isset($data['cpu']) || !isset($data['ram'])) {
            return ['cpu' => [], 'ram' => []];
        }

        return $data;
    }

    /**
     * Sauvegarde l'historique dans le fichier
     * @param array $data Données à sauvegarder
     */
    private static function save($data)
    {
        self::ensureFileExists();
        @file_put_contents(self::HISTORY_FILE, json_encode($data, JSON_PRETTY_PRINT));
    }

    /**
     * Crée le fichier et le répertoire s'ils n'existent pas
     */
    private static function ensureFileExists()
    {
        $dir = dirname(self::HISTORY_FILE);
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!file_exists(self::HISTORY_FILE)) {
            @file_put_contents(self::HISTORY_FILE, '{"cpu":[],"ram":[]}');
        }
    }

    /**
     * Nettoie l'historique (pour tests)
     */
    public static function clear()
    {
        self::save(['cpu' => [], 'ram' => []]);
    }
}
