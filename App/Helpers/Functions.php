<?php

namespace App\Helpers;

/**
 * Fonctions utilitaires générales
 */
class Functions
{
    /**
     * Vérifie si Xdebug est activé
     * @return bool
     */
    public static function isXdebugEnabled()
    {
        return function_exists('xdebug_info');
    }

    /**
     * Génère le HTML pour les icônes Material Symbols
     * @param string $icon Nom de l'icône
     * @return string HTML de l'icône
     */
    public static function icon($icon)
    {
        return '<span class="material-symbols-outlined">' . htmlspecialchars($icon) . '</span>';
    }

    /**
     * Échapper et afficher du texte de manière sécurisée
     * @param string $text
     * @return string
     */
    public static function escape($text)
    {
        return htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    }

    /**
     * Redirige vers une URL
     * @param string $url URL de destination
     */
    public static function redirect($url)
    {
        header("Location: $url");
        exit();
    }

    /**
     * Charge une vue
     * @param string $view Nom de la vue (ex: 'home', 'pages/vhost_form')
     * @param array $data Données à passer à la vue
     */
    public static function view($view, $data = [])
    {
        extract($data);
        $viewPath = dirname(__DIR__, 2) . '/Views/' . $view . '.php';

        if (file_exists($viewPath)) {
            require $viewPath;
        } else {
            die("Vue non trouvée : $viewPath");
        }
    }
}
