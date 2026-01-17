<?php
// Gestion des projets

/**
 * Récupère la liste des projets dans htdocs
 * @return array Liste des projets avec leurs informations
 */
function getProjects() {
    $projects = [];
    $dir = HTDOCS_PATH;

    if ($handle = opendir($dir)) {
        while (false !== ($entry = readdir($handle))) {
            if (is_dir($dir . DIRECTORY_SEPARATOR . $entry)
                && !in_array($entry, EXCLUDED_DIRS)
                && $entry[0] !== '.') {
                $projects[] = $entry;
            }
        }
        closedir($handle);
    }

    return $projects;
}

/**
 * Récupère les projets avec leurs dates de modification
 * @return array Projets triés alphabétiquement avec métadonnées
 */
function getProjectsWithMetadata() {
    $projects = getProjects();
    $projectsData = [];

    foreach ($projects as $proj) {
        $projPath = HTDOCS_PATH . DIRECTORY_SEPARATOR . $proj;
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
 * Identifie les 3 projets les plus récemment modifiés
 * @param array $projects Liste des projets
 * @return array Couleurs pour chaque projet (top 3)
 */
function getProjectColors($projects) {
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
