<?php
/**
 * MÓDULO: CONVOCATORIAS - LISTAR
 * Lista todas las convocatorias con filtros
 * 
 * Base de datos: PostgreSQL (tabla: convocatorias)
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
    $filtro_estado = isset($_GET['estado']) ? sanitizar($_GET['estado']) : '';
    $filtro_anio = isset($_GET['anio']) ? sanitizar($_GET['anio']) : '';
    
    // Construir query con filtros
    $where = [];
    $params = [];
    
    if (!empty($filtro_estado)) {
        $where[] = "estado_convocatoria = :estado";
        $params[':estado'] = $filtro_estado;
    }
    
    if (!empty($filtro_anio)) {
        $where[] = "EXTRACT(YEAR FROM fecha_inicio_postulacion) = :anio";
        $params[':anio'] = $filtro_anio;
    }
    
    $where_clause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';
    
    // Consulta principal con estadísticas
    $query = "
        SELECT 
            c.id_convocatoria,
            c.codigo_convocatoria,
            c.nombre_convocatoria,
            c.descripcion_convocatoria,
            c.fecha_inicio_postulacion,
            c.fecha_fin_postulacion,
            c.monto_total_disponible,
            c.monto_minimo_proyecto,
            c.monto_maximo_proyecto,
            c.estado_convocatoria,
            COUNT(p.id_postulacion) as total_postulaciones,
            SUM(CASE WHEN p.estado_postulacion = 'APROBADA' THEN 1 ELSE 0 END) as postulaciones_aprobadas,
            SUM(p.monto_solicitado) as monto_total_solicitado
        FROM convocatorias c
        LEFT JOIN postulaciones p ON c.id_convocatoria = p.id_convocatoria
        $where_clause
        GROUP BY c.id_convocatoria, c.codigo_convocatoria, c.nombre_convocatoria, c.descripcion_convocatoria, 
                 c.fecha_inicio_postulacion, c.fecha_fin_postulacion, c.monto_total_disponible, 
                 c.monto_minimo_proyecto, c.monto_maximo_proyecto, c.estado_convocatoria
        ORDER BY c.fecha_inicio_postulacion DESC
    ";
    
    $stmt = $db->getConnection()->prepare($query);
    $stmt->execute($params);
    $convocatorias = $stmt->fetchAll();
    
    // Obtener años disponibles para filtro
    $anios = $db->query("
        SELECT DISTINCT EXTRACT(YEAR FROM fecha_inicio_postulacion) as anio 
        FROM convocatorias 
        ORDER BY anio DESC
    ");
    
    // Obtener estados únicos
    $estados = $db->query("
        SELECT DISTINCT estado_convocatoria 
        FROM convocatorias 
        ORDER BY estado_convocatoria
    ");
    
    // Estadísticas generales
    $stats = $db->query("
        SELECT 
            COUNT(*) as total,
            SUM(CASE WHEN estado_convocatoria = 'ABIERTA' THEN 1 ELSE 0 END) as abiertas,
            SUM(CASE WHEN estado_convocatoria = 'CERRADA' THEN 1 ELSE 0 END) as cerradas,
            SUM(monto_total_disponible) as presupuesto_total_global
        FROM convocatorias
    ");
    $estadisticas = $stats[0];
    
    $conexion_ok = true;
    
} catch (Exception $e) {
    $conexion_ok = false;
    $error_msg = $e->getMessage();
    $convocatorias = [];
    $anios = [];
    $estados = [];
    $estadisticas = [
        'total' => 0,
        'abiertas' => 0,
        'cerradas' => 0,
        'presupuesto_total_global' => 0
    ];
}

require_once '../../includes/header.php';
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
            <h1 style="margin: 0 0 0.5rem 0; color: var(--dark-color);">📢 Convocatorias FONDEP</h1>
            <p style="margin: 0; color: #6b7280;">
                Gestión de concursos de financiamiento para innovación educativa
            </p>
        </div>
        <a href="crear.php" class="btn btn-primary">
            ➕ Nueva Convocatoria
        </a>
    </div>

    <?php if (!$conexion_ok): ?>
    <div class="alert alert-danger">
        <strong>⚠️ Error de Conexión PostgreSQL:</strong> <?php echo htmlspecialchars($error_msg); ?>
    </div>
    <?php endif; ?>

    <!-- Estadísticas Rápidas -->
    <div class="row" style="margin-bottom: 2rem;">
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['total']); ?></div>
                <div class="stat-label">Total Convocatorias</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['abiertas']); ?></div>
                <div class="stat-label">Abiertas</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);">
                <div class="stat-value"><?php echo number_format($estadisticas['cerradas']); ?></div>
                <div class="stat-label">Cerradas</div>
            </div>
        </div>
        <div class="col-3">
            <div class="stat-card" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                <div class="stat-value"><?php echo formatearMonto($estadisticas['presupuesto_total_global']); ?></div>
                <div class="stat-label">Presupuesto Total</div>
            </div>
        </div>
    </div>

    <!-- Filtros -->
    <div class="card" style="margin-bottom: 1.5rem;">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <h3 style="margin: 0; font-size: 1rem;">🔍 Filtros de Búsqueda</h3>
        </div>
        <div style="padding: 1.5rem;">
            <form method="GET" action="listar.php">
                <div class="row">
                    <div class="col-6">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Estado</label>
                        <select name="estado" class="input">
                            <option value="">Todos los estados</option>
                            <?php foreach ($estados as $est): ?>
                            <option value="<?php echo $est['estado_convocatoria']; ?>"
                                    <?php echo $filtro_estado == $est['estado_convocatoria'] ? 'selected' : ''; ?>>
                                <?php echo traducirEstado($est['estado_convocatoria']); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-6">
                        <label style="display: block; margin-bottom: 0.5rem; font-weight: 500;">Año</label>
                        <select name="anio" class="input">
                            <option value="">Todos los años</option>
                            <?php foreach ($anios as $a): ?>
                            <option value="<?php echo $a['anio']; ?>"
                                    <?php echo $filtro_anio == $a['anio'] ? 'selected' : ''; ?>>
                                <?php echo $a['anio']; ?>
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

    <!-- Tabla de Convocatorias -->
    <div class="card">
        <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
            <div style="display: flex; justify-content: space-between; align-items: center;">
                <h3 style="margin: 0; font-size: 1rem;">Resultados</h3>
                <span class="badge badge-info"><?php echo count($convocatorias); ?> registros</span>
            </div>
        </div>
        <div style="padding: 0;">
            <?php if (count($convocatorias) > 0): ?>
            <table class="table">
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Título</th>
                        <th>Periodo</th>
                        <th>Presupuesto</th>
                        <th>Postulaciones</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($convocatorias as $conv): ?>
                    <tr>
                        <td>
                            <strong style="color: var(--primary-color);">
                                <?php echo htmlspecialchars($conv['codigo_convocatoria']); ?>
                            </strong>
                        </td>
                        <td>
                            <div style="max-width: 300px;">
                                <div style="font-weight: 500; margin-bottom: 0.25rem;">
                                    <?php echo htmlspecialchars($conv['nombre_convocatoria']); ?>
                                </div>
                                <small style="color: #6b7280;">
                                    <?php echo substr(htmlspecialchars($conv['descripcion_convocatoria']), 0, 80); ?>...
                                </small>
                            </div>
                        </td>
                        <td>
                            <div><?php echo formatearFecha($conv['fecha_inicio_postulacion']); ?></div>
                            <small style="color: #6b7280;">hasta</small>
                            <div><?php echo formatearFecha($conv['fecha_fin_postulacion']); ?></div>
                        </td>
                        <td>
                            <div style="font-weight: 600; margin-bottom: 0.25rem;">
                                <?php echo formatearMonto($conv['monto_total_disponible']); ?>
                            </div>
                            <small style="color: #6b7280;">
                                Por proyecto: <?php echo formatearMonto($conv['monto_minimo_proyecto']); ?> 
                                - <?php echo formatearMonto($conv['monto_maximo_proyecto']); ?>
                            </small>
                        </td>
                        <td>
                            <div>
                                <span style="font-weight: 600;"><?php echo $conv['total_postulaciones']; ?></span> total
                            </div>
                            <?php if ($conv['postulaciones_aprobadas'] > 0): ?>
                            <small style="color: var(--success-color);">
                                ✓ <?php echo $conv['postulaciones_aprobadas']; ?> aprobadas
                            </small>
                            <?php endif; ?>
                            <?php if ($conv['monto_total_solicitado'] > 0): ?>
                            <div>
                                <small style="color: #6b7280;">
                                    Solicitado: <?php echo formatearMonto($conv['monto_total_solicitado']); ?>
                                </small>
                            </div>
                            <?php endif; ?>
                        </td>
                        <td>
                            <span class="badge <?php echo obtenerClaseBadge($conv['estado_convocatoria']); ?>">
                                <?php echo traducirEstado($conv['estado_convocatoria']); ?>
                            </span>
                        </td>
                        <td>
                            <div style="display: flex; gap: 0.25rem;">
                                <a href="detalle.php?id=<?php echo $conv['id_convocatoria']; ?>" 
                                   class="btn btn-sm btn-info" 
                                   title="Ver Detalle">
                                    👁️
                                </a>
                                <a href="editar.php?id=<?php echo $conv['id_convocatoria']; ?>" 
                                   class="btn btn-sm btn-warning" 
                                   title="Editar">
                                    ✏️
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <div style="padding: 3rem; text-align: center;">
                <div style="font-size: 3rem; margin-bottom: 1rem;">📢</div>
                <h3 style="color: #6b7280; margin-bottom: 0.5rem;">No se encontraron convocatorias</h3>
                <p style="color: #9ca3af;">
                    <?php if (!empty($filtro_estado) || !empty($filtro_anio)): ?>
                        Intenta ajustar los filtros de búsqueda
                    <?php else: ?>
                        Aún no hay convocatorias registradas en el sistema
                    <?php endif; ?>
                </p>
            </div>
            <?php endif; ?>
        </div>
    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>