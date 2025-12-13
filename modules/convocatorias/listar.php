<?php
/**
 * MÓDULO: CONVOCATORIAS - LISTAR
 * Muestra todas las convocatorias del sistema con filtros
 * 
 * Base de datos: PostgreSQL (tabla: convocatorias)
 * Datos actuales: Simulados
 */

$page_title = "Convocatorias";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener datos simulados
$convocatorias = obtenerConvocatorias();

// Aplicar filtros si existen
$filtro_estado = $_GET['estado'] ?? '';
if ($filtro_estado) {
    $convocatorias = array_filter($convocatorias, function($conv) use ($filtro_estado) {
        return $conv['estado'] == $filtro_estado;
    });
}

// Estadísticas rápidas
$total_convocatorias = count(obtenerConvocatorias());
$abiertas = count(array_filter(obtenerConvocatorias(), fn($c) => $c['estado'] == 'ABIERTA'));
$cerradas = count(array_filter(obtenerConvocatorias(), fn($c) => $c['estado'] == 'CERRADA'));
$en_evaluacion = count(array_filter(obtenerConvocatorias(), fn($c) => $c['estado'] == 'EN_EVALUACION'));
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <span>Convocatorias</span>
    </div>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0; color: var(--dark-color);">📢 Convocatorias</h1>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                Gestión de convocatorias de financiamiento FONDEP
            </p>
        </div>
        <a href="crear.php" class="btn btn-primary">
            ➕ Nueva Convocatoria
        </a>
    </div>

    <!-- Tarjetas de Estadísticas -->
    <div class="grid" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #3b82f6, #2563eb);">
                <div class="stat-number"><?php echo $total_convocatorias; ?></div>
                <div class="stat-label">Total Convocatorias</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #10b981, #059669);">
                <div class="stat-number"><?php echo $abiertas; ?></div>
                <div class="stat-label">Abiertas</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #f59e0b, #d97706);">
                <div class="stat-number"><?php echo $en_evaluacion; ?></div>
                <div class="stat-label">En Evaluación</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #6b7280, #4b5563);">
                <div class="stat-number"><?php echo $cerradas; ?></div>
                <div class="stat-label">Cerradas</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <form method="GET" action="" style="display: flex; gap: 1rem; align-items: flex-end;">
            <div style="flex: 1;">
                <label class="form-label">Estado de Convocatoria</label>
                <select name="estado" class="form-control">
                    <option value="">Todos los estados</option>
                    <option value="ABIERTA" <?php echo $filtro_estado == 'ABIERTA' ? 'selected' : ''; ?>>Abierta</option>
                    <option value="CERRADA" <?php echo $filtro_estado == 'CERRADA' ? 'selected' : ''; ?>>Cerrada</option>
                    <option value="EN_EVALUACION" <?php echo $filtro_estado == 'EN_EVALUACION' ? 'selected' : ''; ?>>En Evaluación</option>
                    <option value="PLANIFICADA" <?php echo $filtro_estado == 'PLANIFICADA' ? 'selected' : ''; ?>>Planificada</option>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">🔍 Filtrar</button>
            <a href="listar.php" class="btn btn-secondary">🔄 Limpiar</a>
        </form>
    </div>

    <!-- Tabla de Convocatorias -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1.125rem;">
                Listado de Convocatorias (<?php echo count($convocatorias); ?>)
            </h3>
        </div>
        
        <div style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Fecha Inicio</th>
                        <th>Fecha Fin</th>
                        <th>Presupuesto</th>
                        <th>Estado</th>
                        <th style="text-align: center;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($convocatorias)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem; color: #6b7280;">
                                No se encontraron convocatorias con los filtros aplicados
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($convocatorias as $conv): ?>
                            <tr>
                                <td>
                                    <strong style="color: var(--primary-color);">
                                        <?php echo htmlspecialchars($conv['codigo']); ?>
                                    </strong>
                                </td>
                                <td>
                                    <div style="font-weight: 500;">
                                        <?php echo htmlspecialchars($conv['titulo']); ?>
                                    </div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">
                                        <?php echo substr(htmlspecialchars($conv['descripcion']), 0, 60); ?>...
                                    </div>
                                </td>
                                <td><?php echo formatearFecha($conv['fecha_inicio']); ?></td>
                                <td><?php echo formatearFecha($conv['fecha_fin']); ?></td>
                                <td>
                                    <strong><?php echo formatearMonto($conv['presupuesto_total']); ?></strong>
                                </td>
                                <td>
                                    <span class="badge <?php echo obtenerClaseBadge($conv['estado']); ?>">
                                        <?php echo str_replace('_', ' ', $conv['estado']); ?>
                                    </span>
                                </td>
                                <td style="text-align: center;">
                                    <div class="btn-group">
                                        <a href="detalle.php?id=<?php echo $conv['id']; ?>" 
                                           class="btn btn-sm btn-info" 
                                           title="Ver Detalle">
                                            👁️ Ver
                                        </a>
                                        <a href="editar.php?id=<?php echo $conv['id']; ?>" 
                                           class="btn btn-sm btn-warning" 
                                           title="Editar">
                                            ✏️ Editar
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Información adicional -->
    <div class="alert alert-info" style="margin-top: 1.5rem;">
        <strong>ℹ️ Información:</strong> Los datos mostrados son simulados. 
        Al conectar a PostgreSQL, esta vista consultará la tabla <code>convocatorias</code> 
        con queries optimizados y paginación real.
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>