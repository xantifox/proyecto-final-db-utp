<?php
/**
 * MÓDULO: CONVOCATORIAS - CREAR
 * Formulario para registrar nueva convocatoria
 * 
 * Base de datos: PostgreSQL (INSERT en tabla convocatorias)
 * Datos actuales: Simulados
 */

$page_title = "Nueva Convocatoria";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Procesar formulario si se envió
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar datos
    $errores = [];
    
    if (empty($_POST['codigo'])) $errores[] = 'El código es obligatorio';
    if (empty($_POST['titulo'])) $errores[] = 'El título es obligatorio';
    if (empty($_POST['fecha_inicio'])) $errores[] = 'La fecha de inicio es obligatoria';
    if (empty($_POST['fecha_fin'])) $errores[] = 'La fecha de fin es obligatoria';
    if (empty($_POST['presupuesto_total'])) $errores[] = 'El presupuesto es obligatorio';
    
    // Validar que fecha fin sea posterior a fecha inicio
    if (!empty($_POST['fecha_inicio']) && !empty($_POST['fecha_fin'])) {
        if (strtotime($_POST['fecha_fin']) <= strtotime($_POST['fecha_inicio'])) {
            $errores[] = 'La fecha de fin debe ser posterior a la fecha de inicio';
        }
    }
    
    if (empty($errores)) {
        // Aquí iría el INSERT a PostgreSQL
        // Por ahora solo mostramos mensaje de éxito
        $mensaje = '✅ Convocatoria creada exitosamente con código: ' . htmlspecialchars($_POST['codigo']);
        $tipo_mensaje = 'success';
        
        // Limpiar formulario
        $_POST = [];
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
        <span>Nueva Convocatoria</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">➕ Nueva Convocatoria</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Complete el formulario para registrar una nueva convocatoria de financiamiento
        </p>
    </div>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>" style="margin-bottom: 1.5rem;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <div class="row">
        <!-- Formulario Principal -->
        <div class="col-8">
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📝 Información de la Convocatoria</h3>
                </div>
                
                <div style="padding: 1.5rem;">
                    <form method="POST" action="">
                        
                        <!-- Código -->
                        <div class="form-group">
                            <label class="form-label">
                                Código de Convocatoria <span style="color: red;">*</span>
                            </label>
                            <input type="text" 
                                   name="codigo" 
                                   class="form-control" 
                                   placeholder="Ej: CONV-2024-006"
                                   value="<?php echo $_POST['codigo'] ?? ''; ?>"
                                   required>
                            <small class="form-help">Formato: CONV-AAAA-NNN</small>
                        </div>

                        <!-- Título -->
                        <div class="form-group">
                            <label class="form-label">
                                Título de la Convocatoria <span style="color: red;">*</span>
                            </label>
                            <input type="text" 
                                   name="titulo" 
                                   class="form-control" 
                                   placeholder="Ej: Innovación en Educación STEM 2024"
                                   value="<?php echo $_POST['titulo'] ?? ''; ?>"
                                   required>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label class="form-label">Descripción</label>
                            <textarea name="descripcion" 
                                      class="form-control" 
                                      rows="4" 
                                      placeholder="Descripción detallada de los objetivos y alcance de la convocatoria"><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                        </div>

                        <!-- Fechas -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Fecha de Inicio <span style="color: red;">*</span>
                                    </label>
                                    <input type="date" 
                                           name="fecha_inicio" 
                                           class="form-control"
                                           value="<?php echo $_POST['fecha_inicio'] ?? ''; ?>"
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
                                           value="<?php echo $_POST['fecha_fin'] ?? ''; ?>"
                                           required>
                                </div>
                            </div>
                        </div>

                        <!-- Presupuestos -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Presupuesto Total (S/) <span style="color: red;">*</span>
                                    </label>
                                    <input type="number" 
                                           name="presupuesto_total" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           placeholder="500000.00"
                                           value="<?php echo $_POST['presupuesto_total'] ?? ''; ?>"
                                           required>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Monto Máximo por Proyecto (S/)
                                    </label>
                                    <input type="number" 
                                           name="monto_max_proyecto" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           placeholder="50000.00"
                                           value="<?php echo $_POST['monto_max_proyecto'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Requisitos -->
                        <div class="form-group">
                            <label class="form-label">Requisitos de Postulación</label>
                            <textarea name="requisitos" 
                                      class="form-control" 
                                      rows="3" 
                                      placeholder="Ej: Escuelas públicas de nivel secundaria con mínimo 200 alumnos"><?php echo $_POST['requisitos'] ?? ''; ?></textarea>
                        </div>

                        <!-- Áreas Temáticas -->
                        <div class="form-group">
                            <label class="form-label">Áreas Temáticas</label>
                            <input type="text" 
                                   name="areas_tematicas" 
                                   class="form-control" 
                                   placeholder="Ej: Ciencias, Matemáticas, Tecnología"
                                   value="<?php echo $_POST['areas_tematicas'] ?? ''; ?>">
                            <small class="form-help">Separar por comas</small>
                        </div>

                        <!-- Estado -->
                        <div class="form-group">
                            <label class="form-label">Estado Inicial</label>
                            <select name="estado" class="form-control">
                                <option value="PLANIFICADA">Planificada</option>
                                <option value="ABIERTA">Abierta</option>
                            </select>
                        </div>

                        <!-- Botones -->
                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn btn-primary" style="flex: 1;">
                                💾 Guardar Convocatoria
                            </button>
                            <a href="listar.php" class="btn btn-secondary" style="flex: 1;">
                                ❌ Cancelar
                            </a>
                        </div>

                    </form>
                </div>
            </div>
        </div>

        <!-- Panel de Ayuda -->
        <div class="col-4">
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #eff6ff;">
                    <h3 style="margin: 0; font-size: 1rem; color: var(--primary-color);">💡 Ayuda</h3>
                </div>
                <div style="padding: 1rem;">
                    <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">Instrucciones:</h4>
                    <ul style="font-size: 0.875rem; color: #6b7280; padding-left: 1.25rem;">
                        <li>Complete todos los campos marcados con (*)</li>
                        <li>El código debe ser único en el sistema</li>
                        <li>La fecha de fin debe ser posterior al inicio</li>
                        <li>El presupuesto total es el monto disponible para la convocatoria</li>
                    </ul>

                    <hr style="margin: 1rem 0; border: none; border-top: 1px solid var(--border-color);">

                    <h4 style="font-size: 0.875rem; margin-bottom: 0.5rem;">Estados disponibles:</h4>
                    <ul style="font-size: 0.875rem; color: #6b7280; padding-left: 1.25rem;">
                        <li><strong>Planificada:</strong> Convocatoria en preparación</li>
                        <li><strong>Abierta:</strong> Recibiendo postulaciones</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>