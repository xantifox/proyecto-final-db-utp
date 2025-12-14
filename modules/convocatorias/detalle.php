<?php
/**
 * MÓDULO: CONVOCATORIAS - DETALLE
 * Muestra información completa de una convocatoria
 * 
 * Base de datos: PostgreSQL
 */

$base_url = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');

require_once '../../config/database.php';
require_once '../../config/Database.class.php';
require_once '../../includes/helpers.php';

// =====================================================
// OBTENER DATOS REALES DE POSTGRESQL
// =====================================================

$convocatoria_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($convocatoria_id <= 0) {
    header('Location: listar.php');
    exit;
}

try {
    $db = DatabasePGSQL::getInstance();
    
    // Obtener datos de la convocatoria
    $query_conv = "
        SELECT * FROM convocatorias 
        WHERE id_convocatoria = :id
    ";
    
    $stmt = $db->getConnection()->prepare($query_conv);
    $stmt->execute([':id' => $convocatoria_id]);
    $convocatoria = $stmt->fetch();
    
    if (!$convocatoria) {
        header('Location: listar.php');
        exit;
    }
    
    // Obtener requisitos de la convocatoria
    $requisitos = $db->query("
        SELECT * FROM requisitos_convocatoria 
        WHERE id_convocatoria = :id
        ORDER BY orden_presentacion
    ", [':id' => $convocatoria_id]);
    
    // Obtener postulaciones de esta convocatoria
    $postulaciones = $db->query("
        SELECT 
            p.*,
            e.nombre_escuela,
            e.codigo_modular,
            r.nombre_region
        FROM postulaciones p
        INNER JOIN escuelas e ON p.id_escuela = e.id_escuela
        INNER JOIN regiones r ON e.id_region = r.id_region
        WHERE p.id_convocatoria = :id
        ORDER BY p.fecha_postulacion DESC
    ", [':id' => $convocatoria_id]);
    
    // Estadísticas de postulaciones
    $stats_post = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado_postulacion = 'APROBADA' THEN 1 ELSE 0 END) as aprobadas,
            SUM(CASE WHEN estado_postulacion = 'EN_EVALUACION' THEN 1 ELSE 0 END) as en_evaluacion,
            SUM(CASE WHEN estado_postulacion = 'RECHAZADA' THEN 1 ELSE 0 END) as rechazadas,
            SUM(monto_solicitado) as monto_total_solicitado,
            AVG(monto_solicitado) as monto_promedio
        FROM postulaciones
        WHERE id_convocatoria = :id
    ", [':id' => $convocatoria_id]);
    
    $stats = $stats_post[0];
    
    // Distribución por regiones
    $regiones_dist = $db->query("
        SELECT 
            r.nombre_region,
            COUNT(p.id_postulacion) as total_postulaciones
        FROM regiones r
        LEFT JOIN escuelas e ON r.id_region = e.id_region
        LEFT JOIN postulaciones p ON e.id_escuela = p.id_escuela AND p.id_convocatoria = :id
        WHERE r.activo = TRUE
        GROUP BY r.id_region, r.nombre_region
        HAVING COUNT(p.id_postulacion) > 0
        ORDER BY total_postulaciones DESC
        LIMIT 5
    ", [':id' => $convocatoria_id]);
    
    $conexion_ok = true;
    
} catch (Exception $e) {
    $conexion_ok = false;
    $error_msg = $e->getMessage();
}

require_once '../../includes/header.php';
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Convocatorias</a> / 
        <span><?php echo htmlspecialchars($convocatoria['codigo_convocatoria']); ?></span>
    </div>

    <?php if (!$conexion_ok): ?>
    <div class="alert alert-danger">
        <strong>⚠️ Error:</strong> <?php echo htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>

    <!-- Header con acciones -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div style="flex: 1;">
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h1 style="margin: 0; color: var(--dark-color);">
                    <?php echo htmlspecialchars($convocatoria['nombre_convocatoria']); ?>
                </h1>
                <span class="badge <?php echo obtenerClaseBadge($convocatoria['estado_convocatoria']); ?>" 
                      style="font-size: 0.875rem;">
                    <?php echo traducirEstado($convocatoria['estado_convocatoria']); ?>
                </span>
            </div>
            <p style="margin: 0; color: #6b7280; font-size: 1.125rem;">
                <?php echo htmlspecialchars($convocatoria['codigo_convocatoria']); ?>
            </p>
        </div>
        <div style="display: flex; gap: 0.5rem;">
            <a href="editar.php?id=<?php echo $convocatoria['id_convocatoria']; ?>" class="btn btn-warning">
                ✏️ Editar
            </a>
            <a href="listar.php" class="btn btn-secondary">
                ← Volver
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Columna Principal -->
        <div class="col-8">
            
            <!-- Descripción -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📄 Descripción</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <p style="margin: 0; line-height: 1.8; color: #374151;">
                        <?php echo nl2br(htmlspecialchars($convocatoria['descripcion_convocatoria'])); ?>
                    </p>
                </div>
            </div>

            <!-- Requisitos -->
            <?php if (count($requisitos) > 0): ?>
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1.125rem;">📋 Requisitos</h3>
                </div>
                <div style="padding: 1.5rem;">
                    <ol style="margin: 0; padding-left: 1.5rem; line-height: 2;">
                        <?php foreach ($requisitos as $req): ?>
                        <li style="margin-bottom: 0.5rem;">
                            <strong><?php echo htmlspecialchars($req['tipo_requisito']); ?></strong>
                            <?php if (!empty($req['descripcion_requisito'])): ?>
                            <br>
                            <small style="color: #6b7280;">
                                <?php echo htmlspecialchars($req['descripcion_requisito']); ?>
                            </small>
                            <?php endif; ?>
                            <?php if ($req['obligatorio']): ?>
                            <span class="badge badge-danger" style="margin-left: 0.5rem; font-size: 0.75rem;">
                                Obligatorio
                            </span>
                            <?php endif; ?>
                        </li>
                        <?php endforeach; ?>
                    </ol>
                </div>
            </div>
            <?php endif; ?>

            <!-- Postulaciones -->
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <div style="display: flex; justify-content: space-between; align-items: center;">
                        <h3 style="margin: 0; font-size: 1.125rem;">📝 Postulaciones Presentadas</h3>
                        <span class="badge badge-info"><?php echo count($postulaciones); ?> total</span>
                    </div>
                </div>
                <div style="padding: 0;">
                    <?php if (count($postulaciones) > 0): ?>
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Código</th>
                                <th>Proyecto</th>
                                <th>Escuela</th>
                                <th>Región</th>
                                <th>Monto</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($postulaciones as $post): ?>
                            <tr>
                                <td>
                                    <a href="../postulaciones/detalle.php?codigo=<?php echo urlencode($post['codigo_postulacion']); ?>"
                                       style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars($post['codigo_postulacion']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                        <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                    </div>
                                </td>
                                <td>
                                    <div style="max-width: 180px;">
                                        <?php echo htmlspecialchars($post['nombre_escuela']); ?>
                                    </div>
                                    <small style="color: #6b7280;">
                                        <?php echo $post['codigo_modular']; ?>
                                    </small>
                                </td>
                                <td><?php echo htmlspecialchars($post['nombre_region']); ?></td>
                                <td><?php echo formatearMonto($post['monto_solicitado']); ?></td>
                                <td>
                                    <span class="badge <?php echo obtenerClaseBadge($post['estado_postulacion']); ?>">
                                        <?php echo traducirEstado($post['estado_postulacion']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #6b7280;">
                        <p>Aún no hay postulaciones presentadas a esta convocatoria</p>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

        </div>

        <!-- Sidebar -->
        <div class="col-4">
            
            <!-- Información Clave -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">📊 Información Clave</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Periodo</div>
                        <div style="font-weight: 600;">
                            <?php echo formatearFecha($convocatoria['fecha_inicio_postulacion']); ?>
                        </div>
                        <div style="font-size: 0.875rem; color: #6b7280;">hasta</div>
                        <div style="font-weight: 600;">
                            <?php echo formatearFecha($convocatoria['fecha_fin_postulacion']); ?>
                        </div>
                        <div style="margin-top: 0.5rem;">
                            <?php
                            $dias_restantes = diasTranscurridos(date('Y-m-d'), $convocatoria['fecha_fin_postulacion']);
                            if ($convocatoria['estado_convocatoria'] == 'ABIERTA' && $dias_restantes >= 0):
                            ?>
                            <span class="badge badge-warning" style="font-size: 0.75rem;">
                                <?php echo $dias_restantes; ?> días restantes
                            </span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Presupuesto Total</div>
                        <div style="font-weight: 600; font-size: 1.25rem; color: var(--primary-color);">
                            <?php echo formatearMonto($convocatoria['monto_total_disponible']); ?>
                        </div>
                    </div>

                    <div>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Monto por Proyecto</div>
                        <div style="font-weight: 500;">
                            Min: <?php echo formatearMonto($convocatoria['monto_minimo_proyecto']); ?>
                        </div>
                        <div style="font-weight: 500;">
                            Max: <?php echo formatearMonto($convocatoria['monto_maximo_proyecto']); ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Estadísticas de Postulaciones -->
            <div class="card" style="margin-bottom: 1.5rem;">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">📈 Estadísticas</h3>
                </div>
                <div style="padding: 1rem;">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: #6b7280;">Total postulaciones:</span>
                        <strong><?php echo $stats['total']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: var(--success-color);">✓ Aprobadas:</span>
                        <strong><?php echo $stats['aprobadas']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.75rem;">
                        <span style="color: var(--warning-color);">⏳ En evaluación:</span>
                        <strong><?php echo $stats['en_evaluacion']; ?></strong>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem;">
                        <span style="color: var(--danger-color);">✗ Rechazadas:</span>
                        <strong><?php echo $stats['rechazadas']; ?></strong>
                    </div>
                    
                    <div style="padding-top: 1rem; border-top: 1px solid var(--border-color);">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">Monto Total Solicitado</div>
                        <div style="font-weight: 600; color: var(--primary-color);">
                            <?php echo formatearMonto($stats['monto_total_solicitado']); ?>
                        </div>
                        <?php if ($stats['total'] > 0): ?>
                        <div style="font-size: 0.875rem; color: #6b7280; margin-top: 0.5rem;">
                            Promedio: <?php echo formatearMonto($stats['monto_promedio']); ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Top 5 Regiones -->
            <?php if (count($regiones_dist) > 0): ?>
            <div class="card">
                <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                    <h3 style="margin: 0; font-size: 1rem;">🗺️ Top Regiones</h3>
                </div>
                <div style="padding: 1rem;">
                    <?php foreach ($regiones_dist as $index => $reg): ?>
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.75rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <span style="
                                display: inline-block;
                                width: 1.5rem;
                                height: 1.5rem;
                                background: linear-gradient(135deg, #667eea, #764ba2);
                                color: white;
                                border-radius: 50%;
                                text-align: center;
                                line-height: 1.5rem;
                                font-size: 0.75rem;
                                font-weight: bold;
                            ">
                                <?php echo $index + 1; ?>
                            </span>
                            <span><?php echo htmlspecialchars($reg['nombre_region']); ?></span>
                        </div>
                        <strong><?php echo $reg['total_postulaciones']; ?></strong>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
        
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>