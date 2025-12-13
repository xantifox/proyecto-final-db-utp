<?php
/**
 * Dashboard Principal del Sistema FONDEP
 * VERSION DE PRUEBA CON DATOS SIMULADOS
 */

$page_title = "Dashboard";
require_once 'includes/header.php';

// =====================================================
// DATOS SIMULADOS - PostgreSQL
// =====================================================
$totalConvocatorias = 8;
$totalPostulaciones = 247;

$postulacionesPorEstado = [
    ['estado' => 'EN_REVISION', 'cantidad' => 89],
    ['estado' => 'EN_EVALUACION', 'cantidad' => 73],
    ['estado' => 'APROBADA', 'cantidad' => 52],
    ['estado' => 'RECHAZADA', 'cantidad' => 21],
    ['estado' => 'OBSERVADA', 'cantidad' => 12]
];

$convocatoriaActiva = [
    [
        'nombre' => 'Innovación Educativa 2025 - Primera Convocatoria',
        'codigo' => 'FONDEP-2025-01',
        'fecha_inicio' => '2025-01-15',
        'fecha_cierre' => '2025-03-30',
        'monto_maximo' => 50000.00,
        'estado' => 'ACTIVA'
    ]
];

$topRegiones = [
    ['region' => 'Lima', 'total' => 78],
    ['region' => 'Arequipa', 'total' => 34],
    ['region' => 'La Libertad', 'total' => 29],
    ['region' => 'Cusco', 'total' => 24],
    ['region' => 'Piura', 'total' => 18]
];

// =====================================================
// DATOS SIMULADOS - MongoDB
// =====================================================
$totalPropuestas = 247;
$totalEvaluaciones = 156;

$propuestasPorArea = [
    ['_id' => 'Tecnología Educativa', 'cantidad' => 89],
    ['_id' => 'Metodologías Innovadoras', 'cantidad' => 67],
    ['_id' => 'Inclusión Educativa', 'cantidad' => 43],
    ['_id' => 'Educación Rural', 'cantidad' => 28],
    ['_id' => 'Gestión Pedagógica', 'cantidad' => 20]
];

// =====================================================
// DATOS SIMULADOS - Cassandra
// =====================================================
$totalEventos = 1247;
$eventosHoy = 83;

?>

