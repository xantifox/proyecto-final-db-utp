<?php
/**
 * MÓDULO: POSTULACIONES - LISTAR
 * Lista todas las postulaciones del sistema con filtros
 * 
 * Base de datos: PostgreSQL (tabla: postulaciones con JOINs)
 * Datos actuales: Simulados
 */

$page_title = "Postulaciones";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener datos
$postulaciones = obtenerPostulaciones();
$convocatorias = obtenerConvocatorias();
$escuelas = obtenerEscuelas();
$regiones = obtenerRegiones();

// Aplicar filtros
$filtro_codigo = $_GET['codigo'] ?? '';
$filtro_estado = $_GET['estado'] ?? '';
$filtro_region = $_GET['region'] ?? '';
$filtro_convocatoria = $_GET['convocatoria_id'] ?? '';

$postulaciones_filtradas = $postulaciones;

if ($filtro_codigo) {
    $postulaciones_filtradas = array_filter($postulaciones_filtradas, function($p) use ($filtro_codigo) {
        return stripos($p['codigo'], $filtro_codigo) !== false || 
               stripos($p['titulo_proyecto'], $filtro_codigo) !== false;
    });
}

if ($filtro_estado) {
    $postulaciones_filtradas = array_filter($postulaciones_filtradas, fn($p) => $p['estado'] == $filtro_estado);
}

if ($filtro_convocatoria) {
    $postulaciones_filtradas = array_filter($postulaciones_filtradas, fn($p) => $p['convocatoria_id'] == $filtro_convocatoria);
}

if ($filtro_region) {
    $postulaciones_filtradas = array_filter($postulaciones_filtradas, function($p) use ($filtro_region, $escuelas) {
        $escuela = array_filter($escuelas, fn($e) => $e['id'] == $p['escuela_id']);
        if (!empty($escuela)) {
            $escuela = reset($escuela);
            return $escuela['region_id'] == $filtro_region;
        }
        return false;
    });
}

