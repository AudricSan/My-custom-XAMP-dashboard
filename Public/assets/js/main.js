// JavaScript principal du dashboard

// Thème sombre automatique selon la préférence système
function applySystemTheme() {
    const logo = document.getElementById('main-logo');
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    if (window.matchMedia('(prefers-color-scheme: dark)').matches) {
        document.body.classList.add('dark-theme');
        if (logo) logo.src = baseUrl + '/assets/images/logoBN.png';
    } else {
        document.body.classList.remove('dark-theme');
        if (logo) logo.src = baseUrl + '/assets/images/logo.png';
    }

    // Mettre à jour le thème des graphiques si disponibles
    if (typeof updateChartsTheme === 'function') {
        updateChartsTheme();
    }
}

// Appliquer le thème au chargement
applySystemTheme();

// Écouter les changements de préférence
window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', applySystemTheme);

// Rafraîchissement automatique des informations système
function updateSysInfo() {
    const baseUrl = typeof BASE_URL !== 'undefined' ? BASE_URL : '';
    fetch(baseUrl + '/api/server-status')
        .then(response => response.json())
        .then(data => {
            // Mise à jour des informations système
            updateElement('hostname', data.hostname);
            updateElement('os', data.os);
            updateElement('disk_used_gb', data.disk_used_gb);
            updateElement('disk_total_gb', data.disk_total_gb);
            updateElement('disk_percent', data.disk_percent);
            updateElement('disk_free_gb', data.disk_free_gb);

            // Mise à jour mémoire avec gestion de "Indisponible"
            const memTotalEl = document.getElementById('mem_total');
            const memFreeEl = document.getElementById('mem_free');
            if (memTotalEl) memTotalEl.textContent = data.mem_total !== '' ? data.mem_total + ' Go' : 'Indisponible';
            if (memFreeEl) memFreeEl.textContent = data.mem_free !== '' ? data.mem_free + ' Go' : 'Indisponible';

            // Mise à jour versions serveur
            updateElement('apacheVersion', data.apacheVersion);
            updateElement('phpVersion', data.phpVersion);
            updateElement('serverSoftware', data.serverSoftware);

            // Mise à jour versions bases de données avec liens
            const mysqlEl = document.getElementById('mysqlVersion');
            if (mysqlEl) {
                mysqlEl.innerHTML = data.mysqlVersion
                    ? data.mysqlVersion + ' (port: ' + data.mysqlPort + ') <a href="http://dev.mysql.com/doc/index.html">Documentation</a>'
                    : 'Non détecté';
            }

            const mariadbEl = document.getElementById('mariadbVersion');
            if (mariadbEl) {
                mariadbEl.innerHTML = data.mariadbVersion
                    ? data.mariadbVersion + ' (port: ' + data.mariadbPort + ') <a href="https://mariadb.com/kb/en/documentation/">Documentation</a>'
                    : 'Non détecté';
            }

            // Mise à jour des statuts de services
            updateServiceStatus('status-apache', data.apacheVersion);
            updateServiceStatus('status-mysql', data.mysqlVersion || data.mariadbVersion);

            // Vérification phpMyAdmin
            checkPhpMyAdmin();
        })
        .catch(() => {
            // En cas d'erreur, ne rien faire
        });
}

// Fonction utilitaire pour mettre à jour un élément
function updateElement(id, value) {
    const el = document.getElementById(id);
    if (el && value !== undefined) {
        el.textContent = value;
    }
}

// Mise à jour du statut d'un service
function updateServiceStatus(dotId, isActive) {
    const dot = document.getElementById(dotId);
    if (!dot) return;

    if (isActive) {
        dot.classList.add('status-active');
        dot.classList.remove('status-inactive');
        dot.title = 'Actif';
    } else {
        dot.classList.remove('status-active');
        dot.classList.add('status-inactive');
        dot.title = 'Inactif';
    }
}

// Vérification de la disponibilité de phpMyAdmin
function checkPhpMyAdmin() {
    const phpmyadminDot = document.getElementById('status-phpmyadmin');
    if (!phpmyadminDot) return;

    fetch('/phpmyadmin/')
        .then(r => {
            if (r.ok) {
                phpmyadminDot.classList.add('status-active');
                phpmyadminDot.classList.remove('status-inactive');
                phpmyadminDot.title = 'Actif';
            } else {
                phpmyadminDot.classList.remove('status-active');
                phpmyadminDot.classList.add('status-inactive');
                phpmyadminDot.title = 'Inactif';
            }
        })
        .catch(() => {
            phpmyadminDot.classList.remove('status-active');
            phpmyadminDot.classList.add('status-inactive');
            phpmyadminDot.title = 'Inactif';
        });
}

// Vérification de Material Symbols et chargement de Font Awesome en fallback
window.addEventListener('load', function() {
    // Tester si Material Symbols est chargée
    const testIcon = document.createElement('span');
    testIcon.className = 'material-symbols-outlined';
    testIcon.textContent = 'settings';
    testIcon.style.position = 'absolute';
    testIcon.style.visibility = 'hidden';
    document.body.appendChild(testIcon);

    const computedFont = window.getComputedStyle(testIcon).fontFamily;
    document.body.removeChild(testIcon);

    // Si Material Symbols n'a pas pu charger, charger Font Awesome en fallback
    if (!computedFont.includes('Material')) {
        const link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = 'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css';
        document.head.appendChild(link);

        // Remapper les icônes Material vers Font Awesome
        const iconMap = {
            'settings': 'fa-gear',
            'build': 'fa-hammer',
            'dns': 'fa-globe',
            'folder': 'fa-folder',
            'fiber_manual_record': 'fa-circle'
        };

        document.querySelectorAll('.material-symbols-outlined').forEach(icon => {
            const text = icon.textContent.trim();
            if (iconMap[text]) {
                icon.className = 'fas ' + iconMap[text];
            }
        });
    }
});

// Toast notifications (utilisé dans vhost_form)
function showToast(message, isError = false) {
    const toast = document.getElementById('toast');
    if (!toast) return;

    toast.innerHTML = message;
    toast.className = isError ? 'toast toast-error' : 'toast toast-success';
    toast.style.display = 'block';

    const hide = () => {
        toast.style.display = 'none';
    };

    setTimeout(hide, 5000);
    toast.onclick = hide;
}

// Initialiser le rafraîchissement automatique au chargement
if (typeof REFRESH_INTERVAL !== 'undefined') {
    updateSysInfo();
    setInterval(updateSysInfo, REFRESH_INTERVAL);
} else {
    // Valeur par défaut de 30 secondes
    updateSysInfo();
    setInterval(updateSysInfo, 30000);
}
