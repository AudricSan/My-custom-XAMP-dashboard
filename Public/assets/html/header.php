<?php

use App\Helpers\Functions;

$e = [Functions::class, 'escape'];
$icon = [Functions::class, 'icon'];
$pageTitle = $pageTitle ?? 'Home';
?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <title><?= DASHBOARD_TITLE ?> - <?= $pageTitle ?></title>
    <meta charset='UTF-8'>
    <meta name='viewport' content='width=device-width'>
    <link rel='stylesheet' href='https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200' />
    <link rel='stylesheet' href='<?= BASE_URL ?>/assets/css/style.css' />
    <style>
        /* Fallback si Material Symbols n'est pas disponible */
        .material-symbols-outlined::before {
            font-family: Arial, sans-serif !important;
            font-weight: bold;
            font-style: normal;
        }
    </style>
</head>
<body>
    <div id='head'>
        <div class="head-left">
            <img id="main-logo" src="<?= BASE_URL ?>/assets/images/logo.png" alt="Logo AudricSan">
            <div>
                <h1 class="head-title no-margin"><?= DASHBOARD_TITLE ?></h1>
                <p class="no-margin"><?= DASHBOARD_SUBTITLE ?></p>
            </div>
        </div>
        <div class="head-right">
            <h3>Statut des services</h3>
            <ul class="service-status-list">
                <li><span id="status-apache" class="status-dot"></span><strong>Apache</strong></li>
                <li><span id="status-mysql" class="status-dot"></span><strong>MySQL / MariaDB</strong></li>
                <li><span id="status-phpmyadmin" class="status-dot"></span><strong>phpMyAdmin</strong></li>
            </ul>
        </div>
    </div>