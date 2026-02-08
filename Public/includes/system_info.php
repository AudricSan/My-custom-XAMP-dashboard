<?php
// Fonctions pour récupérer les informations système

/**
 * Récupère les informations système
 * @return array Tableau associatif contenant toutes les infos système
 */
function getSystemInfo() {
    $phpVersion = phpversion();
    $hostname = gethostname();
    $os = php_uname();

    // Informations disque
    $disk_total = disk_total_space(HTDOCS_PATH);
    $disk_free = disk_free_space(HTDOCS_PATH);
    $disk_used = $disk_total - $disk_free;
    $disk_percent = $disk_total > 0 ? round($disk_used / $disk_total * 100, 1) : 0;
    $disk_total_gb = round($disk_total / 1024 / 1024 / 1024, 2);
    $disk_free_gb = round($disk_free / 1024 / 1024 / 1024, 2);
    $disk_used_gb = round($disk_used / 1024 / 1024 / 1024, 2);

    // Informations mémoire
    $mem_info = getMemoryInfo();

    // Informations serveur
    $apacheVersion = getApacheVersion();
    $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';

    // Informations base de données
    $db_info = getDatabaseInfo();

    return [
        'hostname' => $hostname,
        'os' => $os,
        'phpVersion' => $phpVersion,
        'disk_total_gb' => $disk_total_gb,
        'disk_free_gb' => $disk_free_gb,
        'disk_used_gb' => $disk_used_gb,
        'disk_percent' => $disk_percent,
        'mem_total' => $mem_info['total'],
        'mem_free' => $mem_info['free'],
        'apacheVersion' => $apacheVersion,
        'serverSoftware' => $serverSoftware,
        'mysqlVersion' => $db_info['mysql_version'],
        'mysqlPort' => $db_info['mysql_port'],
        'mariadbVersion' => $db_info['mariadb_version'],
        'mariadbPort' => $db_info['mariadb_port'],
    ];
}

/**
 * Récupère les informations mémoire selon l'OS
 * @return array [total, free]
 */
function getMemoryInfo() {
    $mem_total = '';
    $mem_free = '';

    if (stristr(PHP_OS, 'WIN')) {
        // Windows: RAM via PowerShell
        $ps_mem = [];
        @exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object TotalVisibleMemorySize,FreePhysicalMemory | ConvertTo-Json"', $ps_mem);
        $ps_mem_json = @json_decode(implode("", $ps_mem), true);

        if (is_array($ps_mem_json) && isset($ps_mem_json['TotalVisibleMemorySize']) && isset($ps_mem_json['FreePhysicalMemory'])) {
            $mem_total = round($ps_mem_json['TotalVisibleMemorySize'] / 1024 / 1024, 2) . ' Go';
            $mem_free = round($ps_mem_json['FreePhysicalMemory'] / 1024 / 1024, 2) . ' Go';
        }
    } else {
        // Linux/Mac
        if (is_readable('/proc/meminfo')) {
            $meminfo = file_get_contents('/proc/meminfo');
            if (preg_match('/MemTotal:\s+(\d+)/', $meminfo, $m)) {
                $mem_total = round($m[1] / 1024 / 1024, 2) . ' Go';
            }
            if (preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $m)) {
                $mem_free = round($m[1] / 1024 / 1024, 2) . ' Go';
            }
        }
    }

    return ['total' => $mem_total, 'free' => $mem_free];
}

/**
 * Récupère la version Apache
 * @return string
 */
function getApacheVersion() {
    if (!isset($_SERVER['SERVER_SOFTWARE'])) {
        return 'N/A';
    }

    if ($matches = preg_split('/(?<=\))\s.*$/', $_SERVER['SERVER_SOFTWARE'])) {
        return $matches[0];
    }

    return $_SERVER['SERVER_SOFTWARE'];
}

/**
 * Récupère les informations de base de données (MySQL/MariaDB)
 * @return array
 */
function getDatabaseInfo() {
    $mysqlVersion = '';
    $mariadbVersion = '';
    $mysqlPort = '';
    $mariadbPort = '';

    if (function_exists('mysqli_connect')) {
        try {
            $mysqli = mysqli_init();
            if ($mysqli) {
                mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 2);
                mysqli_report(MYSQLI_REPORT_OFF);
                $connected = @mysqli_real_connect($mysqli, DB_HOST, DB_USER, DB_PASS, '', (int)DB_PORT);
            } else {
                $connected = false;
            }
        } catch (\Exception $e) {
            $connected = false;
        }

        if ($connected) {
            $server_info = mysqli_get_server_info($mysqli);
            $host_info = mysqli_get_host_info($mysqli);
            $dbPort = preg_match('/:(\d+)/', $host_info, $matches) ? $matches[1] : DB_PORT;

            if (stripos($server_info, 'mariadb') !== false) {
                $mariadbVersion = $server_info;
                $mariadbPort = $dbPort;
            }

            if (stripos($server_info, 'mysql') !== false || stripos($server_info, 'mariadb') === false) {
                $mysqlVersion = $server_info;
                $mysqlPort = $dbPort;
            }

            mysqli_close($mysqli);
        }
    }

    return [
        'mysql_version' => $mysqlVersion,
        'mysql_port' => $mysqlPort,
        'mariadb_version' => $mariadbVersion,
        'mariadb_port' => $mariadbPort,
    ];
}
