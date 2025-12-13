<?php
// Usar la variable $base_url definida en header.php
if (!isset($base_url)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . "://" . $host . '/proyecto-final-db';
}
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?php echo $base_url; ?>/">
                <strong>FONDEP</strong> | Sistema de Gestión
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="<?php echo $base_url; ?>/">Dashboard</a></li>
            <li><a href="<?php echo $base_url; ?>/modules/postulaciones/listar.php">Postulaciones</a></li>
            <li><a href="<?php echo $base_url; ?>/modules/evaluaciones/listar.php">Evaluaciones</a></li>
            <li><a href="<?php echo $base_url; ?>/modules/reportes/auditoria.php">Reportes</a></li>
        </ul>
    </div>
</nav>