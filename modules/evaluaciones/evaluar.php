<?php
/**
 * MÓDULO: EVALUACIONES - EVALUAR
 * Formulario para calificar una postulación
 * 
 * Integración de datos:
 * - PostgreSQL: UPDATE en tabla evaluaciones
 * - MongoDB: Lectura de propuesta completa
 * - Cassandra: Registro de evento EVALUATION
 */

$page_title = "Evaluar Postulación";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener ID de evaluación
$evaluacion_id = $_GET['id'] ?? 1;

// Buscar evaluación
$todas_evaluaciones = obtenerEvaluaciones();
$evaluacion = array_filter($todas_evaluaciones, fn($e) => $e['id'] == $evaluacion_id);
$evaluacion = !empty($evaluacion) ? reset($evaluacion) : null;

if (!$evaluacion) {
    echo "<div class='container'><div class='alert alert-danger'>Evaluación no encontrada</div></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Obtener postulación
$postulacion = array_filter(obtenerPostulaciones(), fn($p) => $p['id'] == $evaluacion['postulacion_id']);
$postulacion = !empty($postulacion) ? reset($postulacion) : null;

// Obtener convocatoria y escuela
$convocatoria = obtenerConvocatoriaPorId($postulacion['convocatoria_id']);
$escuela = array_filter(obtenerEscuelas(), fn($e) => $e['id'] == $postulacion['escuela_id']);
$escuela = !empty($escuela) ? reset($escuela) : null;

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $evaluacion['estado'] == 'ASIGNADA') {
    $errores = [];
    
    // Validar puntajes
    $puntajes = ['pertinencia', 'viabilidad', 'impacto', 'sostenibilidad'];
    foreach ($puntajes as $criterio) {
        $valor = $_POST['puntaje_' . $criterio] ?? '';
        if ($valor === '' || $valor < 0 || $valor > 100) {
            $errores[] = "El puntaje de {$criterio} debe estar entre 0 y 100";
        }
    }
    
    if (empty($_POST['comentarios'])) {
        $errores[] = 'Los comentarios son obligatorios';
    }
    
    if (empty($errores)) {
        // Calcular puntaje total
        $puntaje_total = (
            $_POST['puntaje_pertinencia'] + 
            $_POST['puntaje_viabilidad'] + 
            $_POST['puntaje_impacto'] + 
            $_POST['puntaje_sostenibilidad']
        ) / 4;
        
        $mensaje = "✅ Evaluación guardada exitosamente<br>";
        $mensaje .= "<strong>Puntaje Total:</strong> " . number_format($puntaje_total, 1) . " / 100<br>";
        $mensaje .= "Datos registrados en PostgreSQL y evento guardado en Cassandra.";
        $tipo_mensaje = 'success';
        
        // Simular actualización de estado
        $evaluacion['estado'] = 'COMPLETADA';
    } else {
        $mensaje = '❌ ' . implode('<br>', $errores);
        $tipo_mensaje = 'danger';
    }
}

