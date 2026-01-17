<?php
$pageTitle = 'Gestion VirtualHosts';
require __DIR__ . '/../../Public/assets/html/header.php';
?>

<div class="container">
    <div class="virtual-form card">
        <h2><?= $icon('dns') ?>Créer un VirtualHost</h2>
        <form method="post" action="<?= BASE_URL ?>/vhosts/store" class="vhost-form">
            <label>ServerName* :<br>
                <input type="text" name="servername" required class="input-full" value="<?= isset($editVhost) ? $e($editVhost['servername']) : '' ?>">
            </label>
            <label>DocumentRoot* :<br>
                <input type="text" name="documentroot" required placeholder="ex: C:/xampp/htdocs/monprojet" class="input-full" value="<?= isset($editVhost) ? $e($editVhost['documentroot']) : '' ?>">
            </label>
            <label>Adresse IP (127.0.0.X)* :<br>
                <input type="text" name="ip" value="<?= isset($editVhost) ? $e($editVhost['ip']) : $e($defaultIp) ?>" required placeholder="ex: 127.0.0.20" class="input-full" <?= isset($editVhost) ? 'readonly' : '' ?>>
            </label>
            <?php if ($editVhost): ?>
                <input type="hidden" name="edit_mode" value="1">
            <?php endif; ?>
            <button type="submit" class="input-full"><?= $editVhost ? 'Mettre à jour le Vhost' : 'Créer le Vhost' ?></button>
        </form>
    </div>

    <!-- Liste des vhosts existants -->
    <div class="virtual card">
        <h2>Vhosts existants</h2>
        <ul>
            <?php foreach ($existingVhosts as $vh): ?>
                <li>
                    <b><?= $e($vh['servername']) ?></b> (<?= $e($vh['ip']) ?>)
                    <a href="<?= BASE_URL ?>/vhosts?edit=<?= $e($vh['ip']) ?>">Éditer</a> |
                    <a href="<?= BASE_URL ?>/vhosts?delete=<?= $e($vh['ip']) ?>" onclick="return confirm('Supprimer ce vhost ?');">Supprimer</a>
                </li>
            <?php endforeach; ?>
        </ul>
    </div>

    <div class="btn-card">
        <button onclick="window.location.href='<?= BASE_URL ?>/'" class="btn-large btn-fullwidth">
            &larr; Retour à l'accueil
        </button>
    </div>
</div>

<script>
    // Affichage du toast si message PHP
    <?php if (!empty($message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            showToast(<?= json_encode($message) ?>, <?= strpos($message, 'color:red') !== false ? 'true' : 'false' ?>);
        });
    <?php endif; ?>
</script>

<?php require __DIR__ . '/../../Public/assets/html/footer.php'; ?>