// Estadísticas
$total_postulaciones = count($postulaciones);
$aprobadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'APROBADA'));
$rechazadas = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'RECHAZADA'));
$en_evaluacion = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_EVALUACION'));
$en_revision = count(array_filter($postulaciones, fn($p) => $p['estado'] == 'EN_REVISION'));
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Postulaciones</span>
    </div>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0; color: var(--dark-color);">📝 Postulaciones</h1>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                Gestión de postulaciones a convocatorias FONDEP
            </p>
        </div>
        <a href="crear.php" class="btn btn-primary">
            ➕ Nueva Postulación
        </a>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total_postulaciones; ?></div>
                <div class="stat-label">Total Postulaciones</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $aprobadas; ?></div>
                <div class="stat-label">Aprobadas</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo $en_evaluacion; ?></div>
                <div class="stat-label">En Evaluación</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #ef4444, #dc2626);">
                <div class="stat-number"><?php echo $rechazadas; ?></div>
                <div class="stat-label">Rechazadas</div>
            </div>
        </div>
    </div>

    <!-- Filtros de Búsqueda -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1rem;">🔎 Filtros de Búsqueda</h3>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" action="" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; align-items: flex-end;">
                
                <!-- Búsqueda por Código/Título -->
                <div>
                    <label class="form-label">Código o Título del Proyecto</label>
                    <input type="text" 
                           name="codigo" 
                           class="form-control"
                           placeholder="Buscar..."
                           value="<?php echo htmlspecialchars($filtro_codigo); ?>">
                </div>

                <!-- Estado -->
                <div>
                    <label class="form-label">Estado</label>
                    <select name="estado" class="form-control">
                        <option value="">Todos los estados</option>
                        <option value="APROBADA" <?php echo $filtro_estado == 'APROBADA' ? 'selected' : ''; ?>>Aprobada</option>
                        <option value="RECHAZADA" <?php echo $filtro_estado == 'RECHAZADA' ? 'selected' : ''; ?>>Rechazada</option>
                        <option value="EN_EVALUACION" <?php echo $filtro_estado == 'EN_EVALUACION' ? 'selected' : ''; ?>>En Evaluación</option>
                        <option value="EN_REVISION" <?php echo $filtro_estado == 'EN_REVISION' ? 'selected' : ''; ?>>En Revisión</option>
                        <option value="OBSERVADA" <?php echo $filtro_estado == 'OBSERVADA' ? 'selected' : ''; ?>>Observada</option>
                    </select>
                </div>

                <!-- Región -->
                <div>
                    <label class="form-label">Región</label>
                    <select name="region" class="form-control">
                        <option value="">Todas las regiones</option>
                        <?php foreach ($regiones as $region): ?>
                            <option value="<?php echo $region['id']; ?>"
                                    <?php echo $filtro_region == $region['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($region['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Convocatoria -->
                <div>
                    <label class="form-label">Convocatoria</label>
                    <select name="convocatoria_id" class="form-control">
                        <option value="">Todas las convocatorias</option>
                        <?php foreach ($convocatorias as $conv): ?>
                            <option value="<?php echo $conv['id']; ?>"
                                    <?php echo $filtro_convocatoria == $conv['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($conv['codigo']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        🔍 Filtrar
                    </button>
                    <a href="listar.php" class="btn btn-secondary" style="flex: 1;">
                        🔄 Limpiar
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Postulaciones -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1.125rem;">
                📋 Listado de Postulaciones (<?php echo count($postulaciones_filtradas); ?>)
            </h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Proyecto</th>
                        <th>Escuela</th>
                        <th>Región</th>
                        <th>Convocatoria</th>
                        <th>Monto Solicitado</th>
                        <th>Fecha</th>
                        <th>Estado</th>
                        <th>Puntaje</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($postulaciones_filtradas)): ?>
                        <tr>
                            <td colspan="10" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No se encontraron postulaciones con los filtros aplicados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($postulaciones_filtradas as $post): ?>
                            <?php 
                            $escuela = array_filter($escuelas, fn($e) => $e['id'] == $post['escuela_id']);
                            $escuela = !empty($escuela) ? reset($escuela) : null;
                            
                            $convocatoria = obtenerConvocatoriaPorId($post['convocatoria_id']);
                            ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-color);">
                                        <?php echo htmlspecialchars($post['codigo']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="max-width: 250px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <strong><?php echo htmlspecialchars($post['titulo_proyecto']); ?></strong>
                                    </div>
                                </td>
                                <td style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($escuela['nombre'] ?? 'N/A'); ?>
                                </td>
                                <td style="font-size: 0.875rem;">
                                    <?php echo htmlspecialchars(obtenerNombreRegion($escuela['region_id'] ?? 0)); ?>
                                </td>
                                <td style="font-size: 0.875rem;">
                                    <a href="../convocatorias/detalle.php?id=<?php echo $convocatoria['id']; ?>"
                                       style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars($convocatoria['codigo']); ?>
                                    </a>
                                </td>
                                <td>
                                    <strong style="color: var(--success-color);">
                                        <?php echo formatearMonto($post['monto_solicitado']); ?>
                                    </strong>
                                </td>
                                <td style="font-size: 0.875rem; white-space: nowrap;">
                                    <?php echo formatearFecha($post['fecha_postulacion']); ?>
                                </td>
                                <td>
                                    <span class="badge <?php echo obtenerClaseBadge($post['estado']); ?>">
                                        <?php echo str_replace('_', ' ', $post['estado']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <?php if ($post['puntaje_final']): ?>
                                        <div style="display: inline-block; padding: 0.25rem 0.75rem; background-color: #d1fae5; border-radius: 6px;">
                                            <strong style="color: #065f46; font-size: 1rem;">
                                                <?php echo number_format($post['puntaje_final'], 1); ?>
                                            </strong>
                                        </div>
                                    <?php else: ?>
                                        <span style="color: #9ca3af;">-</span>
                                    <?php endif; ?>
                                </td>
                                <td style="text-align: center;">
                                    <a href="detalle.php?codigo=<?php echo $post['codigo']; ?>" 
                                       class="btn btn-sm btn-info" 
                                       title="Ver Detalle Completo">
                                        👁️ Ver
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Resumen de Resultados -->
        <?php if (count($postulaciones_filtradas) > 0): ?>
            <div style="padding: 1rem; background-color: #f9fafb; border-top: 1px solid var(--border-color); font-size: 0.875rem; color: #6b7280;">
                Mostrando <?php echo count($postulaciones_filtradas); ?> de <?php echo $total_postulaciones; ?> postulaciones totales
            </div>
        <?php endif; ?>
    </div>

    <!-- Distribución por Estado -->
    <div class="grid" style="margin-top: 2rem; gap: 1rem;">
        <div class="col-6">
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">📊 Distribución por Estado</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <?php
                    $estados_data = [
                        ['estado' => 'APROBADA', 'cantidad' => $aprobadas, 'color' => '#10b981'],
                        ['estado' => 'EN_EVALUACION', 'cantidad' => $en_evaluacion, 'color' => '#f59e0b'],
                        ['estado' => 'EN_REVISION', 'cantidad' => $en_revision, 'color' => '#3b82f6'],
                        ['estado' => 'RECHAZADA', 'cantidad' => $rechazadas, 'color' => '#ef4444'],
                    ];
                    ?>
                    <?php foreach ($estados_data as $item): ?>
                        <?php if ($item['cantidad'] > 0): ?>
                            <div style="margin-bottom: 1rem;">
                                <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                    <span style="font-weight: 500;"><?php echo str_replace('_', ' ', $item['estado']); ?></span>
                                    <span style="font-weight: 700; color: <?php echo $item['color']; ?>;">
                                        <?php echo $item['cantidad']; ?> 
                                        (<?php echo number_format(($item['cantidad'] / $total_postulaciones) * 100, 1); ?>%)
                                    </span>
                                </div>
                                <div style="background-color: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden;">
                                    <div style="background-color: <?php echo $item['color']; ?>; 
                                                height: 100%; 
                                                width: <?php echo ($item['cantidad'] / $total_postulaciones) * 100; ?>%;">
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="col-6">
            <div class="alert alert-info">
                <strong>ℹ️ Información:</strong> Los datos mostrados son simulados. 
                Al conectar a PostgreSQL, esta vista consultará con el siguiente query:
                <pre style="background-color: #1f2937; color: #10b981; padding: 1rem; border-radius: 6px; margin-top: 0.5rem; overflow-x: auto; font-size: 0.75rem;">
SELECT p.*, e.nombre as escuela, r.nombre as region, 
       c.codigo as conv_codigo, ev.puntaje_total
FROM postulaciones p
JOIN escuelas e ON e.id = p.escuela_id
JOIN regiones r ON r.id = e.region_id
JOIN convocatorias c ON c.id = p.convocatoria_id
LEFT JOIN evaluaciones ev ON ev.postulacion_id = p.id
WHERE 1=1
  AND (p.codigo LIKE '%filtro%' OR p.titulo_proyecto LIKE '%filtro%')
  AND p.estado = 'estado_filtro'
ORDER BY p.fecha_postulacion DESC;</pre>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>