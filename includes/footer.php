<?php
// Usar la variable $base_url definida en header.php
if (!isset($base_url)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . "://" . $host;
    echo '<p>'.$base_url.'</p>';
}
?>
    </main>
    
    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date('Y'); ?> - Sistema FONDEP - Gestión de Proyectos de Innovación Educativa</p>
            <p>Proyecto Académico - Base de Datos II - UTP</p>
        </div>
    </footer>
    
    <!-- JavaScript -->
    <script src="<?php echo $base_url; ?>/assets/js/main.js"></script>
</body>
</html>