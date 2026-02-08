<?php

namespace App\Models;

/**
 * Modèle pour gérer les informations système
 */
class SystemInfo
{
    /**
     * Récupère toutes les informations système
     * @return array Tableau associatif des informations système
     */
    public static function getAll()
    {
        $phpVersion = phpversion();
        $hostname = gethostname();
        $os = php_uname();

        // Informations disque
        $htdocsPath = defined('HTDOCS_PATH') ? HTDOCS_PATH : dirname(__DIR__, 3);
        $disk_total = disk_total_space($htdocsPath);
        $disk_free = disk_free_space($htdocsPath);
        $disk_used = $disk_total - $disk_free;
        $disk_percent = $disk_total > 0 ? round($disk_used / $disk_total * 100, 1) : 0;
        $disk_total_gb = round($disk_total / 1024 / 1024 / 1024, 2);
        $disk_free_gb = round($disk_free / 1024 / 1024 / 1024, 2);
        $disk_used_gb = round($disk_used / 1024 / 1024 / 1024, 2);

        // Informations mémoire
        $mem_info = self::getMemoryInfo();

        // Informations CPU
        $cpu_percent = self::getCpuUsage();

        // Informations serveur
        $apacheVersion = self::getApacheVersion();
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? 'N/A';

        // Informations base de données
        $db_info = self::getDatabaseInfo();

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
            'mem_percent' => $mem_info['percent'],
            'cpu_percent' => $cpu_percent,
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
     * @return array [total, free, percent]
     */
    private static function getMemoryInfo()
    {
        $mem_total = '';
        $mem_free = '';
        $mem_percent = null;

        if (stristr(PHP_OS, 'WIN')) {
            // Windows: RAM via PowerShell
            $ps_mem = [];
            @exec('powershell -Command "Get-CimInstance Win32_OperatingSystem | Select-Object TotalVisibleMemorySize,FreePhysicalMemory | ConvertTo-Json"', $ps_mem);
            $ps_mem_json = @json_decode(implode("", $ps_mem), true);

            if (is_array($ps_mem_json) && isset($ps_mem_json['TotalVisibleMemorySize']) && isset($ps_mem_json['FreePhysicalMemory'])) {
                $total_kb = $ps_mem_json['TotalVisibleMemorySize'];
                $free_kb = $ps_mem_json['FreePhysicalMemory'];
                $used_kb = $total_kb - $free_kb;

                $mem_total = round($total_kb / 1024 / 1024, 2) . ' Go';
                $mem_free = round($free_kb / 1024 / 1024, 2) . ' Go';
                $mem_percent = round(($used_kb / $total_kb) * 100, 1);
            }
        } else {
            // Linux/Mac
            if (is_readable('/proc/meminfo')) {
                $meminfo = file_get_contents('/proc/meminfo');
                $total = $available = 0;
                if (preg_match('/MemTotal:\s+(\d+)/', $meminfo, $m)) {
                    $total = $m[1];
                    $mem_total = round($total / 1024 / 1024, 2) . ' Go';
                }
                if (preg_match('/MemAvailable:\s+(\d+)/', $meminfo, $m)) {
                    $available = $m[1];
                    $mem_free = round($available / 1024 / 1024, 2) . ' Go';
                }
                if ($total > 0) {
                    $mem_percent = round((($total - $available) / $total) * 100, 1);
                }
            }
        }

        return ['total' => $mem_total, 'free' => $mem_free, 'percent' => $mem_percent];
    }

    /**
     * Récupère l'utilisation CPU en pourcentage
     * @return float|null Pourcentage d'utilisation CPU ou null si indisponible
     */
    private static function getCpuUsage()
    {
        $cpu = null;

        if (stristr(PHP_OS, 'WIN')) {
            // Windows: PowerShell
            $output = [];
            @exec('powershell -Command "Get-CimInstance Win32_Processor | Measure-Object -Property LoadPercentage -Average | Select-Object -ExpandProperty Average"', $output);
            if (!empty($output[0]) && is_numeric($output[0])) {
                $cpu = round((float)$output[0], 1);
            }
        } else {
            // Linux: /proc/stat avec calcul différentiel
            $current = self::readProcStat();
            if ($current && isset($_SESSION['last_cpu_stat'])) {
                $prev = $_SESSION['last_cpu_stat'];
                $cpu = self::calculateCpuPercent($prev, $current);
            }
            $_SESSION['last_cpu_stat'] = $current;
        }

        return $cpu;
    }

    /**
     * Lit les statistiques CPU depuis /proc/stat (Linux)
     * @return array|null
     */
    private static function readProcStat()
    {
        if (!is_readable('/proc/stat')) {
            return null;
        }

        $stat = file_get_contents('/proc/stat');
        if (preg_match('/^cpu\s+(\d+)\s+(\d+)\s+(\d+)\s+(\d+)/m', $stat, $m)) {
            return [
                'user' => (int)$m[1],
                'nice' => (int)$m[2],
                'system' => (int)$m[3],
                'idle' => (int)$m[4],
            ];
        }

        return null;
    }

    /**
     * Calcule le pourcentage d'utilisation CPU entre deux lectures
     * @param array $prev Lecture précédente
     * @param array $current Lecture actuelle
     * @return float|null
     */
    private static function calculateCpuPercent($prev, $current)
    {
        $prev_idle = $prev['idle'];
        $prev_total = array_sum($prev);
        $curr_idle = $current['idle'];
        $curr_total = array_sum($current);

        $total_diff = $curr_total - $prev_total;
        $idle_diff = $curr_idle - $prev_idle;

        if ($total_diff <= 0) {
            return null;
        }

        $usage = (($total_diff - $idle_diff) / $total_diff) * 100;
        return round($usage, 1);
    }

    /**
     * Récupère la version Apache
     * @return string
     */
    private static function getApacheVersion()
    {
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
    private static function getDatabaseInfo()
    {
        $mysqlVersion = '';
        $mariadbVersion = '';
        $mysqlPort = '';
        $mariadbPort = '';

        if (function_exists('mysqli_connect')) {
            $dbHost = defined('DB_HOST') ? DB_HOST : 'localhost';
            $dbUser = defined('DB_USER') ? DB_USER : 'root';
            $dbPass = defined('DB_PASS') ? DB_PASS : '';
            $dbPort = defined('DB_PORT') ? DB_PORT : '3306';

            try {
                $mysqli = mysqli_init();
                if ($mysqli) {
                    mysqli_options($mysqli, MYSQLI_OPT_CONNECT_TIMEOUT, 2);
                    mysqli_report(MYSQLI_REPORT_OFF);
                    $connected = @mysqli_real_connect($mysqli, $dbHost, $dbUser, $dbPass, '', (int)$dbPort);
                } else {
                    $connected = false;
                }
            } catch (\Exception $e) {
                $connected = false;
            }

            if ($connected) {
                $server_info = mysqli_get_server_info($mysqli);
                $host_info = mysqli_get_host_info($mysqli);
                $port = preg_match('/:(\d+)/', $host_info, $matches) ? $matches[1] : $dbPort;

                if (stripos($server_info, 'mariadb') !== false) {
                    $mariadbVersion = $server_info;
                    $mariadbPort = $port;
                }

                if (stripos($server_info, 'mysql') !== false || stripos($server_info, 'mariadb') === false) {
                    $mysqlVersion = $server_info;
                    $mysqlPort = $port;
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
}
