<?php
/**
 * MÓDULO: REPORTES - AUDITORÍA
 * Log de eventos del sistema para trazabilidad y seguridad
 * 
 * Base de datos principal: Cassandra
 * - Consultas time-series por fecha
 * - Filtros por tipo de evento, usuario, tabla
 * - Demuestra capacidad de Cassandra para logs históricos
 */

$page_title = "Auditoría del Sistema";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener eventos de Cassandra
$todos_eventos = obtenerEventos(100);

// Aplicar filtros
$filtro_tipo = $_GET['tipo'] ?? '';
$filtro_usuario = $_GET['usuario'] ?? '';
$filtro_criticidad = $_GET['criticidad'] ?? '';

$eventos_filtrados = $todos_eventos;

if ($filtro_tipo) {
    $eventos_filtrados = array_filter($eventos_filtrados, fn($e) => $e['tipo_evento'] == $filtro_tipo);
}

if ($filtro_usuario) {
    $eventos_filtrados = array_filter($eventos_filtrados, fn($e) => stripos($e['usuario'], $filtro_usuario) !== false);
}

if ($filtro_criticidad) {
    $eventos_filtrados = array_filter($eventos_filtrados, fn($e) => $e['nivel_criticidad'] == $filtro_criticidad);
}

// Limitar a últimos 50
$eventos_mostrar = array_slice($eventos_filtrados, 0, 50);

// Estadísticas
$total_eventos = count($todos_eventos);
$eventos_criticos = count(array_filter($todos_eventos, fn($e) => $e['nivel_criticidad'] == 'CRITICAL'));
$eventos_hoy = count(array_filter($todos_eventos, fn($e) => date('Y-m-d', strtotime($e['timestamp'])) == date('Y-m-d')));

// Tipos de evento únicos
$tipos_evento = array_unique(array_column($todos_eventos, 'tipo_evento'));
sort($tipos_evento);

