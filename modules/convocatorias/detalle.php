<?php
/**
 * MÓDULO: CONVOCATORIAS - DETALLE
 * Vista completa de una convocatoria con sus postulaciones
 * 
 * Integración de datos:
 * - PostgreSQL: Datos de convocatoria y postulaciones
 * - MongoDB: Propuestas completas
 * - Cassandra: Eventos relacionados
 */

$page_title = "Detalle de Convocatoria";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener ID de convocatoria
$convocatoria_id = $_GET['id'] ?? 1;

// Buscar convocatoria
$convocatoria = obtenerConvocatoriaPorId($convocatoria_id);

if (!$convocatoria) {
    echo "<div class='container'><div class='alert alert-danger'>Convocatoria no encontrada</div></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Obtener postulaciones de esta convocatoria
$postulaciones = array_filter(obtenerPostulaciones(), function($p) use ($convocatoria_id) {
    return $p['convocatoria_id'] == $convocatoria_id;
});

// Calcular estadísticas
$total_postulaciones = count($postulaciones);
$aprobadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'APROBADA'));
$rechazadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'RECHAZADA'));
$en_evaluacion = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_EVALUACION'));

// Distribución por región
$regiones_dist = [];
foreach ($postulaciones as $post) {
    $escuela_id = $post['escuela_id'];
    $escuelas = obtenerEscuelas();
    $escuela = array_filter($escuelas, fn($e) => $e['id'] == $escuela_id);
    if (!empty($escuela)) {
        $escuela = reset($escuela);
        $region = obtenerNombreRegion($escuela['region_id']);
        $regiones_dist[$region] = ($regiones_dist[$region] ?? 0) + 1;
    }
}
arsort($regiones_dist);
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Convocatorias</a> / 
        <span><?php echo htmlspecialchars($convocatoria['codigo']); ?></span>
    </div>

    <!-- Header con acciones -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h1 style="margin: 0; color: var(--dark-color);">
                    <?php echo htmlspecialchars($convocatoria['titulo']); ?>
                </h1>
                <span class="badge <?php echo obtenerClaseBadge($convocatoria['estado']); ?>" 
                      style="font-size: 0.875rem;">
                    <?php echo str_replace('_', ' ', $convocatoria['estado']); ?>
                </span>
            </div>
            <p style="margin: 0; color: #6b7280; font-size: 1.125rem;">
                <?php echo htmlspecialchars($convocatoria['codigo']); ?>
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="editar.php?id=<?php echo $convocatoria['id']; ?>" class="btn btn-warning">
                ✏️ Editar
            </a>
            <a href="listar.php" class="btn btn-secondary">
                ← Volver
            </a>
        </div>
    </div>

    <div class="grid">
        <!-- Información Principal -->
        <div class="col-8">
            
            <!-- Descripción -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📄 Descripción</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="margin: 0; line-height: 1.8; color: #374151;">
                        <?php echo htmlspecialchars($convocatoria['descripcion']); ?>
                    </p>
                </div>
            </div>

            <!-- Requisitos -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📋 Requisitos</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="margin: 0; color: #374151;">
                        <?php echo htmlspecialchars($convocatoria['requisitos']); ?>
                    </p>
                    <div style="margin-top: 1rem; padding: 1rem; background-color: #eff6ff; border-left: 4px solid var(--primary-color); border-radius: 4px;">
                        <strong>Áreas Temáticas:</strong> 
                        <?php echo htmlspecialchars($convocatoria['areas_tematicas']); ?>
                    </div>
                </div>
            </div>

            <!-- Postulaciones -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">
                        📝 Postulaciones Recibidas (<?php echo $total_postulaciones; ?>)
                    </h3>
                </div>
                
                <?php if (empty($postulaciones)): ?>
                    <div style="padding: 2rem; text-align: center; color: #6b7280;">
                        No se han recibido postulaciones aún
                    </div>
                <?php else: ?>
                    <div style="overflow-x: auto;">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Código</th>
                                    <th>Escuela</th>
                                    <th>Proyecto</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                    <th>Puntaje</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($postulaciones as $post): ?>
                                    <tr>
                                        <td>
                                            <a href="../postulaciones/detalle.php?codigo=<?php echo $post['codigo']; ?>"
                                               style="color: var(--primary-color); text-decoration: none; font-weight: 500;">
                                                <?php echo htmlspecialchars($post['codigo']); ?>
                                            </a>
                                        </td>
                                        <td><?php echo htmlspecialchars(obtenerNombreEscuela($post['escuela_id'])); ?></td>
                                        <td>
                                            <div style="max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                                <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                            </div>
                                        </td>
                                        <td><?php echo formatearMonto($post['monto_solicitado']); ?></td>
                                        <td>
                                            <span class="badge <?php echo obtenerClaseBadge($post['estado']); ?>">
                                                <?php echo str_replace('_', ' ', $post['estado']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php if ($post['puntaje_final']): ?>
                                                <strong style="color: var(--success-color);">
                                                    <?php echo number_format($post['puntaje_final'], 1); ?>
                                                </strong>
                                            <?php else: ?>
                                                <span style="color: #9ca3af;">-</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>

        </div>

        <!-- Panel Lateral -->
        <div class="col-4">
            
            <!-- Información General -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">ℹ️ Información General</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: #6b7280;">Fecha de Inicio</div>
                        <div style="font-weight: 600; color: var(--dark-color);">
                            <?php echo formatearFecha($convocatoria['fecha_inicio']); ?>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: #6b7280;">Fecha de Cierre</div>
                        <div style="font-weight: 600; color: var(--dark-color);">
                            <?php echo formatearFecha($convocatoria['fecha_fin']); ?>
                        </div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: #6b7280;">Presupuesto Total</div>
                        <div style="font-weight: 600; color: var(--success-color); font-size: 1.25rem;">
                            <?php echo formatearMonto($convocatoria['presupuesto_total']); ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280;">Monto Máximo/Proyecto</div>
                        <div style="font-weight: 600; color: var(--dark-color);">
                            <?php echo formatearMonto($convocatoria['monto_max_proyecto']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">📊 Estadísticas</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="margin-bottom: 1rem;">
                        <div style="font-size: 0.875rem; color: #6b7280;">Total Postulaciones</div>
                        <div style="font-weight: 700; color: var(--primary-color); font-size: 1.5rem;">
                            <?php echo $total_postulaciones; ?>
                        </div>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem;">
                        <div style="text-align: center; padding: 0.75rem; background-color: #d1fae5; border-radius: 6px;">
                            <div style="font-size: 1.25rem; font-weight: 700; color: #065f46;">
                                <?php echo $aprobadas; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #047857;">Aprobadas</div>
                        </div>
                        <div style="text-align: center; padding: 0.75rem; background-color: #fee2e2; border-radius: 6px;">
                            <div style="font-size: 1.25rem; font-weight: 700; color: #991b1b;">
                                <?php echo $rechazadas; ?>
                            </div>
                            <div style="font-size: 0.75rem; color: #b91c1c;">Rechazadas</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Distribución Regional -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">🗺️ Distribución Regional</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php if (empty($regiones_dist)): ?>
                        <p style="color: #6b7280; font-size: 0.875rem; margin: 0;">
                            No hay datos disponibles
                        </p>
                    <?php else: ?>
                        <?php foreach (array_slice($regiones_dist, 0, 5) as $region => $cantidad): ?>
                            <div style="margin-bottom: 0.75rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                                    <span style="font-size: 0.875rem;"><?php echo htmlspecialchars($region); ?></span>
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
                    <?php endif; ?>
                </div>
            </div>

        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>