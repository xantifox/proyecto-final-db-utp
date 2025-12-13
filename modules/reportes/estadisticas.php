<?php
/**
 * MÓDULO: REPORTES - ESTADÍSTICAS
 * Dashboard de estadísticas generales del sistema
 * 
 * Integración de datos:
 * - PostgreSQL: Estadísticas transaccionales y agregaciones
 * - MongoDB: Análisis de propuestas y evaluaciones
 * - Cassandra: Métricas históricas y tendencias
 */

$page_title = "Estadísticas del Sistema";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener datos
$convocatorias = obtenerConvocatorias();
$postulaciones = obtenerPostulaciones();
$evaluaciones = obtenerEvaluaciones();
$regiones = obtenerRegiones();

// Estadísticas generales
$total_convocatorias = count($convocatorias);
$total_postulaciones = count($postulaciones);
$total_evaluaciones = count($evaluaciones);

// Estadísticas por estado
$post_aprobadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'APROBADA'));
$post_rechazadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'RECHAZADA'));
$post_en_evaluacion = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_EVALUACION'));

// Presupuestos
$presupuesto_total = array_sum(array_column($convocatorias, 'presupuesto_total'));
$monto_aprobado = array_sum(array_map(function($p) {
    return $p['estado'] == 'APROBADA' ? $p['monto_solicitado'] : 0;
}, $postulaciones));

// Distribución por región
$dist_regional = [];
foreach ($postulaciones as $post) {
    $escuela = array_filter(obtenerEscuelas(), fn($e) => $e['id'] == $post['escuela_id']);
    if (!empty($escuela)) {
        $escuela = reset($escuela);
        $region = obtenerNombreRegion($escuela['region_id']);
        $dist_regional[$region] = ($dist_regional[$region] ?? 0) + 1;
    }
}
arsort($dist_regional);

// Tasa de aprobación
$tasa_aprobacion = $total_postulaciones > 0 ? ($post_aprobadas / $total_postulaciones) * 100 : 0;

