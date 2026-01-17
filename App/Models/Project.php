<?php

namespace App\Models;

/**
 * Modèle pour gérer les projets
 */
class Project
{
    /**
     * Récupère la liste des projets dans htdocs
     * @return array Liste des noms de projets
     */
    public static function getAll()
    {
        $projects = [];
        $dir = defined('HTDOCS_PATH') ? HTDOCS_PATH : dirname(__DIR__, 3);
        $excludedDirs = defined('EXCLUDED_DIRS') ? EXCLUDED_DIRS : [];

        if ($handle = opendir($dir)) {
            while (false !== ($entry = readdir($handle))) {
                if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)
                    && !in_array($entry, $excludedDirs)
                    && $entry[0] !== '.') {
                    $projects[] = $entry;
                }
            }
            closedir($handle);
        }

        return $projects;
    }

    /**
     * Récupère les projets avec leurs métadonnées (date de modification)
     * @return array Projets triés alphabétiquement avec métadonnées
     */
    public static function getAllWithMetadata()
    {
        $projects = self::getAll();
        $projectsData = [];
        $htdocsPath = defined('HTDOCS_PATH') ? HTDOCS_PATH : dirname(__DIR__, 3);

        foreach ($projects as $proj) {
            $projPath = $htdocsPath . DIRECTORY_SEPARATOR . $proj;
            $lastMod = @filemtime($projPath);

            $projectsData[] = [
                'name' => $proj,
                'lastMod' => $lastMod,
                'lastModStr' => $lastMod ? date('d/m/Y', $lastMod) : 'N/A',
            ];
        }

        // Tri alphabétique
        usort($projectsData, function ($a, $b) {
            return strcasecmp($a['name'], $b['name']);
        });

        return $projectsData;
    }

    /**
     * Identifie les 3 projets les plus récemment modifiés avec code couleur
     * @param array $projects Liste des projets avec métadonnées
     * @return array Couleurs pour chaque projet (top 3)
     */
    public static function getProjectColors($projects)
    {
        // Tri par date de modification
        $sortedByDate = $projects;
        usort($sortedByDate, function ($a, $b) {
            return $b['lastMod'] <=> $a['lastMod'];
        });

        $dateColors = [];
        $colors = ['#e53935', '#fbc02d', '#43a047']; // rouge, jaune, vert
        $count = 0;

        foreach ($sortedByDate as $proj) {
            if ($count < 3 && $proj['lastMod'] > 0) {
                $dateColors[$proj['name']] = $colors[$count];
                $count++;
            }
        }

        return $dateColors;
    }
}
