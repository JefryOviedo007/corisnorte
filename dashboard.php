<?php
session_start();

if (!isset($_SESSION['id'], $_SESSION['rol'])) {
    header("Location: auth.php");
    exit;
}

// 🔥 TODOS LOS ROLES ENTRAN AL MISMO PANEL
require_once "roles/admin.php";
