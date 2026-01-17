<?php

namespace App\Controllers;

use App\Helpers\Functions;

/**
 * Contrôleur pour la gestion des services (Apache, MySQL, etc.)
 */
class ServiceController
{
    /**
     * Affiche la page de gestion des services
     */
    public function index()
    {
        $message = '';
        $messageType = '';

        // Traiter les actions si présentes
        if (isset($_GET['action']) && isset($_GET['service'])) {
            $action = $_GET['action'];
            $service = $_GET['service'];
            $result = $this->executeServiceAction($service, $action);
            $message = $result['message'];
            $messageType = $result['type'];
        }

        // Récupérer les statuts des services
        $services = $this->getServicesStatus();

        Functions::view('pages/services', [
            'services' => $services,
            'message' => $message,
            'messageType' => $messageType,
        ]);
    }

    /**
     * Récupère les statuts des services
     * @return array
     */
    private function getServicesStatus()
    {
        $xamppPath = dirname(HTDOCS_PATH);

        $services = [
            'apache' => [
                'name' => 'Apache',
                'icon' => 'web',
                'status' => $this->checkApacheStatus(),
                'start_cmd' => $xamppPath . '/apache_start.bat',
                'stop_cmd' => $xamppPath . '/apache_stop.bat',
            ],
            'mysql' => [
                'name' => 'MySQL',
                'icon' => 'storage',
                'status' => $this->checkMySQLStatus(),
                'start_cmd' => $xamppPath . '/mysql_start.bat',
                'stop_cmd' => $xamppPath . '/mysql_stop.bat',
            ],
        ];

        // Vérifier si FileZilla est installé
        if (file_exists($xamppPath . '/filezilla_start.bat')) {
            $services['filezilla'] = [
                'name' => 'FileZilla FTP',
                'icon' => 'cloud_upload',
                'status' => $this->checkFileZillaStatus(),
                'start_cmd' => $xamppPath . '/filezilla_start.bat',
                'stop_cmd' => $xamppPath . '/filezilla_stop.bat',
            ];
        }

        // Vérifier si Mercury est installé
        if (file_exists($xamppPath . '/mercury_start.bat')) {
            $services['mercury'] = [
                'name' => 'Mercury Mail',
                'icon' => 'mail',
                'status' => $this->checkMercuryStatus(),
                'start_cmd' => $xamppPath . '/mercury_start.bat',
                'stop_cmd' => $xamppPath . '/mercury_stop.bat',
            ];
        }

        return $services;
    }

    /**
     * Vérifie si Apache est en cours d'exécution
     * @return bool
     */
    private function checkApacheStatus()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $output = [];
            @exec('tasklist /FI "IMAGENAME eq httpd.exe" 2>NUL', $output);
            foreach ($output as $line) {
                if (stripos($line, 'httpd.exe') !== false) {
                    return true;
                }
            }
        } else {
            $output = [];
            @exec('pgrep -x httpd || pgrep -x apache2', $output);
            return !empty($output);
        }
        return false;
    }

    /**
     * Vérifie si MySQL est en cours d'exécution
     * @return bool
     */
    private function checkMySQLStatus()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $output = [];
            @exec('tasklist /FI "IMAGENAME eq mysqld.exe" 2>NUL', $output);
            foreach ($output as $line) {
                if (stripos($line, 'mysqld.exe') !== false) {
                    return true;
                }
            }
        } else {
            $output = [];
            @exec('pgrep -x mysqld', $output);
            return !empty($output);
        }
        return false;
    }

    /**
     * Vérifie si FileZilla est en cours d'exécution
     * @return bool
     */
    private function checkFileZillaStatus()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $output = [];
            @exec('tasklist /FI "IMAGENAME eq FileZillaServer.exe" 2>NUL', $output);
            foreach ($output as $line) {
                if (stripos($line, 'FileZillaServer.exe') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Vérifie si Mercury est en cours d'exécution
     * @return bool
     */
    private function checkMercuryStatus()
    {
        if (stristr(PHP_OS, 'WIN')) {
            $output = [];
            @exec('tasklist /FI "IMAGENAME eq mercury.exe" 2>NUL', $output);
            foreach ($output as $line) {
                if (stripos($line, 'mercury.exe') !== false) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Exécute une action sur un service
     * @param string $service Nom du service
     * @param string $action Action à effectuer (start, stop, restart)
     * @return array Message de résultat
     */
    private function executeServiceAction($service, $action)
    {
        $services = $this->getServicesStatus();

        if (!isset($services[$service])) {
            return [
                'message' => 'Service inconnu.',
                'type' => 'error'
            ];
        }

        $serviceData = $services[$service];
        $serviceName = $serviceData['name'];

        // Vérifier que les commandes existent
        if ($action === 'start' && !file_exists($serviceData['start_cmd'])) {
            return [
                'message' => "Le script de démarrage de $serviceName est introuvable.",
                'type' => 'error'
            ];
        }

        if ($action === 'stop' && !file_exists($serviceData['stop_cmd'])) {
            return [
                'message' => "Le script d'arrêt de $serviceName est introuvable.",
                'type' => 'error'
            ];
        }

        // Exécuter l'action
        switch ($action) {
            case 'start':
                $this->executeCommand($serviceData['start_cmd']);
                sleep(2); // Attendre que le service démarre
                return [
                    'message' => "$serviceName a été démarré.",
                    'type' => 'success'
                ];

            case 'stop':
                $this->executeCommand($serviceData['stop_cmd']);
                sleep(2); // Attendre que le service s'arrête
                return [
                    'message' => "$serviceName a été arrêté.",
                    'type' => 'success'
                ];

            case 'restart':
                $this->executeCommand($serviceData['stop_cmd']);
                sleep(2);
                $this->executeCommand($serviceData['start_cmd']);
                sleep(2);
                return [
                    'message' => "$serviceName a été redémarré.",
                    'type' => 'success'
                ];

            default:
                return [
                    'message' => 'Action invalide.',
                    'type' => 'error'
                ];
        }
    }

    /**
     * Exécute une commande système
     * @param string $command Commande à exécuter
     */
    private function executeCommand($command)
    {
        if (stristr(PHP_OS, 'WIN')) {
            // Windows: Exécuter en arrière-plan
            pclose(popen('start /B "" "' . $command . '"', 'r'));
        } else {
            // Linux: Exécuter en arrière-plan
            exec($command . ' > /dev/null 2>&1 &');
        }
    }
}
