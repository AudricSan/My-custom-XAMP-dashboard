<?php
// Gestion des VirtualHosts

/**
 * Récupère la liste des VirtualHosts configurés
 * @return array Liste des vhosts avec leurs informations
 */
function getVirtualHosts() {
    $virtualhosts = [];
    $vhosts_conf = VHOSTS_CONF;

    if (!file_exists($vhosts_conf)) {
        return $virtualhosts;
    }

    $lines = file($vhosts_conf);
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
 * @return array Liste des vhosts avec IP, ServerName, DocumentRoot
 */
function getVhostsList() {
    $vhosts = [];
    $vhostsConf = VHOSTS_CONF;

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
function getNextVhostIp() {
    $used = [];
    $vhostsConf = VHOSTS_CONF;

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
