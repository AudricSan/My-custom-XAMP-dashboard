<?php
$pageTitle = 'Home';
require __DIR__ . '/../Public/assets/html/header.php';
?>

<div class='container'>
    <!-- Configuration du serveur -->
    <div class='config card server-config'>
        <h2><?= $icon('settings') ?>Server Configuration</h2>
        <div class="config-columns">
            <ul class='config-list' id="sysinfo-list-1">
                <li><strong>Nom de la machine:</strong> <span id="hostname"><?= $e($sysInfo['hostname']) ?></span></li>
                <li><strong>Système d'exploitation:</strong> <span id="os"><?= $e($sysInfo['os']) ?></span></li>
                <br>
                <li><strong>Espace disque utilisé:</strong> <span id="disk_used_gb"><?= $sysInfo['disk_used_gb'] ?></span> Go / <span id="disk_total_gb"><?= $sysInfo['disk_total_gb'] ?></span> Go (<span id="disk_percent"><?= $sysInfo['disk_percent'] ?></span>%)</li>
                <li><strong>Espace disque libre:</strong> <span id="disk_free_gb"><?= $sysInfo['disk_free_gb'] ?></span> Go</li>
                <li><strong>Mémoire totale:</strong> <span id="mem_total"><?= $sysInfo['mem_total'] ? $e($sysInfo['mem_total']) . ' Go' : 'Indisponible' ?></span></li>
                <li><strong>Mémoire libre:</strong> <span id="mem_free"><?= $sysInfo['mem_free'] ? $e($sysInfo['mem_free']) . ' Go' : 'Indisponible' ?></span></li>
            </ul>

            <ul class='config-list' id="sysinfo-list-2">
                <li><strong>Apache Version:</strong> <span id="apacheVersion"><?= $e($sysInfo['apacheVersion']) ?></span> <a href='http://httpd.apache.org/docs/2.4/en/'>Documentation</a></li>
                <li><strong>PHP Version:</strong> <span id="phpVersion"><?= $e($sysInfo['phpVersion']) ?></span> <a href='http://www.php.net/manual/en/'>Documentation</a></li>
                <li><strong>Server Software:</strong> <span id="serverSoftware"><?= $e($sysInfo['serverSoftware']) ?></span></li>
                <li><strong>MySQL Version:</strong> <span id="mysqlVersion"><?= $sysInfo['mysqlVersion'] ? $e($sysInfo['mysqlVersion']) . ' (port: ' . $e($sysInfo['mysqlPort']) . ') <a href="http://dev.mysql.com/doc/index.html">Documentation</a>' : 'Non détecté' ?></span></li>
                <li><strong>MariaDB Version:</strong> <span id="mariadbVersion"><?= $sysInfo['mariadbVersion'] ? $e($sysInfo['mariadbVersion']) . ' (port: ' . $e($sysInfo['mariadbPort']) . ') <a href="https://mariadb.com/kb/en/documentation/">Documentation</a>' : 'Non détecté' ?></span></li>
            </ul>
        </div>
    </div>

    <!-- Outils -->
    <div class='tools card'>
        <h2><?= $icon('build') ?>Outils</h2>
        <ul class='tools-list'>
            <li><?= $icon('fiber_manual_record') ?> <a href='<?= BASE_URL ?>/?phpinfo=-1'>phpinfo()</a></li>
            <li><?= $icon('fiber_manual_record') ?> <a href='/phpmyadmin/' target='_blank'>phpMyAdmin</a></li>
            <li><?= $icon('fiber_manual_record') ?> <a href='<?= BASE_URL ?>/vhosts'>Créer un Vhost</a></li>
            <li><?= $icon('fiber_manual_record') ?> <a href='<?= BASE_URL ?>/services'>Gérer les services</a></li>
            <li><?= $icon('fiber_manual_record') ?> <a href='<?= BASE_URL ?>/logs'>Voir les logs</a></li>
        </ul>
    </div>

    <!-- Virtual Hosts -->
    <?php if ($virtualhosts): ?>
        <div class='vhosts card'>
            <h2><?= $icon('dns') ?>Virtual Hosts</h2>
            <ul class='vhosts-list'>
                <?php foreach ($virtualhosts as $vh): ?>
                    <li>
                        <?= $icon('fiber_manual_record') ?>
                        <a href='http://<?= $e($vh['servername']) ?>/' target='_blank'><?= $e($vh['servername']) ?></a>
                        <span class="vhost-actions">
                            <a href='<?= BASE_URL ?>/vhosts?edit=<?= $e($vh['ip']) ?>' class="vhost-action-btn" title="Éditer">
                                <?= $icon('edit') ?>
                            </a>
                            <a href='<?= BASE_URL ?>/vhosts?delete=<?= $e($vh['ip']) ?>&redirect=<?= urlencode(BASE_URL . '/') ?>'
                               class="vhost-action-btn vhost-delete"
                               title="Supprimer"
                               onclick="return confirm('Êtes-vous sûr de vouloir supprimer ce VirtualHost ?')">
                                <?= $icon('delete') ?>
                            </a>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Projets -->
    <?php if ($projectsData): ?>
        <div class='projects card'>
            <h2><?= $icon('folder') ?>Mes Projets</h2>
            <ul class='projects-list'>
                <?php foreach ($projectsData as $proj): ?>
                    <?php $color = $projectColors[$proj['name']] ?? '#888'; ?>
                    <li>
                        <?= $icon('fiber_manual_record') ?>
                        <a href='../<?= $e($proj['name']) ?>/' target='_blank'><?= $e($proj['name']) ?></a>
                        <span style="font-size:0.9em;color:<?= $color ?>;">(<?= $e($proj['lastModStr']) ?>)</span>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <!-- Monitoring CPU / RAM -->
    <div class='monitoring card'>
        <h2><?= $icon('monitoring') ?>Monitoring Temps Réel</h2>

        <!-- Jauges circulaires -->
        <div class="monitoring-gauges">
            <div class="gauge-container">
                <canvas id="gauge-cpu"></canvas>
                <p class="gauge-label">CPU</p>
            </div>
            <div class="gauge-container">
                <canvas id="gauge-ram"></canvas>
                <p class="gauge-label">RAM</p>
            </div>
        </div>

        <!-- Graphique temps réel -->
        <div class="monitoring-realtime">
            <h3>Évolution (30 dernières minutes)</h3>
            <canvas id="chart-realtime"></canvas>
        </div>

        <!-- Graphique historique -->
        <div class="monitoring-history">
            <h3>Historique (1 heure)</h3>
            <canvas id="chart-history"></canvas>
        </div>
    </div>
</div>

<?php require __DIR__ . '/../Public/assets/html/footer.php'; ?>