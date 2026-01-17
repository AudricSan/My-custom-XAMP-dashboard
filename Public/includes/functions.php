<?php
// Fonctions utilitaires générales

/**
 * Vérifie si Xdebug est activé
 * @return bool
 */
function isXdebugEnabled() {
    return function_exists('xdebug_info');
}

/**
 * Génère le HTML pour les icônes Material Symbols
 * @param string $icon Nom de l'icône
 * @return string HTML de l'icône
 */
function icon($icon) {
    return '<span class="material-symbols-outlined">' . htmlspecialchars($icon) . '</span>';
}

/**
 * Échapper et afficher du texte de manière sécurisée
 * @param string $text
 * @return string
 */
function e($text) {
    return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
}

/**
 * Inclure un template HTML
 * @param string $template Nom du template
 * @param array $data Données à passer au template
 */
function renderTemplate($template, $data = []) {
    extract($data);
    $templatePath = __DIR__ . '/../templates/' . $template . '.php';

    if (file_exists($templatePath)) {
        include $templatePath;
    }
}
