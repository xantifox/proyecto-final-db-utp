<?php
/**
 * Dashboard Principal - Sistema FONDEP
 * Muestra estadísticas en tiempo real desde PostgreSQL
 */

// Configuración de ruta base
$base_url = rtrim(dirname($_SERVER['PHP_SELF']), '/');

// Cargar configuración y clases
require_once 'config/database.php';
require_once 'config/Database.class.php';
require_once 'includes/helpers.php';

// =====================================================
// OBTENER DATOS REALES DE POSTGRESQL
// =====================================================

try {
    $db = DatabasePGSQL::getInstance();
    
    // Estadísticas principales (CORREGIDO: nombres de columnas correctos)
    $stats = $db->query("
        SELECT 
            (SELECT COUNT(*) 
             FROM convocatorias 
             WHERE estado_convocatoria IN ('ABIERTA', 'ACTIVA')) as convocatorias_activas,
            
            (SELECT COUNT(*) 
             FROM postulaciones) as total_postulaciones,
            
            (SELECT COUNT(*) 
             FROM postulaciones 
             WHERE estado_postulacion = 'EN_EVALUACION') as postulaciones_evaluacion,
            
            (SELECT COUNT(*) 
             FROM postulaciones 
             WHERE estado_postulacion = 'APROBADA') as proyectos_aprobados
    ");
    $estadisticas = $stats[0];
    
    // Postulaciones recientes por estado
    $postulaciones_por_estado = $db->query("
        SELECT 
            estado_postulacion,
            COUNT(*) as cantidad
        FROM postulaciones
        GROUP BY estado_postulacion
        ORDER BY cantidad DESC
    ");
    
    // Top 5 regiones con más postulaciones
    $top_regiones = $db->query("
        SELECT 
            r.nombre_region,
            COUNT(p.id_postulacion) as total_postulaciones,
            SUM(CASE WHEN p.estado_postulacion = 'APROBADA' THEN 1 ELSE 0 END) as aprobadas
        FROM regiones r
        LEFT JOIN escuelas e ON r.id_region = e.id_region
        LEFT JOIN postulaciones p ON e.id_escuela = p.id_escuela
        WHERE r.activo = TRUE
        GROUP BY r.id_region, r.nombre_region
        HAVING COUNT(p.id_postulacion) > 0
        ORDER BY total_postulaciones DESC
        LIMIT 5
    ");
    
    // Postulaciones recientes (últimas 10)
    $postulaciones_recientes = $db->query("
        SELECT 
            p.codigo_postulacion,
            p.titulo_proyecto,
            p.estado_postulacion,
            p.monto_solicitado,
            p.fecha_postulacion,
            e.nombre_escuela,
            c.nombre_convocatoria as convocatoria_titulo
        FROM postulaciones p
        INNER JOIN escuelas e ON p.id_escuela = e.id_escuela
        INNER JOIN convocatorias c ON p.id_convocatoria = c.id_convocatoria
        ORDER BY p.fecha_postulacion DESC
        LIMIT 10
    ");
    
    // Estado de conexiones
    $conexion_postgresql = true;
    
} catch (Exception $e) {
    // Si falla la conexión, usar datos de ejemplo
    $conexion_postgresql = false;
    $error_postgresql = $e->getMessage();
    
    // Datos de ejemplo para desarrollo
    $estadisticas = [
        'convocatorias_activas' => 0,
        'total_postulaciones' => 0,
        'postulaciones_evaluacion' => 0,
        'proyectos_aprobados' => 0
    ];
    $postulaciones_por_estado = [];
    $top_regiones = [];
    $postulaciones_recientes = [];
}

// MongoDB y Cassandra aún con datos de prueba
$conexion_mongodb = false;
$conexion_cassandra = false;

require_once 'includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">Dashboard FONDEP</h1>
        <p style="margin: 0; color: #6b7280;">Panel de control - Sistema de Gestión de Proyectos de Innovación Educativa</p>
    </div>

    <?php if (!$conexion_postgresql): ?>
    <!-- Alerta de conexión fallida -->
    <div class="alert alert-danger" style="margin-bottom: 2rem;">
        <strong>⚠️ Error de Conexión PostgreSQL:</strong> <?php echo htmlspecialchars($error_postgresql); ?>
        <br><small>Verifica que la VPN esté activa y las credenciales en config/database.php sean correctas.</small>
    </div>
    <?php endif; ?>

    <!-- Tarjetas de Estadísticas -->
    <div class="row" style="margin-bottom: 2rem;">
        
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['convocatorias_activas']); ?></div>
                <div class="stat-label">Convocatorias Activas</div>
            </div>
        </div>

        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['total_postulaciones']); ?></div>
                <div class="stat-label">Total Postulaciones</div>
            </div>
        </div>

        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['postulaciones_evaluacion']); ?></div>
                <div class="stat-label">En Evaluación</div>
            </div>
        </div>

        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['proyectos_aprobados']); ?></div>
                <div class="stat-label">Proyectos Aprobados</div>
            </div>
        </div>
        
    </div>

    <div class="row">
        
        <!-- Columna Principal -->
        <div class="col-8">
            
            <!-- Postulaciones Recientes -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📋 Postulaciones Recientes</h3>
                </div>
                <div style="padding: 0;">
                    <?php if (count($postulaciones_recientes) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Proyecto</th>
                                <th>Escuela</th>
                                <th>Monto</th>
                                <th>Estado</th>
                                <th>Fecha</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postulaciones_recientes as $post): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo urlModulo('postulaciones', 'detalle', ['codigo' => $post['codigo_postulacion']]); ?>" 
                                       style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                        <?php echo htmlspecialchars($post['codigo_postulacion']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                    </div>
                                    <small style="color: #6b7280;">
                                        <?php echo htmlspecialchars($post['convocatoria_titulo']); ?>
                                    </small>
                                </td>
                                <td>
                                    <div style="max-width: 180px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($post['nombre_escuela']); ?>
                                    </div>
                                </td>
                                <td><?php echo formatearMonto($post['monto_solicitado']); ?></td>
                                <td>
                                    <span class="badge <?php echo obtenerClaseBadge($post['estado_postulacion']); ?>">
                                        <?php echo traducirEstado($post['estado_postulacion']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatearFecha($post['fecha_postulacion']); ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #6b7280;">
                        <p>No hay postulaciones registradas</p>
                    </div>
                    <?php endif; ?>
                </div>
                <div style="padding: 1rem; border-top: 1px solid var(--border-color); text-align: center;">
                    <a href="<?php echo urlModulo('postulaciones'); ?>" 
                       style="color: var(--primary-color); text-decoration: none; font-size: 0.875rem;">
                        Ver todas las postulaciones →
                    </a>
                </div>
            </div>

            <!-- Postulaciones por Estado -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📊 Postulaciones por Estado</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php if (count($postulaciones_por_estado) > 0): ?>
                    <?php foreach ($postulaciones_por_estado as $estado): ?>
                    <div style="margin-bottom: 1rem;">
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span>
                                <span class="badge <?php echo obtenerClaseBadge($estado['estado_postulacion']); ?>" style="margin-right: 0.5rem;">
                                    <?php echo traducirEstado($estado['estado_postulacion']); ?>
                                </span>
                            </span>
                            <strong><?php echo $estado['cantidad']; ?></strong>
                        </div>
                        <div style="background: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                            <?php 
                            $porcentaje = ($estadisticas['total_postulaciones'] > 0) 
                                ? ($estado['cantidad'] / $estadisticas['total_postulaciones']) * 100 
                                : 0;
                            ?>
                            <div style="background: var(--primary-color); height: 100%; width: <?php echo $porcentaje; ?>%;"></div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="text-align: center; color: #6b7280;">No hay datos disponibles</p>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-4">
            
            <!-- Top Regiones -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">🗺️ Top 5 Regiones</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php if (count($top_regiones) > 0): ?>
                    <?php foreach ($top_regiones as $index => $region): ?>
                    <div style="padding: 0.75rem 0; border-bottom: 1px solid var(--border-color);">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <div style="
                                width: 2rem; 
                                height: 2rem; 
                                background: linear-gradient(135deg, #667eea, #764ba2); 
                                border-radius: 50%; 
                                display: flex; 
                                align-items: center; 
                                justify-content: center;
                                color: white;
                                font-weight: bold;
                                font-size: 0.875rem;
                            ">
                                <?php echo $index + 1; ?>
                            </div>
                            <div style="flex: 1;">
                                <div style="font-weight: 500; color: var(--dark-color);">
                                    <?php echo htmlspecialchars($region['nombre_region']); ?>
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">
                                    <?php echo $region['total_postulaciones']; ?> postulaciones
                                    <?php if ($region['aprobadas'] > 0): ?>
                                    | <span style="color: var(--success-color);"><?php echo $region['aprobadas']; ?> aprobadas</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php else: ?>
                    <p style="text-align: center; color: #6b7280; padding: 1rem 0;">Sin datos</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">⚡ Accesos Rápidos</h3>
                </div>
                <div style="padding: 1rem;">
                    <a href="<?php echo urlModulo('convocatorias', 'crear'); ?>" 
                       class="btn btn-primary" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        ➕ Nueva Convocatoria
                    </a>
                    <a href="<?php echo urlModulo('postulaciones', 'crear'); ?>" 
                       class="btn btn-success" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        📝 Nueva Postulación
                    </a>
                    <a href="<?php echo urlModulo('evaluaciones'); ?>" 
                       class="btn btn-warning" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        ⭐ Ver Evaluaciones
                    </a>
                    <a href="<?php echo urlModulo('reportes', 'auditoria'); ?>" 
                       class="btn btn-info" 
                       style="width: 100%;">
                        🔍 Auditoría
                    </a>
                </div>
            </div>

            <!-- Estado de Bases de Datos -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">💾 Estado de Bases de Datos</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span style="
                            width: 0.75rem; 
                            height: 0.75rem; 
                            border-radius: 50%; 
                            background: <?php echo $conexion_postgresql ? '#10b981' : '#ef4444'; ?>;
                        "></span>
                        <span style="font-weight: 500;">PostgreSQL</span>
                        <span class="badge <?php echo $conexion_postgresql ? 'badge-success' : 'badge-danger'; ?>" 
                              style="margin-left: auto; font-size: 0.75rem;">
                            <?php echo $conexion_postgresql ? 'Conectado' : 'Desconectado'; ?>
                        </span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.75rem;">
                        <span style="
                            width: 0.75rem; 
                            height: 0.75rem; 
                            border-radius: 50%; 
                            background: <?php echo $conexion_mongodb ? '#10b981' : '#f59e0b'; ?>;
                        "></span>
                        <span style="font-weight: 500;">MongoDB</span>
                        <span class="badge badge-warning" style="margin-left: auto; font-size: 0.75rem;">
                            Datos de Prueba
                        </span>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 0.5rem;">
                        <span style="
                            width: 0.75rem; 
                            height: 0.75rem; 
                            border-radius: 50%; 
                            background: <?php echo $conexion_cassandra ? '#10b981' : '#f59e0b'; ?>;
                        "></span>
                        <span style="font-weight: 500;">Cassandra</span>
                        <span class="badge badge-warning" style="margin-left: auto; font-size: 0.75rem;">
                            Datos de Prueba
                        </span>
                    </div>
                </div>
            </div>

        </div>
        
    </div>

</div>

<?php require_once 'includes/footer.php'; ?>