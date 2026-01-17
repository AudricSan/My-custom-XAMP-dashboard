<?php
$pageTitle = 'Logs Apache';
require __DIR__ . '/../../Public/assets/html/header.php';
?>

<div class="logs-container">
    <div class="logs-header">
        <h1><?= $icon('description') ?>Logs Apache</h1>

        <div class="logs-controls">
            <!-- Sélection du type de log -->
            <div class="log-type-switcher">
                <a href="<?= BASE_URL ?>/logs?type=error&lines=<?= $lines ?>"
                   class="btn-log <?= $logType === 'error' ? 'active' : '' ?>">
                    <?= $icon('error') ?> Erreurs
                </a>
                <a href="<?= BASE_URL ?>/logs?type=access&lines=<?= $lines ?>"
                   class="btn-log <?= $logType === 'access' ? 'active' : '' ?>">
                    <?= $icon('visibility') ?> Accès
                </a>
            </div>

            <!-- Sélection du nombre de lignes -->
            <div class="lines-selector">
                <label for="lines-count"><strong>Lignes :</strong></label>
                <select id="lines-count" onchange="window.location.href='<?= BASE_URL ?>/logs?type=<?= $logType ?>&lines=' + this.value">
                    <option value="10" <?= $lines === 10 ? 'selected' : '' ?>>10</option>
                    <option value="25" <?= $lines === 25 ? 'selected' : '' ?>>25</option>
                    <option value="50" <?= $lines === 50 ? 'selected' : '' ?>>50</option>
                    <option value="100" <?= $lines === 100 ? 'selected' : '' ?>>100</option>
                    <option value="200" <?= $lines === 200 ? 'selected' : '' ?>>200</option>
                    <option value="500" <?= $lines === 500 ? 'selected' : '' ?>>500</option>
                    <option value="1000" <?= $lines === 1000 ? 'selected' : '' ?>>1000</option>
                </select>
            </div>

            <!-- Bouton retour -->
            <a href="<?= BASE_URL ?>/" class="btn-log">
                <?= $icon('arrow_back') ?> Retour
            </a>
        </div>
    </div>

    <!-- Informations du fichier -->
    <div class="logs-info">
        <span><strong>Type :</strong> <?= $logType === 'error' ? 'Erreurs Apache' : 'Logs d\'accès' ?></span>
        <span><strong>Taille :</strong> <?= $e($fileSize) ?></span>
        <span><strong>Lignes affichées :</strong> <?= count($logContent) ?> / <?= $lines ?></span>
        <?php if ($fileExists): ?>
            <span><strong>Fichier :</strong> <?= $e(basename($logType === 'error' ? $errorLogPath : $accessLogPath)) ?></span>
        <?php endif; ?>
    </div>

    <!-- Affichage des logs -->
    <div class="log-viewer">
        <?php if (!$fileExists): ?>
            <div class="log-empty">
                <span class="material-symbols-outlined">warning</span>
                <p><strong>Fichier de log introuvable</strong></p>
                <p>Le fichier <code><?= $e($logType === 'error' ? $errorLogPath : $accessLogPath) ?></code> n'existe pas ou n'est pas accessible.</p>
            </div>
        <?php elseif (empty($logContent)): ?>
            <div class="log-empty">
                <span class="material-symbols-outlined">check_circle</span>
                <p><strong>Aucun log disponible</strong></p>
                <p>Le fichier de log est vide.</p>
            </div>
        <?php else: ?>
            <?php foreach ($logContent as $line): ?>
                <?php
                // Détection du niveau de log
                $class = '';
                if (preg_match('/\[error\]|\[crit\]|\[alert\]|\[emerg\]/i', $line)) {
                    $class = 'error';
                } elseif (preg_match('/\[warn\]/i', $line)) {
                    $class = 'warning';
                } elseif (preg_match('/\[notice\]/i', $line)) {
                    $class = 'notice';
                }
                ?>
                <div class="log-line <?= $class ?>"><?= $e($line) ?></div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php require __DIR__ . '/../../Public/assets/html/footer.php'; ?>
