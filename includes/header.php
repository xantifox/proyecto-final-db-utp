<?php
// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Detectar la ruta base del proyecto automáticamente
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$host = $_SERVER['HTTP_HOST'];
$base_path = '';
$base_url = $protocol . "://" . $host . $base_path;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?>Sistema FONDEP</title>
    
    <!-- CSS Principal - Ruta absoluta -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>/assets/css/styles.css">
    
    <!-- Fuentes de Google -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <?php 
    // Pasar la variable base_url al navbar
    include __DIR__ . '/navbar.php'; 
    ?>
    
    <main class="main-content">