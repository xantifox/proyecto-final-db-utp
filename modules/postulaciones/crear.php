<?php
/**
 * MÓDULO: POSTULACIONES - CREAR
 * Formulario para registrar nueva postulación a una convocatoria
 * 
 * Integración de datos:
 * - PostgreSQL: INSERT en tabla postulaciones
 * - MongoDB: INSERT documento propuesta completa
 * - Cassandra: Registro de evento CREATE
 */

$page_title = "Nueva Postulación";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener convocatorias abiertas
$convocatorias = array_filter(obtenerConvocatorias(), fn($c) => $c['estado'] == 'ABIERTA');
$escuelas = obtenerEscuelas();
$regiones = obtenerRegiones();

// Procesar formulario
$mensaje = '';
$tipo_mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errores = [];
    
    if (empty($_POST['convocatoria_id'])) $errores[] = 'Debe seleccionar una convocatoria';
    if (empty($_POST['escuela_id'])) $errores[] = 'Debe seleccionar una escuela';
    if (empty($_POST['titulo_proyecto'])) $errores[] = 'El título del proyecto es obligatorio';
    if (empty($_POST['monto_solicitado'])) $errores[] = 'El monto solicitado es obligatorio';
    
    // Validar monto máximo
    if (!empty($_POST['convocatoria_id']) && !empty($_POST['monto_solicitado'])) {
        $conv = obtenerConvocatoriaPorId($_POST['convocatoria_id']);
        if ($conv && $_POST['monto_solicitado'] > $conv['monto_max_proyecto']) {
            $errores[] = 'El monto supera el máximo permitido: ' . formatearMonto($conv['monto_max_proyecto']);
        }
    }
    
    if (empty($errores)) {
        // Generar código
        $codigo = 'POST-2024-' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
        
        $mensaje = "✅ Postulación creada exitosamente con código: <strong>{$codigo}</strong><br>";
        $mensaje .= "Se registraron datos en PostgreSQL y MongoDB.<br>";
        $mensaje .= "Evento de auditoría guardado en Cassandra.";
        $tipo_mensaje = 'success';
        
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
        <a href="listar.php">Postulaciones</a> / 
        <span>Nueva Postulación</span>
    </div>

    <!-- Header -->
    <div style="margin-bottom: 2rem;">
        <h1 style="margin: 0; color: var(--dark-color);">➕ Nueva Postulación</h1>
        <p style="margin: 0.5rem 0 0 0; color: #6b7280;">
            Complete el formulario para postular a una convocatoria FONDEP
        </p>
    </div>

    <!-- Mensajes -->
    <?php if ($mensaje): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?>" style="margin-bottom: 1.5rem;">
            <?php echo $mensaje; ?>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="row">
            
            <!-- Formulario Principal -->
            <div class="col-8">
                
                <!-- Sección 1: Datos de Postulación -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1.125rem;">1️⃣ Datos de Postulación</h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        
                        <!-- Convocatoria -->
                        <div class="form-group">
                            <label class="form-label">
                                Convocatoria <span style="color: red;">*</span>
                            </label>
                            <select name="convocatoria_id" class="form-control" required id="convocatoria">
                                <option value="">Seleccione una convocatoria...</option>
                                <?php foreach ($convocatorias as $conv): ?>
                                    <option value="<?php echo $conv['id']; ?>"
                                            data-presupuesto="<?php echo $conv['presupuesto_total']; ?>"
                                            data-monto-max="<?php echo $conv['monto_max_proyecto']; ?>"
                                            <?php echo ($_POST['convocatoria_id'] ?? '') == $conv['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($conv['codigo'] . ' - ' . $conv['titulo']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <small class="form-help" id="conv-info"></small>
                        </div>

                        <!-- Escuela -->
                        <div class="form-group">
                            <label class="form-label">
                                Institución Educativa <span style="color: red;">*</span>
                            </label>
                            <select name="escuela_id" class="form-control" required>
                                <option value="">Seleccione una escuela...</option>
                                <?php foreach ($escuelas as $esc): ?>
                                    <option value="<?php echo $esc['id']; ?>"
                                            <?php echo ($_POST['escuela_id'] ?? '') == $esc['id'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($esc['codigo'] . ' - ' . $esc['nombre'] . ' (' . obtenerNombreRegion($esc['region_id']) . ')'); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                    </div>
                </div>

                <!-- Sección 2: Datos del Proyecto -->
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1.125rem;">2️⃣ Información del Proyecto</h3>
                    </div>
                    <div style="padding: 1.5rem;">
                        
                        <!-- Título -->
                        <div class="form-group">
                            <label class="form-label">
                                Título del Proyecto <span style="color: red;">*</span>
                            </label>
                            <input type="text" 
                                   name="titulo_proyecto" 
                                   class="form-control"
                                   placeholder="Ej: Implementación de Laboratorio de Robótica Educativa"
                                   value="<?php echo $_POST['titulo_proyecto'] ?? ''; ?>"
                                   required>
                        </div>

                        <!-- Descripción -->
                        <div class="form-group">
                            <label class="form-label">Descripción del Proyecto</label>
                            <textarea name="descripcion" 
                                      class="form-control" 
                                      rows="5"
                                      placeholder="Describa brevemente los objetivos, metodología y resultados esperados del proyecto..."><?php echo $_POST['descripcion'] ?? ''; ?></textarea>
                            <small class="form-help">Esta información se guardará en MongoDB como documento completo</small>
                        </div>

                        <!-- Monto -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">
                                        Monto Solicitado (S/) <span style="color: red;">*</span>
                                    </label>
                                    <input type="number" 
                                           name="monto_solicitado" 
                                           class="form-control" 
                                           step="0.01"
                                           min="0"
                                           placeholder="35000.00"
                                           value="<?php echo $_POST['monto_solicitado'] ?? ''; ?>"
                                           id="monto"
                                           required>
                                    <small class="form-help" id="monto-warning"></small>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Duración (meses)</label>
                                    <input type="number" 
                                           name="duracion_meses" 
                                           class="form-control" 
                                           min="1"
                                           max="24"
                                           placeholder="12"
                                           value="<?php echo $_POST['duracion_meses'] ?? ''; ?>">
                                </div>
                            </div>
                        </div>

                        <!-- Beneficiarios -->
                        <div class="row">
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Número de Beneficiarios Directos</label>
                                    <input type="number" 
                                           name="beneficiarios_directos" 
                                           class="form-control" 
                                           min="0"
                                           placeholder="250"
                                           value="<?php echo $_POST['beneficiarios_directos'] ?? ''; ?>">
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="form-group">
                                    <label class="form-label">Área Temática Principal</label>
                                    <select name="area_tematica" class="form-control">
                                        <option value="">Seleccione...</option>
                                        <option value="Ciencias">Ciencias</option>
                                        <option value="Matemáticas">Matemáticas</option>
                                        <option value="Tecnología">Tecnología</option>
                                        <option value="Lenguaje">Lenguaje</option>
                                        <option value="Medio Ambiente">Medio Ambiente</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Objetivos -->
                        <div class="form-group">
                            <label class="form-label">Objetivos Principales</label>
                            <textarea name="objetivos" 
                                      class="form-control" 
                                      rows="4"
                                      placeholder="Liste los objetivos principales del proyecto (uno por línea)"><?php echo $_POST['objetivos'] ?? ''; ?></textarea>
                        </div>

                    </div>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">
                        💾 Enviar Postulación
                    </button>
                    <a href="listar.php" class="btn btn-secondary" style="flex: 1;">
                        ❌ Cancelar
                    </a>
                </div>

            </div>

            <!-- Panel de Ayuda -->
            <div class="col-4">
                
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #eff6ff;">
                        <h3 style="margin: 0; font-size: 1rem; color: var(--primary-color);">💡 Instrucciones</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <ol style="font-size: 0.875rem; color: #6b7280; padding-left: 1.25rem;">
                            <li style="margin-bottom: 0.5rem;">Seleccione una convocatoria <strong>ABIERTA</strong></li>
                            <li style="margin-bottom: 0.5rem;">Complete los datos de su institución</li>
                            <li style="margin-bottom: 0.5rem;">Describa detalladamente su proyecto</li>
                            <li style="margin-bottom: 0.5rem;">Verifique que el monto no exceda el máximo permitido</li>
                            <li>Revise toda la información antes de enviar</li>
                        </ol>
                    </div>
                </div>

                <div class="alert alert-info">
                    <strong>📊 Integración de Datos:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                        <li><strong>PostgreSQL:</strong> Datos básicos de postulación</li>
                        <li><strong>MongoDB:</strong> Propuesta completa con objetivos y metodología</li>
                        <li><strong>Cassandra:</strong> Evento de creación para auditoría</li>
                    </ul>
                </div>

            </div>

        </div>
    </form>

</div>

<script>
// Validación dinámica de monto máximo
document.getElementById('convocatoria')?.addEventListener('change', function() {
    const option = this.options[this.selectedIndex];
    const montoMax = parseFloat(option.getAttribute('data-monto-max'));
    const presupuesto = parseFloat(option.getAttribute('data-presupuesto'));
    
    if (montoMax) {
        document.getElementById('conv-info').textContent = 
            `Monto máximo por proyecto: S/ ${montoMax.toLocaleString('es-PE', {minimumFractionDigits: 2})}`;
    }
});

document.getElementById('monto')?.addEventListener('input', function() {
    const convSelect = document.getElementById('convocatoria');
    const option = convSelect.options[convSelect.selectedIndex];
    const montoMax = parseFloat(option.getAttribute('data-monto-max'));
    const montoInput = parseFloat(this.value);
    
    if (montoInput > montoMax) {
        document.getElementById('monto-warning').innerHTML = 
            '<span style="color: var(--danger-color);">⚠️ El monto excede el máximo permitido</span>';
    } else {
        document.getElementById('monto-warning').textContent = '';
    }
});
</script>

<?php require_once '../../includes/footer.php'; ?>