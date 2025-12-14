<?php
/**
 * SCRIPT DE PRUEBA - PostgreSQL REMOTO vía VPN
 * Sistema FONDEP - Proyecto Base de Datos II
 */

require_once 'config/db_postgresql.php';

echo "<html><head><meta charset='UTF-8'>";
echo "<title>Test PostgreSQL Remoto - FONDEP</title>";
echo "<style>
    body { font-family: Arial; margin: 20px; background: #f5f5f5; }
    .test-section { background: white; padding: 20px; margin: 15px 0; border-radius: 5px; }
    .success { color: green; font-weight: bold; }
    .error { color: red; font-weight: bold; }
    table { border-collapse: collapse; width: 100%; margin: 10px 0; }
    th { background: #4CAF50; color: white; padding: 10px; text-align: left; }
    td { border: 1px solid #ddd; padding: 8px; }
    tr:nth-child(even) { background: #f2f2f2; }
    .info-box { background: #e3f2fd; padding: 10px; border-left: 4px solid #2196F3; margin: 10px 0; }
</style></head><body>";

echo "<h1>🔌 Test Conexión PostgreSQL REMOTO - FONDEP</h1>";

// ============================================
// TEST 0: Configuración
// ============================================
echo "<div class='test-section'>";
echo "<h2>📋 Configuración Actual</h2>";
echo "<table>";
echo "<tr><th>Parámetro</th><th>Valor</th></tr>";
echo "<tr><td>Host</td><td><strong>" . PG_HOST . "</strong></td></tr>";
echo "<tr><td>Puerto</td><td><strong>" . PG_PORT . "</strong></td></tr>";
echo "<tr><td>Base de datos</td><td><strong>" . PG_DATABASE . "</strong></td></tr>";
echo "<tr><td>Usuario</td><td><strong>" . PG_USER . "</strong></td></tr>";
echo "<tr><td>SSL Mode</td><td><strong>" . PG_SSL_MODE . "</strong></td></tr>";
echo "</table>";
echo "<div class='info-box'>⚠️ <strong>VPN debe estar activa</strong></div>";
echo "</div>";

// ============================================
// TEST 1: Conectividad de red
// ============================================
echo "<div class='test-section'>";
echo "<h2>🌐 Test 1: Conectividad de Red</h2>";

$socket = @fsockopen(PG_HOST, PG_PORT, $errno, $errstr, 5);

if ($socket) {
    echo "<p class='success'>✅ Servidor alcanzable en " . PG_HOST . ":" . PG_PORT . "</p>";
    fclose($socket);
} else {
    echo "<p class='error'>❌ No se alcanza " . PG_HOST . ":" . PG_PORT . "</p>";
    echo "<p>Error: [$errno] $errstr</p>";
    echo "<div class='info-box'><strong>Verifica:</strong> VPN, IP correcta, firewall</div>";
    echo "</div></body></html>";
    exit;
}
echo "</div>";

// ============================================
// TEST 2: Autenticación PostgreSQL
// ============================================
echo "<div class='test-section'>";
echo "<h2>🔐 Test 2: Autenticación PostgreSQL</h2>";

$conn = conectar_postgresql();

if (!$conn) {
    echo "<p class='error'>❌ Autenticación falló</p>";
    echo "</div></body></html>";
    exit;
}

echo "<p class='success'>✅ Autenticación exitosa</p>";
pg_close($conn);
echo "</div>";

// ============================================
// TEST 3: Información del servidor
// ============================================
echo "<div class='test-section'>";
echo "<h2>ℹ️ Test 3: Info del Servidor</h2>";

$info = info_servidor_postgresql();

if ($info) {
    echo "<table>";
    foreach ($info as $key => $value) {
        echo "<tr><td><strong>" . ucfirst($key) . "</strong></td><td>$value</td></tr>";
    }
    echo "</table>";
}
echo "</div>";

// ============================================
// TEST 4: Listar TABLAS DEL PROYECTO FONDEP
// ============================================
echo "<div class='test-section'>";
echo "<h2>📊 Test 4: Tablas del Sistema FONDEP</h2>";

$query = "
    SELECT table_name, 
           pg_size_pretty(pg_total_relation_size(quote_ident(table_name)::regclass)) as tamaño
    FROM information_schema.tables 
    WHERE table_schema = 'public' 
    ORDER BY table_name
";

$tablas = ejecutar_consulta($query, true);

if (count($tablas) > 0) {
    echo "<p class='success'>✅ Encontradas " . count($tablas) . " tablas</p>";
    echo "<table>";
    echo "<tr><th>#</th><th>Tabla</th><th>Tamaño</th></tr>";
    $i = 1;
    foreach ($tablas as $tabla) {
        echo "<tr><td>$i</td><td><strong>{$tabla['table_name']}</strong></td><td>{$tabla['tamaño']}</td></tr>";
        $i++;
    }
    echo "</table>";
} else {
    echo "<p class='error'>❌ No hay tablas</p>";
}
echo "</div>";

// ============================================
// TEST 5: Consulta REGIONES (datos reales)
// ============================================
echo "<div class='test-section'>";
echo "<h2>🔍 Test 5: Datos de Regiones del Perú</h2>";

$query = "
    SELECT id_region, nombre_region, codigo_ubigeo 
    FROM regiones 
    WHERE activo = TRUE
    ORDER BY nombre_region 
    LIMIT 10
";

$regiones = ejecutar_consulta($query, true);

if (count($regiones) > 0) {
    echo "<p class='success'>✅ Regiones recuperadas</p>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Región</th><th>UBIGEO</th></tr>";
    foreach ($regiones as $region) {
        echo "<tr>";
        echo "<td>{$region['id_region']}</td>";
        echo "<td><strong>{$region['nombre_region']}</strong></td>";
        echo "<td>{$region['codigo_ubigeo']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>⚠️ Tabla 'regiones' sin datos</p>";
}
echo "</div>";

// ============================================
// TEST 6: Estadísticas del Sistema FONDEP
// ============================================
echo "<div class='test-section'>";
echo "<h2>📈 Test 6: Estadísticas FONDEP</h2>";

$tablas_fondep = [
    'regiones' => 'Departamentos del Perú',
    'escuelas' => 'Instituciones Educativas',
    'convocatorias' => 'Concursos FONDEP',
    'requisitos_convocatoria' => 'Requisitos por Convocatoria',
    'postulaciones' => 'Proyectos Presentados',
    'evaluadores' => 'Especialistas Evaluadores',
    'asignaciones_evaluacion' => 'Asignaciones de Evaluación',
    'decisiones_financiamiento' => 'Decisiones de Financiamiento',
    'desembolsos' => 'Cronograma de Pagos'
];

echo "<table>";
echo "<tr><th>Tabla</th><th>Descripción</th><th>Registros</th><th>Estado</th></tr>";

foreach ($tablas_fondep as $tabla => $descripcion) {
    $query = "SELECT COUNT(*) as total FROM $tabla";
    $resultado = ejecutar_consulta($query);
    
    if ($resultado && count($resultado) > 0) {
        $total = $resultado[0]['total'];
        $estado = $total > 0 ? "✅ Con datos" : "⚠️ Vacía";
        echo "<tr>";
        echo "<td><strong>$tabla</strong></td>";
        echo "<td>$descripcion</td>";
        echo "<td>" . number_format($total) . "</td>";
        echo "<td>$estado</td>";
        echo "</tr>";
    } else {
        echo "<tr>";
        echo "<td><strong>$tabla</strong></td>";
        echo "<td>$descripcion</td>";
        echo "<td colspan='2' class='error'>❌ No existe</td>";
        echo "</tr>";
    }
}
echo "</table>";
echo "</div>";

// ============================================
// TEST 7: Consulta con JOIN (Escuelas por Región)
// ============================================
echo "<div class='test-section'>";
echo "<h2>🔗 Test 7: Consulta con JOIN - Escuelas por Región</h2>";

$query = "
    SELECT r.nombre_region, COUNT(e.id_escuela) as total_escuelas
    FROM regiones r
    LEFT JOIN escuelas e ON r.id_region = e.id_region
    WHERE r.activo = TRUE
    GROUP BY r.id_region, r.nombre_region
    HAVING COUNT(e.id_escuela) > 0
    ORDER BY total_escuelas DESC
    LIMIT 5
";

$datos_join = ejecutar_consulta($query, true);

if (count($datos_join) > 0) {
    echo "<p class='success'>✅ Consulta JOIN exitosa</p>";
    echo "<table>";
    echo "<tr><th>Región</th><th>Escuelas Registradas</th></tr>";
    foreach ($datos_join as $fila) {
        echo "<tr>";
        echo "<td><strong>{$fila['nombre_region']}</strong></td>";
        echo "<td>{$fila['total_escuelas']}</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p class='error'>⚠️ No hay datos para el JOIN</p>";
}
echo "</div>";

// ============================================
// RESUMEN FINAL
// ============================================
echo "<div class='test-section' style='background:#e8f5e9;'>";
echo "<h2>✅ RESUMEN</h2>";
echo "<p class='success' style='font-size:18px;'>🎉 Conexión a PostgreSQL remoto funcionando</p>";
echo "<p><strong>Próximos pasos:</strong></p>";
echo "<ol>";
echo "<li>Si las tablas están vacías, ejecutar scripts de datos de prueba</li>";
echo "<li>Crear módulos PHP para gestión FONDEP</li>";
echo "<li>Continuar con MongoDB y Cassandra</li>";
echo "</ol>";
echo "</div>";

echo "</body></html>";
?>