// Determinar si es solo lectura
$solo_lectura = $evaluacion['estado'] == 'COMPLETADA';
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Evaluaciones</a> / 
        <span>Evaluar</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">
            <?php echo $solo_lectura ? '👁️ Ver Evaluación' : '✏️ Evaluar Postulación'; ?>
        </h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            <?php echo htmlspecialchars($postulacion['codigo'] . ' - ' . $postulacion['titulo_proyecto']); ?>
        </p>
    </div>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>" style="margin-bottom: 1.5rem;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        
        <!-- Columna Principal - Formulario de Evaluación -->
        <div class="col-8">
            
            <!-- Información de la Postulación -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #eff6ff;">
                    <h3 style="margin: 0; font-size: 1.125rem; color: var(--primary-color);">
                        📄 Información de la Postulación
                    </h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="row">
                        <div class="col-6">
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.875rem; color: #6b7280;">Convocatoria</div>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($convocatoria['titulo']); ?>
                                </div>
                            </div>
                            <div>
                                <div style="font-size: 0.875rem; color: #6b7280;">Institución</div>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($escuela['nombre']); ?>
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">
                                    <?php echo htmlspecialchars(obtenerNombreRegion($escuela['region_id'])); ?>
                                </div>
                            </div>
                        </div>
                        <div class="col-6">
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.875rem; color: #6b7280;">Monto Solicitado</div>
                                <div style="font-weight: 700; color: var(--success-color); font-size: 1.5rem;">
                                    <?php echo formatearMonto($postulacion['monto_solicitado']); ?>
                                </div>
                            </div>
                            <div>
                                <a href="../postulaciones/detalle.php?codigo=<?php echo $postulacion['codigo']; ?>" 
                                   class="btn btn-info btn-sm"
                                   target="_blank">
                                    👁️ Ver Propuesta Completa
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Formulario de Evaluación -->
            <form method="POST" action="">
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1.125rem;">⭐ Criterios de Evaluación</h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        
                        <!-- Pertinencia -->
                        <div class="form-group">
                            <label class="form-label">
                                1. Pertinencia del Proyecto (0-100 puntos)
                                <span style="color: red;">*</span>
                            </label>
                            <input type="number" 
                                   name="puntaje_pertinencia" 
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.1"
                                   value="<?php echo $evaluacion['puntaje_pertinencia'] ?? ''; ?>"
                                   <?php echo $solo_lectura ? 'readonly' : 'required'; ?>>
                            <small class="form-help">
                                Evalúe la alineación del proyecto con los objetivos de la convocatoria y las necesidades educativas identificadas
                            </small>
                        </div>

                        <!-- Viabilidad -->
                        <div class="form-group">
                            <label class="form-label">
                                2. Viabilidad del Proyecto (0-100 puntos)
                                <span style="color: red;">*</span>
                            </label>
                            <input type="number" 
                                   name="puntaje_viabilidad" 
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.1"
                                   value="<?php echo $evaluacion['puntaje_viabilidad'] ?? ''; ?>"
                                   <?php echo $solo_lectura ? 'readonly' : 'required'; ?>>
                            <small class="form-help">
                                Evalúe la factibilidad técnica, presupuestal y operativa del proyecto
                            </small>
                        </div>

                        <!-- Impacto -->
                        <div class="form-group">
                            <label class="form-label">
                                3. Impacto Esperado (0-100 puntos)
                                <span style="color: red;">*</span>
                            </label>
                            <input type="number" 
                                   name="puntaje_impacto" 
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.1"
                                   value="<?php echo $evaluacion['puntaje_impacto'] ?? ''; ?>"
                                   <?php echo $solo_lectura ? 'readonly' : 'required'; ?>>
                            <small class="form-help">
                                Evalúe el potencial de mejora en el aprendizaje y beneficios para la comunidad educativa
                            </small>
                        </div>

                        <!-- Sostenibilidad -->
                        <div class="form-group">
                            <label class="form-label">
                                4. Sostenibilidad del Proyecto (0-100 puntos)
                                <span style="color: red;">*</span>
                            </label>
                            <input type="number" 
                                   name="puntaje_sostenibilidad" 
                                   class="form-control"
                                   min="0"
                                   max="100"
                                   step="0.1"
                                   value="<?php echo $evaluacion['puntaje_sostenibilidad'] ?? ''; ?>"
                                   <?php echo $solo_lectura ? 'readonly' : 'required'; ?>>
                            <small class="form-help">
                                Evalúe la capacidad de mantener los resultados del proyecto a largo plazo
                            </small>
                        </div>

                        <!-- Comentarios -->
                        <div class="form-group">
                            <label class="form-label">
                                Comentarios y Observaciones
                                <span style="color: red;">*</span>
                            </label>
                            <textarea name="comentarios" 
                                      class="form-control" 
                                      rows="6"
                                      placeholder="Detalle las fortalezas y debilidades del proyecto, justifique los puntajes asignados..."
                                      <?php echo $solo_lectura ? 'readonly' : 'required'; ?>><?php echo $evaluacion['comentarios'] ?? ''; ?></textarea>
                        </div>

                        <!-- Recomendación -->
                        <div class="form-group">
                            <label class="form-label">
                                Recomendación Final
                                <span style="color: red;">*</span>
                            </label>
                            <select name="recomendacion" 
                                    class="form-control"
                                    <?php echo $solo_lectura ? 'disabled' : 'required'; ?>>
                                <option value="">Seleccione...</option>
                                <option value="APROBAR" <?php echo ($evaluacion['recomendacion'] ?? '') == 'APROBAR' ? 'selected' : ''; ?>>
                                    ✅ APROBAR - Proyecto cumple con todos los criterios
                                </option>
                                <option value="APROBAR_CON_OBSERVACIONES" <?php echo ($evaluacion['recomendacion'] ?? '') == 'APROBAR_CON_OBSERVACIONES' ? 'selected' : ''; ?>>
                                    ⚠️ APROBAR CON OBSERVACIONES - Requiere ajustes menores
                                </option>
                                <option value="RECHAZAR" <?php echo ($evaluacion['recomendacion'] ?? '') == 'RECHAZAR' ? 'selected' : ''; ?>>
                                    ❌ RECHAZAR - No cumple con criterios mínimos
                                </option>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <?php if (!$solo_lectura): ?>
                    <div style="display: flex; gap: 1rem;">
                        <button type="submit" class="btn btn-primary" style="flex: 1;">
                            💾 Guardar Evaluación
                        </button>
                        <a href="listar.php" class="btn btn-secondary" style="flex: 1;">
                            ❌ Cancelar
                        </a>
                    </div>
                <?php else: ?>
                    <div>
                        <a href="listar.php" class="btn btn-secondary" style="width: 100%;">
                            ← Volver a Mis Evaluaciones
                        </a>
                    </div>
                <?php endif; ?>
            </form>

        </div>

        <!-- Panel Lateral -->
        <div class="col-4">
            
            <!-- Cálculo de Puntaje -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">📊 Cálculo de Puntaje</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php if ($evaluacion['puntaje_total']): ?>
                        <div style="text-align: center; padding: 1.5rem; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 6px; margin-bottom: 1rem;">
                            <div style="font-size: 3rem; font-weight: 700;">
                                <?php echo number_format($evaluacion['puntaje_total'], 1); ?>
                            </div>
                            <div style="font-size: 1rem;">Puntaje Final</div>
                        </div>
                        <div style="font-size: 0.875rem; color: #6b7280;">
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Pertinencia:</span>
                                <strong><?php echo $evaluacion['puntaje_pertinencia']; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Viabilidad:</span>
                                <strong><?php echo $evaluacion['puntaje_viabilidad']; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                <span>Impacto:</span>
                                <strong><?php echo $evaluacion['puntaje_impacto']; ?></strong>
                            </div>
                            <div style="display: flex; justify-content: space-between;">
                                <span>Sostenibilidad:</span>
                                <strong><?php echo $evaluacion['puntaje_sostenibilidad']; ?></strong>
                            </div>
                        </div>
                    <?php else: ?>
                        <div style="text-align: center; padding: 2rem; color: #6b7280;">
                            Complete todos los criterios para ver el puntaje final
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Guía de Evaluación -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #eff6ff;">
                    <h3 style="margin: 0; font-size: 1rem; color: var(--primary-color);">💡 Guía de Evaluación</h3>
                </div>
                <div style="padding: 1rem; font-size: 0.875rem;">
                    <div style="margin-bottom: 1rem;">
                        <strong>Escala de Puntajes:</strong>
                        <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem;">
                            <li>90-100: Excelente</li>
                            <li>75-89: Muy bueno</li>
                            <li>60-74: Bueno</li>
                            <li>0-59: Insuficiente</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Puntaje Mínimo de Aprobación:</strong>
                        <div style="background-color: #fef3c7; padding: 0.75rem; border-radius: 6px; margin-top: 0.5rem;">
                            El proyecto requiere un puntaje final mínimo de <strong>70 puntos</strong> para ser considerado aprobado.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Alerta de Estado -->
            <?php if ($solo_lectura): ?>
                <div class="alert alert-success">
                    <strong>✅ Evaluación Completada</strong><br>
                    Fecha: <?php echo formatearFecha($evaluacion['fecha_evaluacion']); ?>
                </div>
            <?php else: ?>
                <div class="alert alert-warning">
                    <strong>⚠️ Recordatorio:</strong> Una vez guardada, la evaluación no podrá ser modificada.
                </div>
            <?php endif; ?>

            <!-- Integración de BD -->
            <div class="alert alert-info">
                <strong>📊 Registro de Datos:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                    <li><strong>PostgreSQL:</strong> Puntajes y recomendación</li>
                    <li><strong>MongoDB:</strong> Comentarios extensos</li>
                    <li><strong>Cassandra:</strong> Evento de evaluación con timestamp</li>
                </ul>
            </div>

        </div>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>