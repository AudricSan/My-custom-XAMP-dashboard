<?php
$pageTitle = 'Gestion des Services';
require __DIR__ . '/../../Public/assets/html/header.php';
?>

<style>
.services-container {
    max-width: 1200px;
    margin: 2em auto;
}

.services-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2em;
}

.services-header h1 {
    margin: 0;
}

.services-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1.5em;
    margin-top: 2em;
}

.service-card {
    background: #fff;
    border-radius: 8px;
    padding: 1.5em;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
    transition: transform 0.2s, box-shadow 0.2s;
}

.service-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 16px rgba(0,0,0,0.15);
}

.service-header {
    display: flex;
    align-items: center;
    gap: 1em;
    margin-bottom: 1.5em;
    padding-bottom: 1em;
    border-bottom: 2px solid #e0e0e0;
}

.service-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.8em;
    background: linear-gradient(135deg, #024378, #04569a);
    color: #fff;
}

.service-info h3 {
    margin: 0;
    font-size: 1.3em;
    color: #024378;
}

.service-status {
    display: inline-flex;
    align-items: center;
    gap: 0.5em;
    padding: 0.3em 0.8em;
    border-radius: 20px;
    font-size: 0.85em;
    font-weight: bold;
    margin-top: 0.5em;
}

.service-status.running {
    background: #e8f5e9;
    color: #2e7d32;
}

.service-status.stopped {
    background: #ffebee;
    color: #c62828;
}

.service-status .status-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    display: inline-block;
}

.service-status.running .status-dot {
    background: #4caf50;
}

.service-status.stopped .status-dot {
    background: #f44336;
}

.service-actions {
    display: flex;
    gap: 0.8em;
}

.service-btn {
    flex: 1;
    padding: 0.7em 1em;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.2s;
    text-decoration: none;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 0.5em;
    font-size: 0.9em;
}

.service-btn .material-symbols-outlined {
    font-size: 1.1em;
}

.btn-start {
    background: #4caf50;
    color: #fff;
}

.btn-start:hover {
    background: #45a049;
}

.btn-stop {
    background: #f44336;
    color: #fff;
}

.btn-stop:hover {
    background: #da190b;
}

.btn-restart {
    background: #ff9800;
    color: #fff;
}

.btn-restart:hover {
    background: #fb8c00;
}

.btn-start:disabled,
.btn-stop:disabled,
.btn-restart:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.message-box {
    padding: 1em 1.5em;
    border-radius: 6px;
    margin-bottom: 1.5em;
    font-weight: bold;
    display: flex;
    align-items: center;
    gap: 0.8em;
}

.message-box.success {
    background: #e8f5e9;
    color: #2e7d32;
    border-left: 4px solid #4caf50;
}

.message-box.error {
    background: #ffebee;
    color: #c62828;
    border-left: 4px solid #f44336;
}

.btn-back {
    padding: 0.7em 1.5em;
    background: #024378;
    color: #fff;
    border: none;
    border-radius: 6px;
    font-weight: bold;
    cursor: pointer;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 0.5em;
    transition: background 0.2s;
}

.btn-back:hover {
    background: #04569a;
}

/* Dark theme */
body.dark-theme .service-card {
    background: #23272a;
    box-shadow: 0 2px 8px rgba(0,0,0,0.4);
}

body.dark-theme .service-card:hover {
    box-shadow: 0 4px 16px rgba(0,0,0,0.6);
}

body.dark-theme .service-header {
    border-bottom-color: #333;
}

body.dark-theme .service-info h3 {
    color: #8ecaff;
}

body.dark-theme .service-icon {
    background: linear-gradient(135deg, #1a4d7a, #2667a3);
}

body.dark-theme .service-status.running {
    background: rgba(76, 175, 80, 0.2);
    color: #81c784;
}

body.dark-theme .service-status.stopped {
    background: rgba(244, 67, 54, 0.2);
    color: #e57373;
}

body.dark-theme .message-box.success {
    background: rgba(76, 175, 80, 0.15);
    color: #81c784;
    border-left-color: #4caf50;
}

body.dark-theme .message-box.error {
    background: rgba(244, 67, 54, 0.15);
    color: #e57373;
    border-left-color: #f44336;
}

body.dark-theme .btn-back {
    background: #1a4d7a;
}

body.dark-theme .btn-back:hover {
    background: #2667a3;
}

/* Responsive */
@media (max-width: 768px) {
    .services-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1em;
    }

    .services-grid {
        grid-template-columns: 1fr;
    }

    .service-actions {
        flex-direction: column;
    }
}
</style>

<div class="services-container">
    <div class="services-header">
        <h1><?= $icon('settings') ?>Gestion des Services</h1>
        <a href="<?= BASE_URL ?>/" class="btn-back">
            <?= $icon('arrow_back') ?> Retour
        </a>
    </div>

    <?php if (!empty($message)): ?>
        <div class="message-box <?= $e($messageType) ?>">
            <?= $icon($messageType === 'success' ? 'check_circle' : 'error') ?>
            <span><?= $e($message) ?></span>
        </div>
    <?php endif; ?>

    <div class="services-grid">
        <?php foreach ($services as $serviceKey => $service): ?>
            <div class="service-card">
                <div class="service-header">
                    <div class="service-icon">
                        <?= $icon($service['icon']) ?>
                    </div>
                    <div class="service-info">
                        <h3><?= $e($service['name']) ?></h3>
                        <div class="service-status <?= $service['status'] ? 'running' : 'stopped' ?>">
                            <span class="status-dot"></span>
                            <?= $service['status'] ? 'En cours d\'exécution' : 'Arrêté' ?>
                        </div>
                    </div>
                </div>

                <div class="service-actions">
                    <?php if ($service['status']): ?>
                        <a href="<?= BASE_URL ?>/services?action=stop&service=<?= $e($serviceKey) ?>"
                           class="service-btn btn-stop"
                           onclick="return confirm('Êtes-vous sûr de vouloir arrêter <?= $e($service['name']) ?> ?')">
                            <?= $icon('stop_circle') ?> Arrêter
                        </a>
                        <a href="<?= BASE_URL ?>/services?action=restart&service=<?= $e($serviceKey) ?>"
                           class="service-btn btn-restart"
                           onclick="return confirm('Êtes-vous sûr de vouloir redémarrer <?= $e($service['name']) ?> ?')">
                            <?= $icon('refresh') ?> Redémarrer
                        </a>
                    <?php else: ?>
                        <a href="<?= BASE_URL ?>/services?action=start&service=<?= $e($serviceKey) ?>"
                           class="service-btn btn-start">
                            <?= $icon('play_circle') ?> Démarrer
                        </a>
                        <button class="service-btn btn-restart" disabled>
                            <?= $icon('refresh') ?> Redémarrer
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div style="margin-top: 2em; padding: 1em; background: rgba(255,152,0,0.1); border-left: 4px solid #ff9800; border-radius: 6px;">
        <p style="margin: 0;"><strong><?= $icon('info') ?> Note :</strong> Les actions peuvent prendre quelques secondes. La page se rechargera automatiquement après chaque action.</p>
    </div>
</div>

<?php require __DIR__ . '/../../Public/assets/html/footer.php'; ?>
