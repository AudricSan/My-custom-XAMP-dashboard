<?php

namespace App\Models;

/**
 * Modèle pour gérer les VirtualHosts Apache
 */
class VirtualHost
{
    /**
     * Récupère la liste des VirtualHosts configurés (simple)
     * @return array Liste des vhosts avec url et nom
     */
    public static function getAll()
    {
        $virtualhosts = [];
        $vhostsConf = defined('VHOSTS_CONF') ? VHOSTS_CONF : '';

        if (!file_exists($vhostsConf)) {
            return $virtualhosts;
        }

        $lines = file($vhostsConf);
        $current = [];
        $in_vhost = false;
        $vhost_addr = '';

        foreach ($lines as $line) {
            $trim = trim($line);

            if (preg_match('/^<VirtualHost ([^>]+)>/i', $trim, $matches)) {
                $in_vhost = true;
                $vhost_addr = $matches[1];
                $current = ['url' => '', 'name' => ''];
            } elseif ($in_vhost && preg_match('/^ServerName\s+(.+)/i', $trim, $matches)) {
                $current['name'] = $matches[1];
            } elseif ($in_vhost && preg_match('/^<\/VirtualHost>/i', $trim)) {
                $host = preg_replace('/:.*/', '', $vhost_addr);
                $port = preg_match('/:(\d+)/', $vhost_addr, $pm) ? $pm[1] : '80';
                $current['url'] = 'http://' . $host . ($port !== '80' ? (":" . $port) : '');

                if (!empty($current['name'])) {
                    $virtualhosts[] = $current;
                }

                $in_vhost = false;
                $vhost_addr = '';
                $current = [];
            }
        }

        return $virtualhosts;
    }

    /**
     * Récupère la liste détaillée des VirtualHosts (pour gestion)
     * @return array Liste des vhosts avec IP, ServerName, DocumentRoot, block
     */
    public static function getAllDetailed()
    {
        $vhosts = [];
        $vhostsConf = defined('VHOSTS_CONF') ? VHOSTS_CONF : '';

        if (!file_exists($vhostsConf)) {
            return $vhosts;
        }

        $lines = file($vhostsConf);
        $current = [];
        $in_vhost = false;
        $block = '';

        foreach ($lines as $line) {
            if (preg_match('/^<VirtualHost (127\.0\.0\.([0-9]{1,3})):80>/', trim($line), $m)) {
                $in_vhost = true;
                $current = [
                    'ip' => $m[1],
                    'block' => '',
                    'servername' => '',
                    'documentroot' => ''
                ];
                $block = $line;
            } elseif ($in_vhost) {
                $block .= $line;

                if (preg_match('/ServerName (.+)/', $line, $sm)) {
                    $current['servername'] = trim($sm[1]);
                }

                if (preg_match('/DocumentRoot "?([^"]+)"?/', $line, $dm)) {
                    $current['documentroot'] = trim($dm[1]);
                }

                if (preg_match('/^<\/VirtualHost>/', trim($line))) {
                    $current['block'] = $block;
                    $vhosts[] = $current;
                    $in_vhost = false;
                    $block = '';
                }
            }
        }

        return $vhosts;
    }

    /**
     * Trouve la prochaine IP disponible dans la plage 127.0.0.X
     * @return string Prochaine IP disponible
     */
    public static function getNextAvailableIp()
    {
        $used = [];
        $vhostsConf = defined('VHOSTS_CONF') ? VHOSTS_CONF : '';

        if (file_exists($vhostsConf)) {
            $lines = file($vhostsConf);
            foreach ($lines as $line) {
                if (preg_match('/<VirtualHost (127\.0\.0\.([0-9]{1,3})):80>/', $line, $m)) {
                    $used[(int)$m[2]] = true;
                }
            }
        }

        for ($i = 2; $i <= 254; $i++) {
            if (!isset($used[$i])) {
                return "127.0.0.$i";
            }
        }

        return "127.0.0.1"; // fallback
    }

    /**
     * Crée ou met à jour un VirtualHost
     * @param string $servername Nom du serveur
     * @param string $documentroot Racine du document
     * @param string $ip Adresse IP
     * @param bool $editMode Mode édition (true) ou création (false)
     * @return bool Succès de l'opération
     */
    public static function save($servername, $documentroot, $ip, $editMode = false)
    {
        $vhostsConf = defined('VHOSTS_CONF') ? VHOSTS_CONF : '';

        if (!is_writable($vhostsConf)) {
            return false;
        }

        // Validation de l'IP
        if (!preg_match('/^127\.0\.0\.([0-9]{1,3})$/', $ip)) {
            return false;
        }

        $vhostBlock = "\n<VirtualHost $ip:80>\n    ServerName $servername\n    DocumentRoot \"$documentroot\"\n    <Directory \"$documentroot\">\n        AllowOverride All\n        Require all granted\n    </Directory>\n    ErrorLog \"logs/{$servername}_error.log\"\n    CustomLog \"logs/{$servername}_access.log\" common\n</VirtualHost>\n";

        if ($editMode) {
            // Mode édition : remplacer le bloc existant
            $vhosts = self::getAllDetailed();
            $newContent = '';
            foreach ($vhosts as $vh) {
                if ($vh['ip'] === $ip) {
                    $newContent .= $vhostBlock;
                } else {
                    $newContent .= $vh['block'];
                }
            }
            return file_put_contents($vhostsConf, $newContent) !== false;
        } else {
            // Ajout classique
            return file_put_contents($vhostsConf, $vhostBlock, FILE_APPEND | LOCK_EX) !== false;
        }
    }

    /**
     * Supprime un VirtualHost par son IP
     * @param string $ip Adresse IP du vhost à supprimer
     * @return bool Succès de l'opération
     */
    public static function delete($ip)
    {
        $vhostsConf = defined('VHOSTS_CONF') ? VHOSTS_CONF : '';

        if (!is_writable($vhostsConf)) {
            return false;
        }

        $vhosts = self::getAllDetailed();
        $newContent = '';
        foreach ($vhosts as $vh) {
            if ($vh['ip'] !== $ip) {
                $newContent .= $vh['block'];
            }
        }

        return file_put_contents($vhostsConf, $newContent) !== false;
    }

    /**
     * Récupère un VirtualHost par son IP
     * @param string $ip Adresse IP
     * @return array|null VirtualHost ou null si non trouvé
     */
    public static function findByIp($ip)
    {
        $vhosts = self::getAllDetailed();
        foreach ($vhosts as $vh) {
            if ($vh['ip'] === $ip) {
                return $vh;
            }
        }
        return null;
    }
}
