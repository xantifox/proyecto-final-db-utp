<?php
/**
 * MÓDULO: EVALUACIONES - RESULTADOS
 * Resultados consolidados de evaluaciones por convocatoria
 * 
 * Base de datos:
 * - PostgreSQL: Postulaciones y evaluaciones con JOINs
 * - MongoDB: Análisis agregado de evaluaciones
 */

$page_title = "Resultados de Evaluaciones";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener convocatorias y filtro
$convocatorias = obtenerConvocatorias();
$convocatoria_seleccionada = $_GET['convocatoria_id'] ?? null;

// Si no hay selección, usar la primera convocatoria cerrada o en evaluación
if (!$convocatoria_seleccionada) {
    $conv_validas = array_filter($convocatorias, fn($c) => in_array($c['estado'], ['CERRADA', 'EN_EVALUACION']));
    if (!empty($conv_validas)) {
        $conv = reset($conv_validas);
        $convocatoria_seleccionada = $conv['id'];
    }
}

$convocatoria = obtenerConvocatoriaPorId($convocatoria_seleccionada);

// Obtener postulaciones de la convocatoria
$postulaciones = array_filter(obtenerPostulaciones(), fn($p) => $p['convocatoria_id'] == $convocatoria_seleccionada);

// Obtener evaluaciones
$todas_evaluaciones = obtenerEvaluaciones();

// Combinar datos
$resultados = [];
foreach ($postulaciones as $post) {
    $evaluacion = array_filter($todas_evaluaciones, fn($e) => $e['postulacion_id'] == $post['id']);
    $evaluacion = !empty($evaluacion) ? reset($evaluacion) : null;
    
    $escuela = array_filter(obtenerEscuelas(), fn($e) => $e['id'] == $post['escuela_id']);
    $escuela = !empty($escuela) ? reset($escuela) : null;
    
    $resultados[] = [
        'postulacion' => $post,
        'evaluacion' => $evaluacion,
        'escuela' => $escuela
    ];
}

// Ordenar por puntaje descendente
usort($resultados, function($a, $b) {
    $puntaje_a = $a['evaluacion']['puntaje_total'] ?? 0;
    $puntaje_b = $b['evaluacion']['puntaje_total'] ?? 0;
    return $puntaje_b <=> $puntaje_a;
});

// Estadísticas
$total_postulaciones = count($postulaciones);
$evaluadas = count(array_filter($resultados, fn($r) => $r['evaluacion'] && $r['evaluacion']['estado'] == 'COMPLETADA'));
$pendientes = $total_postulaciones - $evaluadas;
$aprobadas = count(array_filter($resultados, fn($r) => $r['postulacion']['estado'] == 'APROBADA'));

