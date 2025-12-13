<?php
/**
 * Script de verificación de extensiones PHP para Proyecto FONDEP
 * Base de Datos II - UTP
 */

echo "<h1>Diagnóstico de Extensiones PHP - Proyecto FONDEP</h1>";
echo "<h2>Información General</h2>";
echo "<strong>Versión de PHP:</strong> " . phpversion() . "<br>";
echo "<strong>Sistema Operativo:</strong> " . PHP_OS . "<br>";
echo "<strong>Archivo php.ini:</strong> " . php_ini_loaded_file() . "<br><br>";

echo "<h2>Extensiones PDO (PostgreSQL)</h2>";
if (extension_loaded('pdo')) {
    echo "✅ PDO: <span style='color:green;'>INSTALADO</span><br>";
    $drivers = PDO::getAvailableDrivers();
    echo "Drivers disponibles: " . implode(', ', $drivers) . "<br>";
    
    if (in_array('pgsql', $drivers)) {
        echo "✅ PDO_PGSQL: <span style='color:green;'>INSTALADO</span><br>";
    } else {
        echo "❌ PDO_PGSQL: <span style='color:red;'>NO DISPONIBLE</span><br>";
    }
} else {
    echo "❌ PDO: <span style='color:red;'>NO INSTALADO</span><br>";
}

echo "<br><h2>Extensión MongoDB</h2>";
if (extension_loaded('mongodb')) {
    echo "✅ MongoDB: <span style='color:green;'>INSTALADO</span><br>";
    if (class_exists('MongoDB\Driver\Manager')) {
        echo "✅ MongoDB Driver Manager: <span style='color:green;'>DISPONIBLE</span><br>";
    }
} else {
    echo "❌ MongoDB: <span style='color:red;'>NO INSTALADO</span><br>";
    echo "<strong>Solución:</strong> Necesitas instalar el driver de MongoDB<br>";
}

echo "<br><h2>Extensión Cassandra</h2>";
if (extension_loaded('cassandra')) {
    echo "✅ Cassandra: <span style='color:green;'>INSTALADO</span><br>";
} else {
    echo "❌ Cassandra: <span style='color:red;'>NO INSTALADO</span><br>";
    echo "<strong>Nota:</strong> Usaremos alternativa con cURL o biblioteca externa<br>";
}

echo "<br><h2>Otras extensiones útiles</h2>";
$extensiones_utiles = ['curl', 'json', 'mbstring', 'openssl'];
foreach ($extensiones_utiles as $ext) {
    $status = extension_loaded($ext) ? 
        "✅ <span style='color:green;'>INSTALADO</span>" : 
        "❌ <span style='color:red;'>NO INSTALADO</span>";
    echo "{$ext}: {$status}<br>";
}

echo "<br><h2>Todas las extensiones cargadas</h2>";
echo "<pre>";
print_r(get_loaded_extensions());
echo "</pre>";

echo "<h2>Arquitectura del Sistema</h2>";
echo "<strong>PHP Architecture:</strong> " . (PHP_INT_SIZE * 8) . " bits<br>";
echo "<strong>Thread Safety:</strong> " . (PHP_ZTS ? 'Enabled (TS)' : 'Disabled (NTS)') . "<br>";
echo "<strong>Compiler:</strong> " . (defined('COMPILER') ? COMPILER : 'Unknown') . "<br>";

?>