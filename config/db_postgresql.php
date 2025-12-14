<?php
/**
 * CONFIGURACIÓN DE CONEXIÓN A POSTGRESQL REMOTO
 * Proyecto FONDEP - Base de Datos II
 * 
 * IMPORTANTE: Este servidor está en red remota, requiere VPN activa
 */

// ============================================
// PARÁMETROS DE CONEXIÓN - SERVIDOR REMOTO
// ============================================

define('PG_HOST', '127.0.0.1');        // ⚠️ CAMBIA ESTO: IP del servidor remoto
define('PG_PORT', '5432');                  // Puerto (confirma con el admin)
define('PG_DATABASE', 'fondep');         // ⚠️ CAMBIA ESTO: nombre de la BD
define('PG_USER', 'postgres');           // ⚠️ CAMBIA ESTO: usuario proporcionado
define('PG_PASSWORD', '12345678');   // ⚠️ CAMBIA ESTO: contraseña proporcionada

// Configuración SSL (si el servidor lo requiere)
define('PG_SSL_MODE', 'prefer');  // Opciones: disable, allow, prefer, require

/**
 * Función para conectar a PostgreSQL REMOTO
 * @return resource|false Conexión o false si falla
 */
function conectar_postgresql() {
    $conn_string = sprintf(
        "host=%s port=%s dbname=%s user=%s password=%s connect_timeout=10 sslmode=%s",
        PG_HOST,
        PG_PORT,
        PG_DATABASE,
        PG_USER,
        PG_PASSWORD,
        PG_SSL_MODE
    );
    
    $conexion = @pg_connect($conn_string);
    
    if (!$conexion) {
        $error_msg = pg_last_error();
        
        echo "<div style='background:#ffcccc; padding:15px; border:2px solid red;'>";
        echo "<h3>❌ ERROR DE CONEXIÓN A POSTGRESQL REMOTO</h3>";
        echo "<strong>Servidor:</strong> " . PG_HOST . ":" . PG_PORT . "<br>";
        echo "<strong>Base de datos:</strong> " . PG_DATABASE . "<br>";
        echo "<strong>Usuario:</strong> " . PG_USER . "<br>";
        echo "<strong>Error:</strong> " . ($error_msg ? $error_msg : "No se pudo establecer conexión") . "<br>";
        echo "<hr>";
        echo "<strong>Verifica:</strong><ul>";
        echo "<li>VPN activa y conectada</li>";
        echo "<li>Firewall permite puerto " . PG_PORT . "</li>";
        echo "<li>Credenciales correctas</li>";
        echo "<li>PostgreSQL corriendo en servidor</li>";
        echo "</ul></div>";
        
        return false;
    }
    
    return $conexion;
}

/**
 * Ejecutar consultas SELECT
 */
function ejecutar_consulta($query, $debug = false) {
    $inicio = microtime(true);
    $conn = conectar_postgresql();
    
    if (!$conn) return [];
    
    if ($debug) {
        echo "<div style='background:#f0f0f0; padding:10px; margin:10px 0;'>";
        echo "<strong>🔍 Query:</strong> <code>" . htmlspecialchars($query) . "</code><br>";
    }
    
    $resultado = pg_query($conn, $query);
    
    if (!$resultado) {
        echo "<div style='background:#ffcccc; padding:10px;'>";
        echo "❌ <strong>ERROR:</strong> " . pg_last_error($conn);
        echo "</div>";
        pg_close($conn);
        return [];
    }
    
    $datos = pg_fetch_all($resultado);
    $num_rows = pg_num_rows($resultado);
    $tiempo = round((microtime(true) - $inicio) * 1000, 2);
    
    if ($debug) {
        echo "✅ Filas: $num_rows | Tiempo: {$tiempo}ms</div>";
    }
    
    pg_close($conn);
    return $datos ? $datos : [];
}

/**
 * Ejecutar INSERT/UPDATE/DELETE
 */
function ejecutar_comando($query) {
    $conn = conectar_postgresql();
    if (!$conn) return false;
    
    $resultado = pg_query($conn, $query);
    
    if (!$resultado) {
        echo "<div style='background:#ffcccc; padding:10px;'>";
        echo "❌ ERROR: " . pg_last_error($conn);
        echo "</div>";
        pg_close($conn);
        return false;
    }
    
    pg_close($conn);
    return true;
}

/**
 * Información del servidor remoto
 */
function info_servidor_postgresql() {
    $conn = conectar_postgresql();
    if (!$conn) return null;
    
    $info = [
        'host' => pg_host($conn),
        'port' => pg_port($conn),
        'dbname' => pg_dbname($conn),
        'version' => pg_version($conn)['server'],
        'encoding' => pg_client_encoding($conn),
        'status' => 'Conectado ✅'
    ];
    
    pg_close($conn);
    return $info;
}
?>