// Promedio de puntajes
$puntajes = array_filter(array_map(fn($r) => $r['evaluacion']['puntaje_total'] ?? null, $resultados));
$promedio_puntajes = !empty($puntajes) ? array_sum($puntajes) / count($puntajes) : 0;
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Evaluaciones</a> / 
        <span>Resultados</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">📊 Resultados de Evaluaciones</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Consolidado de evaluaciones y ranking de proyectos
        </p>
    </div>

    <!-- Selector de Convocatoria -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1.5rem;">
            <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end;">
                <div style="flex: 1;">
                    <label class="form-label">Seleccione Convocatoria</label>
                    <select name="convocatoria_id" class="form-control" onchange="this.form.submit()">
                        <?php foreach ($convocatorias as $conv): ?>
                            <?php if (in_array($conv['estado'], ['CERRADA', 'EN_EVALUACION'])): ?>
                                <option value="<?php echo $conv['id']; ?>"
                                        <?php echo $convocatoria_seleccionada == $conv['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($conv['codigo'] . ' - ' . $conv['titulo']); ?>
                                    (<?php echo $conv['estado']; ?>)
                                </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>
            </form>
        </div>
    </div>

    <?php if ($convocatoria): ?>

        <!-- Información de Convocatoria -->
        <div class="card" style="margin-bottom: 1.5rem; background: linear-gradient(135deg, #eff6ff, #ffffff); border: 2px solid #3b82f6;">
            <div style="padding: 1.5rem;">
                <h3 style="margin: 0 0 1rem 0; color: var(--primary-color);">
                    <?php echo htmlspecialchars($convocatoria['titulo']); ?>
                </h3>
                <div class="row">
                    <div class="col-3">
                        <div style="font-size: 0.875rem; color: #6b7280;">Presupuesto</div>
                        <div style="font-size: 1.25rem; font-weight: 700; color: var(--success-color);">
                            <?php echo formatearMonto($convocatoria['presupuesto_total']); ?>
                        </div>
                    </div>
                    <div class="col-3">
                        <div style="font-size: 0.875rem; color: #6b7280;">Estado</div>
                        <span class="badge <?php echo obtenerClaseBadge($convocatoria['estado']); ?>" style="font-size: 1rem;">
                            <?php echo $convocatoria['estado']; ?>
                        </span>
                    </div>
                    <div class="col-6">
                        <div style="font-size: 0.875rem; color: #6b7280;">Periodo</div>
                        <div style="font-weight: 600;">
                            <?php echo formatearFecha($convocatoria['fecha_inicio']); ?> - 
                            <?php echo formatearFecha($convocatoria['fecha_fin']); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Estadísticas de Evaluación -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                    <div class="stat-number"><?php echo $total_postulaciones; ?></div>
                    <div class="stat-label">Total Postulaciones</div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                    <div class="stat-number"><?php echo $evaluadas; ?></div>
                    <div class="stat-label">Evaluadas</div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                    <div class="stat-number"><?php echo $aprobadas; ?></div>
                    <div class="stat-label">Aprobadas</div>
                </div>
            </div>
            <div class="col-3">
                <div class="stat-card" style="background: linear-gradient(135deg, #8b5cf6, #7c3aed);">
                    <div class="stat-number"><?php echo number_format($promedio_puntajes, 1); ?></div>
                    <div class="stat-label">Promedio Puntajes</div>
                </div>
            </div>
        </div>

        <!-- Ranking de Postulaciones -->
        <div class="card">
            <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                <h3 style="margin: 0; font-size: 1.125rem;">🏆 Ranking de Proyectos</h3>
            </div>
            
            <div style="overflow-x: auto;">
                <table class="table">
                    <thead>
                        <tr>
                            <th style="width: 60px; text-align: center;">Pos.</th>
                            <th>Código</th>
                            <th>Proyecto</th>
                            <th>Escuela</th>
                            <th>Pertinencia</th>
                            <th>Viabilidad</th>
                            <th>Impacto</th>
                            <th>Sostenibilidad</th>
                            <th>Puntaje Final</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($resultados)): ?>
                            <tr>
                                <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">
                                    No hay postulaciones para esta convocatoria
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($resultados as $index => $resultado): ?>
                                <?php 
                                $post = $resultado['postulacion'];
                                $eval = $resultado['evaluacion'];
                                $escuela = $resultado['escuela'];
                                $posicion = $index + 1;
                                ?>
                                <tr style="<?php echo $posicion <= 3 && $eval && $eval['puntaje_total'] ? 'background-color: #fef3c7;' : ''; ?>">
                                    <td style="text-align: center;">
                                        <?php if ($posicion == 1 && $eval && $eval['puntaje_total']): ?>
                                            <span style="font-size: 1.5rem;">🥇</span>
                                        <?php elseif ($posicion == 2 && $eval && $eval['puntaje_total']): ?>
                                            <span style="font-size: 1.5rem;">🥈</span>
                                        <?php elseif ($posicion == 3 && $eval && $eval['puntaje_total']): ?>
                                            <span style="font-size: 1.5rem;">🥉</span>
                                        <?php else: ?>
                                            <strong><?php echo $posicion; ?></strong>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <a href="../postulaciones/detalle.php?codigo=<?php echo $post['codigo']; ?>"
                                           style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                            <?php echo htmlspecialchars($post['codigo']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                        </div>
                                    </td>
                                    <td style="font-size: 0.875rem;">
                                        <?php echo htmlspecialchars($escuela['nombre'] ?? 'N/A'); ?>
                                    </td>
                                    
                                    <?php if ($eval && $eval['estado'] == 'COMPLETADA'): ?>
                                        <td style="text-align: center;">
                                            <strong style="color: var(--primary-color);">
                                                <?php echo $eval['puntaje_pertinencia']; ?>
                                            </strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <strong style="color: var(--primary-color);">
                                                <?php echo $eval['puntaje_viabilidad']; ?>
                                            </strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <strong style="color: var(--primary-color);">
                                                <?php echo $eval['puntaje_impacto']; ?>
                                            </strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <strong style="color: var(--primary-color);">
                                                <?php echo $eval['puntaje_sostenibilidad']; ?>
                                            </strong>
                                        </td>
                                        <td style="text-align: center;">
                                            <div style="font-size: 1.25rem; font-weight: 700; color: var(--success-color);">
                                                <?php echo number_format($eval['puntaje_total'], 1); ?>
                                            </div>
                                        </td>
                                    <?php else: ?>
                                        <td colspan="5" style="text-align: center; color: #6b7280;">
                                            <em>Evaluación pendiente</em>
                                        </td>
                                    <?php endif; ?>
                                    
                                    <td>
                                        <span class="badge <?php echo obtenerClaseBadge($post['estado']); ?>">
                                            <?php echo str_replace('_', ' ', $post['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Análisis Adicional -->
        <?php if ($evaluadas > 0): ?>
            <div class="row" style="margin-top: 2rem; gap: 1rem;">
                <div class="col-6">
                    <div class="card">
                        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                            <h3 style="margin: 0; font-size: 1rem;">📈 Distribución de Puntajes</h3>
                        </div>
                        <div style="padding: 1.5rem;">
                            <?php
                            $rango_90_100 = count(array_filter($puntajes, fn($p) => $p >= 90));
                            $rango_75_89 = count(array_filter($puntajes, fn($p) => $p >= 75 && $p < 90));
                            $rango_60_74 = count(array_filter($puntajes, fn($p) => $p >= 60 && $p < 75));
                            $rango_0_59 = count(array_filter($puntajes, fn($p) => $p < 60));
                            ?>
                            <table class="table">
                                <tbody>
                                    <tr>
                                        <td><strong>Excelente (90-100)</strong></td>
                                        <td style="text-align: right;"><strong style="color: var(--success-color);"><?php echo $rango_90_100; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Muy Bueno (75-89)</strong></td>
                                        <td style="text-align: right;"><strong style="color: var(--primary-color);"><?php echo $rango_75_89; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Bueno (60-74)</strong></td>
                                        <td style="text-align: right;"><strong style="color: var(--warning-color);"><?php echo $rango_60_74; ?></strong></td>
                                    </tr>
                                    <tr>
                                        <td><strong>Insuficiente (0-59)</strong></td>
                                        <td style="text-align: right;"><strong style="color: var(--danger-color);"><?php echo $rango_0_59; ?></strong></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="alert alert-info">
                        <strong>📊 Integración de Datos:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                            <li><strong>PostgreSQL:</strong> JOIN de postulaciones, evaluaciones y escuelas</li>
                            <li><strong>MongoDB:</strong> Análisis agregado de comentarios de evaluadores</li>
                            <li><strong>Query:</strong> ORDER BY puntaje_total DESC para ranking</li>
                        </ul>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php else: ?>
        <div class="alert alert-warning">
            <strong>⚠️ No hay convocatorias disponibles</strong><br>
            No existen convocatorias en estado CERRADA o EN_EVALUACION para mostrar resultados.
        </div>
    <?php endif; ?>

</div>

<?php require_once '../../includes/footer.php'; ?>