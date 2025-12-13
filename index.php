<?php
/**
 * DASHBOARD PRINCIPAL - Sistema FONDEP
 * Página de inicio con resumen ejecutivo del sistema
 * 
 * Integración de datos:
 * - PostgreSQL: Estadísticas de convocatorias y postulaciones
 * - MongoDB: Total de propuestas y evaluaciones
 * - Cassandra: Eventos del sistema
 */

$page_title = "Dashboard";
require_once 'includes/header.php';
require_once 'includes/datos_simulados.php';

// Obtener datos consolidados
$convocatorias = obtenerConvocatorias();
$postulaciones = obtenerPostulaciones();
$evaluaciones = obtenerEvaluaciones();
$regiones = obtenerRegiones();

// Estadísticas PostgreSQL
$total_convocatorias = count($convocatorias);
$total_postulaciones = count($postulaciones);
$conv_abiertas = count(array_filter($convocatorias, fn($c) => $c['estado'] == 'ABIERTA'));
$post_aprobadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'APROBADA'));

// Estadísticas MongoDB
$total_evaluaciones = count($evaluaciones);
$evaluaciones_completadas = count(array_filter($evaluaciones, fn($e) => $e['estado'] == 'COMPLETADA'));

// Estadísticas Cassandra
$total_eventos = 1247; // Simulado
$eventos_hoy = 83;

// Postulaciones por estado
$estados_post = [
    'APROBADA' => count(array_filter($postulaciones, fn($p) => $p['estado'] == 'APROBADA')),
    'EN_EVALUACION' => count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_EVALUACION')),
    'EN_REVISION' => count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_REVISION')),
    'RECHAZADA' => count(array_filter($postulaciones, fn($p) => $p['estado'] == 'RECHAZADA')),
    'OBSERVADA' => count(array_filter($postulaciones, fn($p) => $p['estado'] == 'OBSERVADA'))
];

// Distribución regional
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

