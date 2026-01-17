<?php

/**
 * Modèle pour gérer les Logs Apache
 */
namespace App\Models;

class ApacheLog
{
    /**
     * Lit les dernières lignes d’un fichier de log Apache
     */
    public static function tail(string $file, int $limit = 100): array
    {
        if (!self::isAllowedLog($file)) {
            return [];
        }

        if (!is_file($file) || !is_readable($file)) {
            return [];
        }

        $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        return array_slice($lines, -$limit);
    }

    /**
     * Sécurité : restreindre aux dossiers Apache/logs
     */
    private static function isAllowedLog(string $file): bool
    {
        $base = realpath(APACHE_LOGS_PATH);
        $real = realpath($file);

        return $real !== false && str_starts_with($real, $base);
    }
}
