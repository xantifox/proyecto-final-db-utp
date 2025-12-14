<?php
/**
 * MÓDULO: REPORTES - SEGUIMIENTO
 * Seguimiento de avance de proyectos aprobados
 * 
 * Integración de datos:
 * - PostgreSQL: Datos de postulaciones aprobadas
 * - Cassandra: Métricas de seguimiento por fecha (time-series)
 */

$page_title = "Seguimiento de Proyectos";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener postulaciones aprobadas
$postulaciones_aprobadas = array_filter(obtenerPostulaciones(), fn($p) => $p['estado'] == 'APROBADA');

// Obtener métricas de Cassandra
$metricas = obtenerMetricas();

// Organizar métricas por postulación
$metricas_por_postulacion = [];
foreach ($metricas as $metrica) {
    $post_id = $metrica['postulacion_id'];
    if (!isset($metricas_por_postulacion[$post_id])) {
        $metricas_por_postulacion[$post_id] = [];
    }
    $metricas_por_postulacion[$post_id][] = $metrica;
}

// Estadísticas generales
$total_proyectos = count($postulaciones_aprobadas);
$monto_total_aprobado = array_sum(array_column($postulaciones_aprobadas, 'monto_solicitado'));
$monto_total_ejecutado = array_sum(array_column($metricas, 'monto_ejecutado'));
$avance_promedio = count($metricas) > 0 ? array_sum(array_column($metricas, 'avance_porcentaje')) / count($metricas) : 0;
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Seguimiento de Proyectos</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">📅 Seguimiento de Proyectos</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Monitoreo de avance y ejecución de proyectos aprobados
        </p>
    </div>

    <!-- Alerta de integración -->
    <div class="alert alert-warning" style="margin-bottom: 2rem; border: 2px solid #f59e0b;">
        <strong>⚡ Métricas de Cassandra:</strong> Los datos de seguimiento se almacenan en Cassandra 
        para consultas eficientes de series temporales. Cada reporte de avance genera un registro 
        con partition key (postulacion_id, fecha_reporte).
    </div>

    <!-- KPIs de Seguimiento -->
    <div class="row" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total_proyectos; ?></div>
                <div class="stat-label">Proyectos Aprobados</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo number_format($avance_promedio, 1); ?>%</div>
                <div class="stat-label">Avance Promedio</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo formatearMonto($monto_total_aprobado); ?></div>
                <div class="stat-label">Monto Aprobado</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="stat-number"><?php echo formatearMonto($monto_total_ejecutado); ?></div>
                <div class="stat-label">Monto Ejecutado</div>
            </div>
        </div>
    </div>

    <!-- Listado de Proyectos con Seguimiento -->
    <?php foreach ($postulaciones_aprobadas as $post): ?>
        <?php 
        $convocatoria = obtenerConvocatoriaPorId($post['convocatoria_id']);
        $escuela = array_filter(obtenerEscuelas(), fn($e) => $e['id'] == $post['escuela_id']);
        $escuela = !empty($escuela) ? reset($escuela) : null;
        $metricas_proyecto = $metricas_por_postulacion[$post['id']] ?? [];
        $ultima_metrica = !empty($metricas_proyecto) ? end($metricas_proyecto) : null;
        $avance_actual = $ultima_metrica['avance_porcentaje'] ?? 0;
        $monto_ejecutado = $ultima_metrica['monto_ejecutado'] ?? 0;
        ?>
        
        <div class="card" style="margin-bottom: 1.5rem;">
            <!-- Header del Proyecto -->
            <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background: linear-gradient(135deg, #f9fafb, #ffffff);">
                <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                    <div style="flex: 1;">
                        <h3 style="margin: 0 0 0.5rem 0; color: var(--primary-color);">
                            <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                        </h3>
                        <div style="font-size: 0.875rem; color: #6b7280;">
                            <strong>Código:</strong> <?php echo htmlspecialchars($post['codigo']); ?> | 
                            <strong>Escuela:</strong> <?php echo htmlspecialchars($escuela['nombre'] ?? 'N/A'); ?> | 
                            <strong>Región:</strong> <?php echo htmlspecialchars(obtenerNombreRegion($escuela['region_id'] ?? 0)); ?>
                        </div>
                    </div>
                    <a href="../postulaciones/detalle.php?codigo=<?php echo $post['codigo']; ?>" 
                       class="btn btn-sm btn-info">
                        👁️ Ver Detalle
                    </a>
                </div>
            </div>

            <!-- Contenido del Proyecto -->
            <div style="padding: 1.5rem;">
                <div class="row">
                    
                    <!-- Información General -->
                    <div class="col-4">
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.875rem; color: #6b7280;">Monto Aprobado</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: var(--success-color);">
                                <?php echo formatearMonto($post['monto_solicitado']); ?>
                            </div>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Monto Ejecutado</div>
                            <div style="font-size: 1.25rem; font-weight: 600; color: var(--primary-color);">
                                <?php echo formatearMonto($monto_ejecutado); ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #6b7280;">
                                <?php echo number_format(($monto_ejecutado / $post['monto_solicitado']) * 100, 1); ?>% del presupuesto
                            </div>
                        </div>
                    </div>

                    <!-- Barra de Avance -->
                    <div class="col-8">
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-weight: 600; color: var(--dark-color);">Avance del Proyecto</span>
                                <span style="font-weight: 700; font-size: 1.25rem; color: var(--primary-color);">
                                    <?php echo number_format($avance_actual, 1); ?>%
                                </span>
                            </div>
                            <div style="background-color: #e5e7eb; height: 30px; border-radius: 15px; overflow: hidden; position: relative;">
                                <div style="background: linear-gradient(90deg, #10b981, #059669); 
                                            height: 100%; 
                                            width: <?php echo $avance_actual; ?>%;
                                            transition: width 0.3s ease;">
                                </div>
                                <div style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); 
                                            font-weight: 600; color: <?php echo $avance_actual > 50 ? 'white' : 'var(--dark-color)'; ?>;">
                                    <?php echo number_format($avance_actual, 1); ?>% completado
                                </div>
                            </div>
                        </div>

                        <!-- Timeline de Métricas -->
                        <?php if (!empty($metricas_proyecto)): ?>
                            <div style="background-color: #f9fafb; padding: 1rem; border-radius: 6px;">
                                <strong style="font-size: 0.875rem; color: #6b7280;">Histórico de Reportes:</strong>
                                <div style="margin-top: 0.5rem; display: flex; gap: 1rem; overflow-x: auto;">
                                    <?php foreach ($metricas_proyecto as $metrica): ?>
                                        <div style="min-width: 150px; background-color: white; padding: 0.75rem; border-radius: 6px; border: 1px solid var(--border-color);">
                                            <div style="font-size: 0.75rem; color: #6b7280;">
                                                <?php echo formatearFecha($metrica['fecha_reporte']); ?>
                                            </div>
                                            <div style="font-weight: 600; color: var(--primary-color); font-size: 1.125rem;">
                                                <?php echo number_format($metrica['avance_porcentaje'], 1); ?>%
                                            </div>
                                            <div style="font-size: 0.75rem; color: #6b7280;">
                                                <?php echo $metrica['actividades_completadas']; ?> actividades
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php else: ?>
                            <div class="alert alert-warning" style="margin: 0;">
                                <strong>⚠️ Sin reportes de seguimiento</strong><br>
                                No se han registrado reportes de avance para este proyecto.
                            </div>
                        <?php endif; ?>
                    </div>

                </div>

                <!-- Última Observación -->
                <?php if ($ultima_metrica && !empty($ultima_metrica['observaciones'])): ?>
                    <div style="margin-top: 1rem; padding: 1rem; background-color: #eff6ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                        <strong style="color: var(--primary-color);">Última Observación:</strong>
                        <p style="margin: 0.5rem 0 0 0; color: #374151;">
                            <?php echo htmlspecialchars($ultima_metrica['observaciones']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>

    <?php if (empty($postulaciones_aprobadas)): ?>
        <div class="card">
            <div style="padding: 3rem; text-align: center; color: #6b7280;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📂</div>
                <h3 style="margin: 0 0 0.5rem 0;">No hay proyectos aprobados</h3>
                <p style="margin: 0;">Cuando se aprueben proyectos, aparecerán aquí con su seguimiento.</p>
            </div>
        </div>
    <?php endif; ?>

    <!-- Información Técnica -->
    <div class="row" style="margin-top: 2rem; gap: 1rem;">
        <div class="col-6">
            <div class="alert alert-info">
                <strong>📊 Query Cassandra para Métricas:</strong>
                <pre style="background-color: #1f2937; color: #10b981; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; overflow-x: auto; font-size: 0.75rem;">
SELECT * FROM metricas_seguimiento
WHERE postulacion_id = 'POST-2024-001'
  AND fecha_reporte >= '2024-01-01'
ORDER BY fecha_reporte DESC;
                </pre>
            </div>
        </div>
        <div class="col-6">
            <div class="alert alert-success">
                <strong>✅ Ventajas para Seguimiento:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                    <li>Consultas rápidas por proyecto y rango de fechas</li>
                    <li>Partition key por postulacion_id + fecha</li>
                    <li>Ideal para gráficos de tendencias temporales</li>
                    <li>Escalable para miles de proyectos simultáneos</li>
                </ul>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>