// Convocatorias abiertas
$convocatorias_abiertas = array_filter($convocatorias, fn($c) => $c['estado'] == 'ABIERTA');
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">📊 Dashboard - Sistema FONDEP</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Panel de control y estadísticas del sistema de gestión de proyectos de innovación educativa
        </p>
    </div>

    <!-- Alerta de Modo Prueba -->
    <div class="alert alert-info" style="margin-bottom: 2rem;">
        <strong>🔍 Modo de Prueba:</strong> El sistema está funcionando con datos simulados. 
        Las conexiones a PostgreSQL, MongoDB y Cassandra están preparadas para activarse.
    </div>

    <!-- KPIs Principales -->
    <div class="grid" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total_convocatorias; ?></div>
                <div class="stat-label">Convocatorias</div>
                <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                    <?php echo $conv_abiertas; ?> abiertas
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $total_postulaciones; ?></div>
                <div class="stat-label">Postulaciones</div>
                <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                    <?php echo $post_aprobadas; ?> aprobadas
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo $total_evaluaciones; ?></div>
                <div class="stat-label">Evaluaciones</div>
                <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                    <?php echo $evaluaciones_completadas; ?> completadas
                </div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                <div class="stat-number"><?php echo number_format($total_eventos); ?></div>
                <div class="stat-label">Eventos del Sistema</div>
                <div style="margin-top: 0.5rem; font-size: 0.875rem;">
                    Hoy: <?php echo $eventos_hoy; ?> eventos
                </div>
            </div>
        </div>
    </div>

    <div class="grid">
        
        <!-- Columna Izquierda -->
        <div class="col-8">
            
            <!-- Convocatorias Abiertas -->
            <?php if (!empty($convocatorias_abiertas)): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background: linear-gradient(135deg, #eff6ff, #ffffff);">
                        <h3 style="margin: 0; font-size: 1.125rem; color: var(--primary-color);">
                            📢 Convocatorias Abiertas
                        </h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        <?php foreach ($convocatorias_abiertas as $conv): ?>
                            <div style="margin-bottom: 1.5rem; padding: 1rem; background-color: #f9fafb; border-left: 4px solid var(--primary-color); border-radius: 6px;">
                                <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                                    <h4 style="margin: 0; color: var(--dark-color);">
                                        <?php echo htmlspecialchars($conv['titulo']); ?>
                                    </h4>
                                    <span class="badge badge-success">ABIERTA</span>
                                </div>
                                <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($conv['descripcion']); ?>
                                </p>
                                <div class="grid" style="margin-top: 1rem;">
                                    <div class="col-4">
                                        <div style="font-size: 0.875rem; color: #6b7280;">Código</div>
                                        <div style="font-weight: 600;"><?php echo htmlspecialchars($conv['codigo']); ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div style="font-size: 0.875rem; color: #6b7280;">Cierre</div>
                                        <div style="font-weight: 600;"><?php echo formatearFecha($conv['fecha_fin']); ?></div>
                                    </div>
                                    <div class="col-4">
                                        <div style="font-size: 0.875rem; color: #6b7280;">Presupuesto</div>
                                        <div style="font-weight: 600; color: var(--success-color);">
                                            <?php echo formatearMonto($conv['presupuesto_total']); ?>
                                        </div>
                                    </div>
                                </div>
                                <div style="margin-top: 1rem;">
                                    <a href="modules/convocatorias/detalle.php?id=<?php echo $conv['id']; ?>" 
                                       class="btn btn-primary btn-sm">
                                        Ver Detalle
                                    </a>
                                    <a href="modules/postulaciones/crear.php?convocatoria_id=<?php echo $conv['id']; ?>" 
                                       class="btn btn-success btn-sm">
                                        Postular Proyecto
                                    </a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Postulaciones por Estado -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📝 Postulaciones por Estado</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Estado</th>
                                <th>Cantidad</th>
                                <th>Porcentaje</th>
                                <th style="width: 40%;">Distribución</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($estados_post as $estado => $cantidad): ?>
                                <?php if ($cantidad > 0): ?>
                                    <tr>
                                        <td>
                                            <span class="badge <?php echo obtenerClaseBadge($estado); ?>">
                                                <?php echo str_replace('_', ' ', $estado); ?>
                                            </span>
                                        </td>
                                        <td><strong><?php echo $cantidad; ?></strong></td>
                                        <td><?php echo number_format(($cantidad / $total_postulaciones) * 100, 1); ?>%</td>
                                        <td>
                                            <div style="background-color: #e5e7eb; height: 20px; border-radius: 10px; overflow: hidden;">
                                                <div style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); 
                                                            height: 100%; 
                                                            width: <?php echo ($cantidad / $total_postulaciones) * 100; ?>%;
                                                            display: flex;
                                                            align-items: center;
                                                            justify-content: flex-end;
                                                            padding-right: 0.5rem;
                                                            color: white;
                                                            font-size: 0.75rem;
                                                            font-weight: 600;">
                                                    <?php echo $cantidad; ?>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                <div style="padding: 1rem; background-color: #f9fafb; text-align: center;">
                    <a href="modules/postulaciones/listar.php" class="btn btn-primary">
                        Ver Todas las Postulaciones
                    </a>
                </div>
            </div>

        </div>

        <!-- Columna Derecha -->
        <div class="col-4">
            
            <!-- Top 5 Regiones -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">🗺️ Top 5 Regiones</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php $count = 0; ?>
                    <?php foreach (array_slice($dist_regional, 0, 5) as $region => $cantidad): ?>
                        <?php $count++; ?>
                        <div style="margin-bottom: 1rem;">
                            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.5rem;">
                                <div style="display: flex; align-items: center; gap: 0.5rem;">
                                    <div style="width: 24px; height: 24px; background-color: var(--primary-color); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; font-size: 0.75rem;">
                                        <?php echo $count; ?>
                                    </div>
                                    <span style="font-weight: 500;"><?php echo htmlspecialchars($region); ?></span>
                                </div>
                                <strong style="color: var(--primary-color);"><?php echo $cantidad; ?></strong>
                            </div>
                            <div style="background-color: #e5e7eb; height: 6px; border-radius: 3px; overflow: hidden;">
                                <div style="background: linear-gradient(90deg, var(--primary-color), var(--secondary-color)); 
                                            height: 100%; 
                                            width: <?php echo ($cantidad / $total_postulaciones) * 100; ?>%;">
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border-color); text-align: center;">
                        <a href="modules/reportes/estadisticas.php" style="color: var(--primary-color); text-decoration: none; font-size: 0.875rem;">
                            Ver estadísticas completas →
                        </a>
                    </div>
                </div>
            </div>

            <!-- Accesos Rápidos -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">⚡ Accesos Rápidos</h3>
                </div>
                <div style="padding: 1rem;">
                    <a href="modules/convocatorias/crear.php" 
                       class="btn btn-primary" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        ➕ Nueva Convocatoria
                    </a>
                    <a href="modules/postulaciones/crear.php" 
                       class="btn btn-success" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        📝 Nueva Postulación
                    </a>
                    <a href="modules/evaluaciones/listar.php" 
                       class="btn btn-warning" 
                       style="width: 100%; margin-bottom: 0.5rem;">
                        ⭐ Mis Evaluaciones
                    </a>
                    <a href="modules/reportes/auditoria.php" 
                       class="btn btn-info" 
                       style="width: 100%;">
                        🔍 Ver Auditoría
                    </a>
                </div>
            </div>

            <!-- Estado de Bases de Datos -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">💾 Bases de Datos</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="padding: 1rem; background-color: #dbeafe; border-radius: 6px; margin-bottom: 0.75rem;">
                        <div style="font-weight: 600; color: #1e40af; margin-bottom: 0.25rem;">PostgreSQL</div>
                        <div style="font-size: 0.75rem; color: #1e40af;">
                            <strong>Estado:</strong> <span class="badge badge-warning" style="font-size: 0.7rem;">Simulado</span><br>
                            <strong>Tablas:</strong> 10 tablas<br>
                            <strong>Datos:</strong> <?php echo $total_postulaciones; ?> registros
                        </div>
                    </div>
                    <div style="padding: 1rem; background-color: #d1fae5; border-radius: 6px; margin-bottom: 0.75rem;">
                        <div style="font-weight: 600; color: #065f46; margin-bottom: 0.25rem;">MongoDB</div>
                        <div style="font-size: 0.75rem; color: #065f46;">
                            <strong>Estado:</strong> <span class="badge badge-warning" style="font-size: 0.7rem;">Simulado</span><br>
                            <strong>Colecciones:</strong> 4 colecciones<br>
                            <strong>Documentos:</strong> <?php echo $total_postulaciones; ?> docs
                        </div>
                    </div>
                    <div style="padding: 1rem; background-color: #fee2e2; border-radius: 6px;">
                        <div style="font-weight: 600; color: #991b1b; margin-bottom: 0.25rem;">Cassandra</div>
                        <div style="font-size: 0.75rem; color: #991b1b;">
                            <strong>Estado:</strong> <span class="badge badge-warning" style="font-size: 0.7rem;">Simulado</span><br>
                            <strong>Keyspace:</strong> fondep_analytics<br>
                            <strong>Eventos:</strong> <?php echo number_format($total_eventos); ?> eventos
                        </div>
                    </div>
                </div>
            </div>

        </div>

    </div>

</div>

<?php require_once 'includes/footer.php'; ?>