<div class="container">
    <h1 class="mb-4">Dashboard - Sistema FONDEP</h1>
    
    <div class="alert alert-info">
        <strong>🔍 Modo de Prueba:</strong> Visualizando con datos simulados. 
        Las conexiones a bases de datos están deshabilitadas temporalmente.
    </div>
    
    <!-- ESTADÍSTICAS PRINCIPALES -->
    <div class="row">
        <div class="col-3">
            <div class="stat-card">
                <div class="stat-card-title">Total Convocatorias</div>
                <div class="stat-card-value"><?php echo $totalConvocatorias; ?></div>
                <div class="stat-card-subtitle">Registradas en el sistema</div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="stat-card success">
                <div class="stat-card-title">Total Postulaciones</div>
                <div class="stat-card-value"><?php echo $totalPostulaciones; ?></div>
                <div class="stat-card-subtitle">Escuelas participantes</div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="stat-card warning">
                <div class="stat-card-title">Total Propuestas</div>
                <div class="stat-card-value"><?php echo $totalPropuestas; ?></div>
                <div class="stat-card-subtitle">Documentos en MongoDB</div>
            </div>
        </div>
        
        <div class="col-3">
            <div class="stat-card danger">
                <div class="stat-card-title">Eventos del Sistema</div>
                <div class="stat-card-value"><?php echo number_format($totalEventos); ?></div>
                <div class="stat-card-subtitle">Hoy: <?php echo $eventosHoy; ?> eventos</div>
            </div>
        </div>
    </div>
    
    <!-- CONVOCATORIA ACTIVA -->
    <?php if (!empty($convocatoriaActiva)): ?>
    <div class="card">
        <div class="card-header">
            <h2>Convocatoria Activa</h2>
        </div>
        <div class="card-body">
            <h3><?php echo htmlspecialchars($convocatoriaActiva[0]['nombre']); ?></h3>
            <p><strong>Código:</strong> <?php echo htmlspecialchars($convocatoriaActiva[0]['codigo']); ?></p>
            <p><strong>Periodo:</strong> 
                <?php echo date('d/m/Y', strtotime($convocatoriaActiva[0]['fecha_inicio'])); ?> - 
                <?php echo date('d/m/Y', strtotime($convocatoriaActiva[0]['fecha_cierre'])); ?>
            </p>
            <p><strong>Monto Máximo:</strong> S/ <?php echo number_format($convocatoriaActiva[0]['monto_maximo'], 2); ?></p>
            <span class="badge badge-success"><?php echo $convocatoriaActiva[0]['estado']; ?></span>
        </div>
    </div>
    <?php endif; ?>
    
    <!-- FILAS DE INFORMACIÓN -->
    <div class="row">
        <!-- POSTULACIONES POR ESTADO -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3>Postulaciones por Estado (PostgreSQL)</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Estado</th>
                                    <th class="text-right">Cantidad</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($postulacionesPorEstado as $estado): ?>
                                <tr>
                                    <td>
                                        <?php 
                                        $badgeClass = 'badge-secondary';
                                        if ($estado['estado'] == 'APROBADA') $badgeClass = 'badge-success';
                                        elseif ($estado['estado'] == 'EN_EVALUACION') $badgeClass = 'badge-warning';
                                        elseif ($estado['estado'] == 'RECHAZADA') $badgeClass = 'badge-danger';
                                        elseif ($estado['estado'] == 'EN_REVISION') $badgeClass = 'badge-primary';
                                        ?>
                                        <span class="badge <?php echo $badgeClass; ?>"><?php echo $estado['estado']; ?></span>
                                    </td>
                                    <td class="text-right fw-bold"><?php echo $estado['cantidad']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                                <tr style="background-color: #f9fafb; font-weight: bold;">
                                    <td>TOTAL</td>
                                    <td class="text-right"><?php echo array_sum(array_column($postulacionesPorEstado, 'cantidad')); ?></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- TOP REGIONES -->
        <div class="col-6">
            <div class="card">
                <div class="card-header">
                    <h3>Top 5 Regiones - Postulaciones</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>Región</th>
                                    <th class="text-right">Postulaciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $posicion = 1; ?>
                                <?php foreach ($topRegiones as $region): ?>
                                <tr>
                                    <td><?php echo $posicion++; ?></td>
                                    <td><?php echo htmlspecialchars($region['region']); ?></td>
                                    <td class="text-right fw-bold"><?php echo $region['total']; ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- PROPUESTAS POR ÁREA TEMÁTICA (MongoDB) -->
    <div class="card">
        <div class="card-header">
            <h3>Propuestas por Área Temática (MongoDB)</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Área Temática</th>
                            <th class="text-right">Propuestas</th>
                            <th class="text-right">Porcentaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalProp = array_sum(array_column($propuestasPorArea, 'cantidad'));
                        foreach ($propuestasPorArea as $area): 
                            $porcentaje = ($area['cantidad'] / $totalProp) * 100;
                        ?>
                        <tr>
                            <td><?php echo htmlspecialchars($area['_id']); ?></td>
                            <td class="text-right fw-bold"><?php echo $area['cantidad']; ?></td>
                            <td class="text-right"><?php echo number_format($porcentaje, 1); ?>%</td>
                        </tr>
                        <?php endforeach; ?>
                        <tr style="background-color: #f9fafb; font-weight: bold;">
                            <td>TOTAL</td>
                            <td class="text-right"><?php echo $totalProp; ?></td>
                            <td class="text-right">100%</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- INFORMACIÓN DE CONEXIONES -->
    <div class="card">
        <div class="card-header">
            <h3>Estado de Bases de Datos</h3>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-4">
                    <div style="padding: 1rem; background-color: #dbeafe; border-radius: 6px;">
                        <h4 style="margin-bottom: 0.5rem; color: #1e40af;">PostgreSQL</h4>
                        <p style="margin: 0; font-size: 0.875rem;">
                            <strong>Estado:</strong> <span class="badge badge-warning">Deshabilitado</span><br>
                            <strong>Tablas:</strong> 10 tablas normalizadas<br>
                            <strong>Datos:</strong> <?php echo $totalPostulaciones; ?> registros simulados
                        </p>
                    </div>
                </div>
                
                <div class="col-4">
                    <div style="padding: 1rem; background-color: #d1fae5; border-radius: 6px;">
                        <h4 style="margin-bottom: 0.5rem; color: #065f46;">MongoDB</h4>
                        <p style="margin: 0; font-size: 0.875rem;">
                            <strong>Estado:</strong> <span class="badge badge-warning">Deshabilitado</span><br>
                            <strong>Colecciones:</strong> 4 colecciones<br>
                            <strong>Documentos:</strong> <?php echo $totalPropuestas; ?> documentos simulados
                        </p>
                    </div>
                </div>
                
                <div class="col-4">
                    <div style="padding: 1rem; background-color: #fee2e2; border-radius: 6px;">
                        <h4 style="margin-bottom: 0.5rem; color: #991b1b;">Cassandra</h4>
                        <p style="margin: 0; font-size: 0.875rem;">
                            <strong>Estado:</strong> <span class="badge badge-warning">Deshabilitado</span><br>
                            <strong>Keyspace:</strong> fondep_eventos<br>
                            <strong>Eventos:</strong> <?php echo number_format($totalEventos); ?> eventos simulados
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
</div>

<?php require_once 'includes/footer.php'; ?>