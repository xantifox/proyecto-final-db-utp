<?php
/**
 * MÓDULO: EVALUACIONES - LISTAR
 * Muestra las postulaciones asignadas al evaluador para calificar
 * 
 * Base de datos:
 * - PostgreSQL: evaluaciones, postulaciones
 * - MongoDB: propuestas completas para evaluar
 */

$page_title = "Mis Evaluaciones";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Simular evaluador logueado (ID = 1)
$evaluador_id = 1;
$evaluador = array_filter(obtenerEvaluadores(), fn($e) => $e['id'] == $evaluador_id);
$evaluador = reset($evaluador);

// Obtener evaluaciones del evaluador
$todas_evaluaciones = obtenerEvaluaciones();
$mis_evaluaciones = array_filter($todas_evaluaciones, fn($e) => $e['evaluador_id'] == $evaluador_id);

// Obtener postulaciones correspondientes
$postulaciones = obtenerPostulaciones();
$postulaciones_para_evaluar = [];

foreach ($mis_evaluaciones as $eval) {
    $post = array_filter($postulaciones, fn($p) => $p['id'] == $eval['postulacion_id']);
    if (!empty($post)) {
        $post = reset($post);
        $post['evaluacion'] = $eval;
        $postulaciones_para_evaluar[] = $post;
    }
}

// Estadísticas
$total = count($postulaciones_para_evaluar);
$completadas = count(array_filter($mis_evaluaciones, fn($e) => $e['estado'] == 'COMPLETADA'));
$pendientes = count(array_filter($mis_evaluaciones, fn($e) => $e['estado'] == 'ASIGNADA'));
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Evaluaciones</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">⭐ Mis Evaluaciones</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Bienvenido/a <strong><?php echo htmlspecialchars($evaluador['nombres'] . ' ' . $evaluador['apellidos']); ?></strong> 
            - Especialidad: <?php echo htmlspecialchars($evaluador['especialidad']); ?>
        </p>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="row" style="margin-bottom: 2rem;">
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total; ?></div>
                <div class="stat-label">Total Asignadas</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo $pendientes; ?></div>
                <div class="stat-label">Pendientes</div>
            </div>
        </div>
        <div class="col-4">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $completadas; ?></div>
                <div class="stat-label">Completadas</div>
            </div>
        </div>
    </div>

    <!-- Tabla de Evaluaciones -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1.125rem;">
                📝 Postulaciones Asignadas (<?php echo $total; ?>)
            </h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Proyecto</th>
                        <th>Escuela</th>
                        <th>Monto</th>
                        <th>Estado Evaluación</th>
                        <th>Puntaje</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulaciones_para_evaluar)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No tienes evaluaciones asignadas en este momento
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($postulaciones_para_evaluar as $post): ?>
                            <?php $eval = $post['evaluacion']; ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-color);">
                                        <?php echo htmlspecialchars($post['codigo']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis;">
                                        <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        Convocatoria: <?php 
                                            $conv = obtenerConvocatoriaPorId($post['convocatoria_id']);
                                            echo htmlspecialchars($conv['codigo']);
                                        ?>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars(obtenerNombreEscuela($post['escuela_id'])); ?></td>
                                <td><?php echo formatearMonto($post['monto_solicitado']); ?></td>
                                <td>
                                    <span class="badge <?php echo obtenerClaseBadge($eval['estado']); ?>">
                                        <?php echo $eval['estado'] == 'ASIGNADA' ? 'PENDIENTE' : 'COMPLETADA'; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($eval['puntaje_total']): ?>
                                        <strong style="color: var(--success-color); font-size: 1.125rem;">
                                            <?php echo number_format($eval['puntaje_total'], 1); ?>
                                        </strong>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($eval['estado'] == 'ASIGNADA'): ?>
                                        <a href="evaluar.php?id=<?php echo $eval['id']; ?>" 
                                           class="btn btn-sm btn-primary">
                                            ✏️ Evaluar
                                        </a>
                                    <?php else: ?>
                                        <a href="evaluar.php?id=<?php echo $eval['id']; ?>" 
                                           class="btn btn-sm btn-info">
                                            👁️ Ver Evaluación
                                        </a>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="row" style="margin-top: 1.5rem; gap: 1rem;">
        <div class="col-6">
            <div class="alert alert-info">
                <strong>ℹ️ Criterios de Evaluación:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                    <li>Pertinencia (0-100 puntos)</li>
                    <li>Viabilidad (0-100 puntos)</li>
                    <li>Impacto Esperado (0-100 puntos)</li>
                    <li>Sostenibilidad (0-100 puntos)</li>
                </ul>
            </div>
        </div>
        <div class="col-6">
            <div class="alert alert-warning">
                <strong>⚠️ Importante:</strong> Las evaluaciones completadas no pueden ser modificadas. El puntaje final se calcula como promedio de los 4 criterios.
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>