<?php
/**
 * Módulo: Listado de Postulaciones
 * Obtiene datos REALES desde PostgreSQL
 */

$base_url = rtrim(dirname(dirname($_SERVER['PHP_SELF'])), '/');

require_once '../../config/database.php';
require_once '../../config/Database.class.php';
require_once '../../includes/helpers.php';

// =====================================================
// OBTENER DATOS REALES DE POSTGRESQL
// =====================================================

try {
    $db = DatabasePGSQL::getInstance();
    
    // Filtros
    $filtro_codigo = isset($_GET['codigo']) ? sanitizar($_GET['codigo']) : '';
    $filtro_estado = isset($_GET['estado']) ? sanitizar($_GET['estado']) : '';
    $filtro_region = isset($_GET['region']) ? sanitizar($_GET['region']) : '';
    
    // Construir query con filtros
    $where = [];
    $params = [];
    
    if (!empty($filtro_codigo)) {
        $where[] = "p.codigo_postulacion ILIKE :codigo";
        $params[':codigo'] = '%' . $filtro_codigo . '%';
    }
    
    if (!empty($filtro_estado)) {
        $where[] = "p.estado_postulacion = :estado";
        $params[':estado'] = $filtro_estado;
    }
    
    if (!empty($filtro_region)) {
        $where[] = "r.id_region = :region";
        $params[':region'] = $filtro_region;
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Consulta principal
    $query = "
        SELECT 
            p.id_postulacion,
            p.codigo_postulacion,
            p.titulo_proyecto,
            p.estado_postulacion,
            p.monto_solicitado,
            p.fecha_postulacion,
            e.nombre_escuela,
            e.codigo_modular,
            r.nombre_region,
            c.nombre_convocatoria as convocatoria_titulo,
            c.codigo_convocatoria
        FROM postulaciones p
        INNER JOIN escuelas e ON p.id_escuela = e.id_escuela
        INNER JOIN regiones r ON e.id_region = r.id_region
        INNER JOIN convocatorias c ON p.id_convocatoria = c.id_convocatoria
        $where_clause
        ORDER BY p.fecha_postulacion DESC
    ";
    
    $stmt = $db->getConnection()->prepare($query);
    $stmt->execute($params);
    $postulaciones = $stmt->fetchAll();
    
    // Obtener regiones para filtro
    $regiones = $db->query("
        SELECT id_region, nombre_region 
        FROM regiones 
        WHERE activo = TRUE 
        ORDER BY nombre_region
    ");
    
    // Obtener estados únicos
    $estados = $db->query("
        SELECT DISTINCT estado_postulacion 
        FROM postulaciones 
        ORDER BY estado_postulacion
    ");
    
    $conexion_ok = true;
    
} catch (Exception $e) {
    $conexion_ok = false;
    $error_msg = $e->getMessage();
    $postulaciones = [];
    $regiones = [];
    $estados = [];
}

require_once '../../includes/header.php';
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
            <h1 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">📋 Postulaciones</h1>
            <p style="margin: 0; color: #6b7280;">
                Gestión de proyectos presentados a convocatorias FONDEP
            </p>
        </div>
        <a href="crear.php" class="btn btn-primary">
            ➕ Nueva Postulación
        </a>
    </div>

    <?php if (!$conexion_ok): ?>
    <div class="alert alert-danger">
        <strong>⚠️ Error de Conexión PostgreSQL:</strong> <?php echo htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1rem;">🔍 Filtros de Búsqueda</h3>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" action="listar.php">
                <div class="row">
                    <div class="col-4">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Código</label>
                        <input 
                            type="text" 
                            name="codigo" 
                            class="input" 
                            placeholder="Ej: POST-2025-00001"
                            value="<?php echo htmlspecialchars($filtro_codigo); ?>"
                        >
                    </div>
                    <div class="col-4">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Estado</label>
                        <select name="estado" class="input">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $est): ?>
                            <option value="<?php echo $est['estado_postulacion']; ?>"
                                    <?php echo $filtro_estado == $est['estado_postulacion'] ? 'selected' : ''; ?>>
                                <?php echo traducirEstado($est['estado_postulacion']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-4">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Región</label>
                        <select name="region" class="input">
                            <option value="">Todas las regiones</option>
                            <?php foreach ($regiones as $reg): ?>
                            <option value="<?php echo $reg['id_region']; ?>"
                                    <?php echo $filtro_region == $reg['id_region'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($reg['nombre_region']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div style="margin-top: 1rem; display: flex; gap: 0.5rem;">
                    <button type="submit" class="btn btn-primary">Buscar</button>
                    <a href="listar.php" class="btn btn-secondary">Limpiar Filtros</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Tabla de Postulaciones -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1rem;">Resultados</h3>
                <span class="badge badge-info"><?php echo count($postulaciones); ?> registros</span>
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
                        <th>Convocatoria</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($postulaciones as $post): ?>
                    <tr>
                        <td>
                            <strong style="color: var(--primary-color);">
                                <?php echo htmlspecialchars($post['codigo_postulacion']); ?>
                            </strong>
                        </td>
                        <td>
                            <div style="max-width: 250px;">
                                <div style="font-weight: 500; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($post['titulo_proyecto']); ?>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="max-width: 200px;">
                                <?php echo htmlspecialchars($post['nombre_escuela']); ?>
                            </div>
                            <small style="color: #6b7280;">
                                Cód. <?php echo $post['codigo_modular']; ?>
                            </small>
                        </td>
                        <td><?php echo htmlspecialchars($post['nombre_region']); ?></td>
                        <td>
                            <div style="max-width: 180px;">
                                <?php echo htmlspecialchars($post['convocatoria_titulo']); ?>
                            </div>
                            <small style="color: #6b7280;">
                                <?php echo $post['codigo_convocatoria']; ?>
                            </small>
                        </td>
                        <td><?php echo formatearMonto($post['monto_solicitado']); ?></td>
                        <td>
                            <span class="badge <?php echo obtenerClaseBadge($post['estado_postulacion']); ?>">
                                <?php echo traducirEstado($post['estado_postulacion']); ?>
                            </span>
                        </td>
                        <td><?php echo formatearFecha($post['fecha_postulacion']); ?></td>
                        <td>
                            <a href="detalle.php?codigo=<?php echo urlencode($post['codigo_postulacion']); ?>" 
                               class="btn btn-sm btn-info">
                                Ver
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📋</div>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No se encontraron postulaciones</h3>
                <p style="color: #9ca3af;">
                    <?php if (!empty($filtro_codigo) || !empty($filtro_estado) || !empty($filtro_region)): ?>
                        Intenta ajustar los filtros de búsqueda
                    <?php else: ?>
                        Aún no hay postulaciones registradas en el sistema
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>