<?php
/**
 * MÓDULO: CONVOCATORIAS - EDITAR
 * Formulario para editar convocatoria existente
 * 
 * Base de datos: PostgreSQL (UPDATE en tabla convocatorias)
 */

$page_title = "Editar Convocatoria";
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

// Procesar formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = [];
    
    if (empty($_POST['titulo'])) $errores[] = 'El título es obligatorio';
    if (empty($_POST['fecha_inicio'])) $errores[] = 'La fecha de inicio es obligatoria';
    if (empty($_POST['fecha_fin'])) $errores[] = 'La fecha de fin es obligatoria';
    
    // Validar fechas
    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        if (strtotime($_POST['fecha_fin']) <= strtotime($_POST['fecha_inicio'])) {
            $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
        }
    }
    
    if (empty($errores)) {
        $mensaje = '✅ Convocatoria actualizada exitosamente<br>';
        $mensaje .= 'Cambios registrados en PostgreSQL y evento guardado en Cassandra.';
        $tipo_mensaje = 'success';
        
        // Simular actualización de datos
        $convocatoria = array_merge($convocatoria, $_POST);
    } else {
        $mensaje = '❌ ' . implode('<br>', $errores);
        $tipo_mensaje = 'danger';
    }
}
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Convocatorias</a> / 
        <span>Editar</span>
    </div>

    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
        <div>
            <h1 style="margin: 0; color: var(--dark-color);">✏️ Editar Convocatoria</h1>
            <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
                <?php echo htmlspecialchars($convocatoria['codigo']); ?>
            </p>
        </div>
        <a href="detalle.php?id=<?php echo $convocatoria_id; ?>" class="btn btn-secondary">
            👁️ Ver Detalle
        </a>
    </div>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>" style="margin-bottom: 1.5rem;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="grid">
            
            <!-- Formulario Principal -->
            <div class="col-8">
                <div class="card">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1.125rem;">📝 Datos de la Convocatoria</h3>
                    </div>
                    
                    <div style="padding: 1.5rem;">
                        
                        <!-- Código (solo lectura) -->
                        <div class="form-group">
                            <label class="form-label">Código de Convocatoria</label>
                            <input type="text" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($convocatoria['codigo']); ?>"
                                   readonly
                                   style="background-color: #f3f4f6; cursor: not-allowed;">
                            <small class="form-help">El código no puede ser modificado</small>
                        </div>

                        <!-- Título -->
                        <div class="form-group">
                            <label class="form-label">
                                Título de la Convocatoria <span style="color: red;">*</span>
                            </label>
                            <input type="text" 
                                   name="titulo" 
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($convocatoria['titulo']); ?>"
                                   required>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" 
                                      class="form-control" 
                                      rows="4"><?php echo htmlspecialchars($convocatoria['descripcion']); ?></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="grid">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Fecha de Inicio <span style="color: red;">*</span>
                                    </label>
                                    <input type="date" 
                                           name="fecha_inicio" 
                                           class="form-control"
                                           value="<?php echo $convocatoria['fecha_inicio']; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Fecha de Fin <span style="color: red;">*</span>
                                    </label>
                                    <input type="date" 
                                           name="fecha_fin" 
                                           class="form-control"
                                           value="<?php echo $convocatoria['fecha_fin']; ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Presupuestos -->
                        <div class="grid">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Presupuesto Total (S/)</label>
                                    <input type="number" 
                                           name="presupuesto_total" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           value="<?php echo $convocatoria['presupuesto_total']; ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Monto Máximo por Proyecto (S/)</label>
                                    <input type="number" 
                                           name="monto_max_proyecto" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           value="<?php echo $convocatoria['monto_max_proyecto']; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Requisitos -->
                        <div class="form-group">
                            <label class="form-label">Requisitos de Postulación</label>
                            <textarea name="requisitos" 
                                      class="form-control" 
                                      rows="3"><?php echo htmlspecialchars($convocatoria['requisitos']); ?></textarea>
                        </div>

                        <!-- Áreas Temáticas -->
                        <div class="form-group">
                            <label class="form-label">Áreas Temáticas</label>
                            <input type="text" 
                                   name="areas_tematicas" 
                                   class="form-control"
                                   value="<?php echo htmlspecialchars($convocatoria['areas_tematicas']); ?>">
                            <small class="form-help">Separar por comas</small>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label class="form-label">Estado</label>
                            <select name="estado" class="form-control">
                                <option value="PLANIFICADA" <?php echo $convocatoria['estado'] == 'PLANIFICADA' ? 'selected' : ''; ?>>
                                    Planificada
                                </option>
                                <option value="ABIERTA" <?php echo $convocatoria['estado'] == 'ABIERTA' ? 'selected' : ''; ?>>
                                    Abierta
                                </option>
                                <option value="CERRADA" <?php echo $convocatoria['estado'] == 'CERRADA' ? 'selected' : ''; ?>>
                                    Cerrada
                                </option>
                                <option value="EN_EVALUACION" <?php echo $convocatoria['estado'] == 'EN_EVALUACION' ? 'selected' : ''; ?>>
                                    En Evaluación
                                </option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                💾 Guardar Cambios
                            </button>
                            <a href="listar.php" class="btn btn-secondary" style="flex: 1;">
                                ❌ Cancelar
                            </a>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Panel Lateral -->
            <div class="col-4">
                
                <!-- Estado Actual -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #eff6ff;">
                        <h3 style="margin: 0; font-size: 1rem; color: var(--primary-color);">ℹ️ Estado Actual</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <div style="margin-bottom: 1rem;">
                            <div style="font-size: 0.875rem; color: #6b7280;">Estado</div>
                            <span class="badge <?php echo obtenerClaseBadge($convocatoria['estado']); ?>" style="font-size: 1rem;">
                                <?php echo str_replace('_', ' ', $convocatoria['estado']); ?>
                            </span>
                        </div>
                        <div>
                            <div style="font-size: 0.875rem; color: #6b7280;">Última Actualización</div>
                            <div style="font-weight: 600;">
                                <?php echo date('d/m/Y H:i'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Advertencias -->
                <div class="alert alert-warning">
                    <strong>⚠️ Importante:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                        <li>No modifique fechas si ya hay postulaciones</li>
                        <li>Cambios en el presupuesto requieren aprobación</li>
                        <li>El estado afecta la visibilidad de la convocatoria</li>
                    </ul>
                </div>

                <!-- Integración de BD -->
                <div class="alert alert-info">
                    <strong>📊 Actualización de Datos:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                        <li><strong>PostgreSQL:</strong> UPDATE en tabla convocatorias</li>
                        <li><strong>Cassandra:</strong> Evento de modificación con timestamp</li>
                    </ul>
                </div>

            </div>

        </div>
    </form>

</div>

<?php require_once '../../includes/footer.php'; ?>