// Usuarios únicos
$usuarios = array_unique(array_column($todos_eventos, 'usuario'));
sort($usuarios);
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Auditoría</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">🔍 Auditoría del Sistema</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Log histórico de eventos del sistema almacenado en Cassandra
        </p>
    </div>

    <!-- Alerta de integración Cassandra -->
    <div class="alert alert-warning" style="margin-bottom: 2rem; border: 2px solid #f59e0b;">
        <strong>⚡ Datos de Cassandra (Time-Series):</strong> Esta vista demuestra consultas optimizadas 
        para series temporales con partition keys por fecha y clustering por timestamp.
        <br><strong>Query ejemplo:</strong> 
        <code>SELECT * FROM eventos_sistema WHERE fecha_evento_day = '2024-12-13' AND timestamp_evento > '2024-12-13 00:00:00' ORDER BY timestamp_evento DESC;</code>
    </div>

    <!-- Estadísticas Rápidas -->
    <div class="row" style="margin-bottom: 2rem;">
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo number_format($total_eventos); ?></div>
                <div class="stat-label">Total Eventos</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $eventos_hoy; ?></div>
                <div class="stat-label">Eventos Hoy</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <div class="stat-number"><?php echo $eventos_criticos; ?></div>
                <div class="stat-label">Eventos Críticos</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1rem;">🔎 Filtros de Búsqueda</h3>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap;">
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Tipo de Evento</label>
                    <select name="tipo" class="form-control">
                        <option value="">Todos los tipos</option>
                        <?php foreach ($tipos_evento as $tipo): ?>
                            <option value="<?php echo $tipo; ?>" <?php echo $filtro_tipo == $tipo ? 'selected' : ''; ?>>
                                <?php echo $tipo; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Usuario</label>
                    <input type="text" 
                           name="usuario" 
                           class="form-control"
                           placeholder="Buscar por usuario..."
                           value="<?php echo htmlspecialchars($filtro_usuario); ?>">
                </div>
                <div style="flex: 1; min-width: 200px;">
                    <label class="form-label">Criticidad</label>
                    <select name="criticidad" class="form-control">
                        <option value="">Todos los niveles</option>
                        <option value="INFO" <?php echo $filtro_criticidad == 'INFO' ? 'selected' : ''; ?>>INFO</option>
                        <option value="WARNING" <?php echo $filtro_criticidad == 'WARNING' ? 'selected' : ''; ?>>WARNING</option>
                        <option value="ERROR" <?php echo $filtro_criticidad == 'ERROR' ? 'selected' : ''; ?>>ERROR</option>
                        <option value="CRITICAL" <?php echo $filtro_criticidad == 'CRITICAL' ? 'selected' : ''; ?>>CRITICAL</option>
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
                </div>
                <div>
                    <a href="auditoria.php" class="btn btn-secondary">🔄 Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Eventos -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1.125rem;">
                📋 Log de Eventos (últimos <?php echo count($eventos_mostrar); ?>)
            </h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Timestamp</th>
                        <th>Tipo</th>
                        <th>Usuario</th>
                        <th>Tabla Afectada</th>
                        <th>Descripción</th>
                        <th>IP Origen</th>
                        <th>Criticidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($eventos_mostrar)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No se encontraron eventos con los filtros aplicados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($eventos_mostrar as $evento): ?>
                            <tr>
                                <td style="white-space: nowrap; font-size: 0.875rem;">
                                    <strong><?php echo date('d/m/Y', strtotime($evento['timestamp'])); ?></strong><br>
                                    <span style="color: #6b7280;"><?php echo date('H:i:s', strtotime($evento['timestamp'])); ?></span>
                                </td>
                                <td>
                                    <?php
                                    $color_tipo = [
                                        'CREATE' => '#10b981',
                                        'UPDATE' => '#3b82f6',
                                        'DELETE' => '#ef4444',
                                        'LOGIN' => '#8b5cf6',
                                        'EXPORT' => '#f59e0b',
                                        'APPROVAL' => '#059669',
                                        'REJECTION' => '#dc2626'
                                    ];
                                    $color = $color_tipo[$evento['tipo_evento']] ?? '#6b7280';
                                    ?>
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; background-color: <?php echo $color; ?>; color: white; border-radius: 4px; font-size: 0.75rem; font-weight: 600;">
                                        <?php echo $evento['tipo_evento']; ?>
                                    </span>
                                </td>
                                <td style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($evento['usuario']); ?>
                                </td>
                                <td>
                                    <code style="background-color: #f3f4f6; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($evento['tabla_afectada']); ?>
                                    </code>
                                </td>
                                <td style="max-width: 300px;">
                                    <?php echo htmlspecialchars($evento['descripcion']); ?>
                                </td>
                                <td style="font-size: 0.875rem; color: #6b7280;">
                                    <?php echo htmlspecialchars($evento['ip_origen']); ?>
                                </td>
                                <td>
                                    <?php
                                    $color_criticidad = [
                                        'INFO' => 'badge-info',
                                        'WARNING' => 'badge-warning',
                                        'ERROR' => 'badge-danger',
                                        'CRITICAL' => 'badge-danger'
                                    ];
                                    $badge_class = $color_criticidad[$evento['nivel_criticidad']] ?? 'badge-secondary';
                                    ?>
                                    <span class="badge <?php echo $badge_class; ?>">
                                        <?php echo $evento['nivel_criticidad']; ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <?php if (count($eventos_filtrados) > 50): ?>
            <div style="padding: 1rem; background-color: #f9fafb; text-align: center; color: #6b7280; font-size: 0.875rem;">
                Mostrando 50 de <?php echo count($eventos_filtrados); ?> eventos encontrados
            </div>
        <?php endif; ?>
    </div>

    <!-- Información Técnica -->
    <div class="row" style="margin-top: 2rem; gap: 1rem;">
        <div class="col-6">
            <div class="alert alert-info">
                <strong>📊 Estructura de Cassandra:</strong>
                <pre style="background-color: #1f2937; color: #10b981; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; overflow-x: auto; font-size: 0.75rem;">
CREATE TABLE eventos_sistema (
    fecha_evento_day DATE,
    timestamp_evento TIMESTAMP,
    id_evento TEXT,
    tipo_evento TEXT,
    usuario TEXT,
    tabla_afectada TEXT,
    nivel_criticidad TEXT,
    PRIMARY KEY (fecha_evento_day, timestamp_evento, id_evento)
) WITH CLUSTERING ORDER BY (timestamp_evento DESC);
                </pre>
            </div>
        </div>
        <div class="col-6">
            <div class="alert alert-success">
                <strong>✅ Ventajas de Cassandra para Auditoría:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                    <li>Alta velocidad de escritura para logs en tiempo real</li>
                    <li>Partition key por fecha optimiza queries temporales</li>
                    <li>Escalabilidad horizontal para millones de eventos</li>
                    <li>TTL automático para retención de datos (90 días)</li>
                    <li>Clustering por timestamp para orden cronológico</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>