// Promedio de puntajes
$puntajes_evaluaciones = array_filter(array_column($evaluaciones, 'puntaje_total'));
$promedio_puntajes = !empty($puntajes_evaluaciones) ? array_sum($puntajes_evaluaciones) / count($puntajes_evaluaciones) : 0;
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Estadísticas</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">📊 Estadísticas del Sistema</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Vista consolidada de métricas e indicadores del sistema FONDEP
        </p>
    </div>

    <!-- Alerta de integración -->
    <div class="alert alert-info" style="margin-bottom: 2rem;">
        <strong>🔗 Datos Integrados:</strong> Esta vista consolida información de 
        <span class="badge badge-info">PostgreSQL</span>
        <span class="badge badge-success">MongoDB</span>
        <span class="badge badge-warning">Cassandra</span>
    </div>

    <!-- KPIs Principales -->
    <div class="grid" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total_convocatorias; ?></div>
                <div class="stat-label">Convocatorias</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $total_postulaciones; ?></div>
                <div class="stat-label">Postulaciones</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo number_format($tasa_aprobacion, 1); ?>%</div>
                <div class="stat-label">Tasa Aprobación</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="stat-number"><?php echo number_format($promedio_puntajes, 1); ?></div>
                <div class="stat-label">Promedio Puntajes</div>
            </div>
        </div>
    </div>

    <div class="grid">
        
        <!-- Columna Izquierda -->
        <div class="col-6">
            
            <!-- Presupuestos -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">💰 Análisis Presupuestal</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                            Presupuesto Total Disponible
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                            <?php echo formatearMonto($presupuesto_total); ?>
                        </div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                            Monto Aprobado
                        </div>
                        <div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">
                            <?php echo formatearMonto($monto_aprobado); ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                            % Ejecutado
                        </div>
                        <div style="background-color: #e5e7eb; height: 30px; border-radius: 15px; overflow: hidden;">
                            <?php $porcentaje_ejecutado = ($monto_aprobado / $presupuesto_total) * 100; ?>
                            <div style="background: linear-gradient(90deg, #10b981, #059669); 
                                        height: 100%; 
                                        width: <?php echo $porcentaje_ejecutado; ?>%;
                                        display: flex;
                                        align-items: center;
                                        justify-content: center;
                                        color: white;
                                        font-weight: 600;">
                                <?php echo number_format($porcentaje_ejecutado, 1); ?>%
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estado de Postulaciones -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📈 Estado de Postulaciones</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>
                                    <span class="badge badge-success">APROBADA</span>
                                </td>
                                <td><strong><?php echo $post_aprobadas; ?></strong></td>
                                <td><?php echo number_format(($post_aprobadas / $total_postulaciones) * 100, 1); ?>%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge badge-danger">RECHAZADA</span>
                                </td>
                                <td><strong><?php echo $post_rechazadas; ?></strong></td>
                                <td><?php echo number_format(($post_rechazadas / $total_postulaciones) * 100, 1); ?>%</td>
                            </tr>
                            <tr>
                                <td>
                                    <span class="badge badge-warning">EN EVALUACIÓN</span>
                                </td>
                                <td><strong><?php echo $post_en_evaluacion; ?></strong></td>
                                <td><?php echo number_format(($post_en_evaluacion / $total_postulaciones) * 100, 1); ?>%</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-6">
            
            <!-- Distribución Regional -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">🗺️ Distribución Regional</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php foreach (array_slice($dist_regional, 0, 8) as $region => $cantidad): ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span style="font-weight: 500;"><?php echo htmlspecialchars($region); ?></span>
                                <span style="color: var(--primary-color); font-weight: 600;">
                                    <?php echo $cantidad; ?> (<?php echo number_format(($cantidad / $total_postulaciones) * 100, 1); ?>%)
                                </span>
                            </div>
                            <div style="background-color: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, #3b82f6, #2563eb); 
                                            height: 100%; 
                                            width: <?php echo ($cantidad / $total_postulaciones) * 100; ?>%;">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Convocatorias por Estado -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📢 Convocatorias por Estado</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php
                    $conv_abiertas = count(array_filter($convocatorias, fn($c) => $c['estado'] == 'ABIERTA'));
                    $conv_cerradas = count(array_filter($convocatorias, fn($c) => $c['estado'] == 'CERRADA'));
                    $conv_evaluacion = count(array_filter($convocatorias, fn($c) => $c['estado'] == 'EN_EVALUACION'));
                    $conv_planificadas = count(array_filter($convocatorias, fn($c) => $c['estado'] == 'PLANIFICADA'));
                    ?>
                    <div class="grid" style="gap: 1rem;">
                        <div class="col-6" style="text-align: center; padding: 1rem; background-color: #d1fae5; border-radius: 8px;">
                            <div style="font-size: 2rem; font-weight: 700; color: #065f46;">
                                <?php echo $conv_abiertas; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #047857;">Abiertas</div>
                        </div>
                        <div class="col-6" style="text-align: center; padding: 1rem; background-color: #fee2e2; border-radius: 8px;">
                            <div style="font-size: 2rem; font-weight: 700; color: #991b1b;">
                                <?php echo $conv_cerradas; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #b91c1c;">Cerradas</div>
                        </div>
                        <div class="col-6" style="text-align: center; padding: 1rem; background-color: #fef3c7; border-radius: 8px;">
                            <div style="font-size: 2rem; font-weight: 700; color: #92400e;">
                                <?php echo $conv_evaluacion; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #b45309;">En Evaluación</div>
                        </div>
                        <div class="col-6" style="text-align: center; padding: 1rem; background-color: #dbeafe; border-radius: 8px;">
                            <div style="font-size: 2rem; font-weight: 700; color: #1e40af;">
                                <?php echo $conv_planificadas; ?>
                            </div>
                            <div style="font-size: 0.875rem; color: #1d4ed8;">Planificadas</div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

    <!-- Resumen de Evaluaciones -->
    <div class="card" style="margin-top: 2rem;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1.125rem;">⭐ Análisis de Evaluaciones (MongoDB)</h3>
        </div>
        <div style="padding: 1.5rem;">
            <div class="grid">
                <div class="col-3" style="text-align: center;">
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                        Total Evaluaciones
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--primary-color);">
                        <?php echo $total_evaluaciones; ?>
                    </div>
                </div>
                <div class="col-3" style="text-align: center;">
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                        Completadas
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--success-color);">
                        <?php echo count(array_filter($evaluaciones, fn($e) => $e['estado'] == 'COMPLETADA')); ?>
                    </div>
                </div>
                <div class="col-3" style="text-align: center;">
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                        Promedio General
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: var(--warning-color);">
                        <?php echo number_format($promedio_puntajes, 1); ?>
                    </div>
                </div>
                <div class="col-3" style="text-align: center;">
                    <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                        Pendientes
                    </div>
                    <div style="font-size: 2rem; font-weight: 700; color: #6b7280;">
                        <?php echo count(array_filter($evaluaciones, fn($e) => $e['estado'] == 'ASIGNADA')); ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Información de queries -->
    <div class="alert alert-info" style="margin-top: 2rem;">
        <strong>🔍 Consultas Ejecutadas:</strong>
        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
            <li><strong>PostgreSQL:</strong> Agregaciones con GROUP BY, SUM(), COUNT(), AVG()</li>
            <li><strong>MongoDB:</strong> Pipeline de agregación con $match, $group, $avg</li>
            <li><strong>Cassandra:</strong> Queries de series temporales para métricas históricas</li>
        </ul>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>