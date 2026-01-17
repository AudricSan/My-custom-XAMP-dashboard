<?php
// API pour récupérer les informations système en temps réel

// Inclure les fichiers de configuration et fonctions
require_once __DIR__ . '/../config/settings.php';
require_once __DIR__ . '/../includes/system_info.php';

// En-tête JSON
header('Content-Type: application/json');

// Récupérer et retourner les informations système
echo json_encode(getSystemInfo());
