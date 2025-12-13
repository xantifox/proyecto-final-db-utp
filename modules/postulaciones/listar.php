<?php
/**
 * Listado de Postulaciones
 * VERSION DE PRUEBA CON DATOS SIMULADOS
 */

$page_title = "Listado de Postulaciones";
require_once '../../includes/header.php';

// Datos simulados de postulaciones
$postulaciones = [
    [
        'id' => 1,
        'codigo' => 'POST-2025-001',
        'escuela' => 'IE 5051 Virgen de Fátima',
        'departamento' => 'Lima',
        'region' => 'Lima',
        'titulo_proyecto' => 'Implementación de laboratorio de robótica educativa',
        'monto_solicitado' => 45000.00,
        'fecha_postulacion' => '2025-01-20',
        'estado' => 'EN_EVALUACION'
    ],
    [
        'id' => 2,
        'codigo' => 'POST-2025-002',
        'escuela' => 'IE San Juan Bautista',
        'departamento' => 'Arequipa',
        'region' => 'Arequipa',
        'titulo_proyecto' => 'Huerto escolar sostenible para educación ambiental',
        'monto_solicitado' => 28000.00,
        'fecha_postulacion' => '2025-01-22',
        'estado' => 'APROBADA'
    ],
    [
        'id' => 3,
        'codigo' => 'POST-2025-003',
        'escuela' => 'IE Nuestra Señora de Guadalupe',
        'departamento' => 'La Libertad',
        'region' => 'La Libertad',
        'titulo_proyecto' => 'Aula virtual interactiva para matemáticas',
        'monto_solicitado' => 35000.00,
        'fecha_postulacion' => '2025-01-25',
        'estado' => 'EN_REVISION'
    ],
    [
        'id' => 4,
        'codigo' => 'POST-2025-004',
        'escuela' => 'IE Abraham Valdelomar',
        'departamento' => 'Ica',
        'region' => 'Ica',
        'titulo_proyecto' => 'Biblioteca digital con enfoque inclusivo',
        'monto_solicitado' => 32000.00,
        'fecha_postulacion' => '2025-01-28',
        'estado' => 'OBSERVADA'
    ],
    [
        'id' => 5,
        'codigo' => 'POST-2025-005',
        'escuela' => 'IE José María Arguedas',
        'departamento' => 'Cusco',
        'region' => 'Cusco',
        'titulo_proyecto' => 'Rescate de lenguas originarias mediante tecnología',
        'monto_solicitado' => 40000.00,
        'fecha_postulacion' => '2025-02-01',
        'estado' => 'EN_EVALUACION'
    ],
    [
        'id' => 6,
        'codigo' => 'POST-2025-006',
        'escuela' => 'IE Miguel Grau',
        'departamento' => 'Piura',
        'region' => 'Piura',
        'titulo_proyecto' => 'Innovación en educación física y deportes',
        'monto_solicitado' => 25000.00,
        'fecha_postulacion' => '2025-02-03',
        'estado' => 'RECHAZADA'
    ],
    [
        'id' => 7,
        'codigo' => 'POST-2025-007',
        'escuela' => 'IE Ricardo Palma',
        'departamento' => 'Lima',
        'region' => 'Lima',
        'titulo_proyecto' => 'Centro de innovación pedagógica',
        'monto_solicitado' => 50000.00,
        'fecha_postulacion' => '2025-02-05',
        'estado' => 'APROBADA'
    ],
    [
        'id' => 8,
        'codigo' => 'POST-2025-008',
        'escuela' => 'IE César Vallejo',
        'departamento' => 'Lambayeque',
        'region' => 'Lambayeque',
        'titulo_proyecto' => 'Implementación de aula STEAM',
        'monto_solicitado' => 38000.00,
        'fecha_postulacion' => '2025-02-08',
        'estado' => 'EN_REVISION'
    ]
];

?>

<div class="container">
    <div class="d-flex justify-between align-center mb-4">
        <h1>Listado de Postulaciones</h1>
        <a href="#" class="btn btn-primary">+ Nueva Postulación</a>
    </div>
    
    <div class="alert alert-info">
        <strong>🔍 Modo de Prueba:</strong> Mostrando <?php echo count($postulaciones); ?> postulaciones simuladas.
    </div>
    
    <!-- FILTROS -->
    <div class="card mb-3">
        <div class="card-body">
            <form method="GET" class="row">
                <div class="col-4">
                    <div class="form-group">
                        <label class="form-label">Buscar por código o escuela</label>
                        <input type="text" name="buscar" class="form-control" placeholder="POST-2025-001">
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-control">
                            <option value="">Todos</option>
                            <option value="EN_REVISION">En Revisión</option>
                            <option value="EN_EVALUACION">En Evaluación</option>
                            <option value="APROBADA">Aprobada</option>
                            <option value="RECHAZADA">Rechazada</option>
                            <option value="OBSERVADA">Observada</option>
                        </select>
                    </div>
                </div>
                <div class="col-3">
                    <div class="form-group">
                        <label class="form-label">Región</label>
                        <select name="region" class="form-control">
                            <option value="">Todas</option>
                            <option value="Lima">Lima</option>
                            <option value="Arequipa">Arequipa</option>
                            <option value="Cusco">Cusco</option>
                        </select>
                    </div>
                </div>
                <div class="col-2">
                    <div class="form-group">
                        <label class="form-label">&nbsp;</label>
                        <button type="submit" class="btn btn-primary" style="width: 100%;">Filtrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
    <!-- TABLA DE POSTULACIONES -->
    <div class="card">
        <div class="card-header">
            <h3>Total: <?php echo count($postulaciones); ?> postulaciones</h3>
        </div>
        <div class="card-body">
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Escuela</th>
                            <th>Región</th>
                            <th>Proyecto</th>
                            <th class="text-right">Monto</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($postulaciones as $post): ?>
                        <tr>
                            <td><strong><?php echo $post['codigo']; ?></strong></td>
                            <td><?php echo htmlspecialchars($post['escuela']); ?></td>
                            <td><?php echo htmlspecialchars($post['region']); ?></td>
                            <td><?php echo htmlspecialchars(substr($post['titulo_proyecto'], 0, 50)) . '...'; ?></td>
                            <td class="text-right">S/ <?php echo number_format($post['monto_solicitado'], 2); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($post['fecha_postulacion'])); ?></td>
                            <td>
                                <?php 
                                $badgeClass = 'badge-secondary';
                                if ($post['estado'] == 'APROBADA') $badgeClass = 'badge-success';
                                elseif ($post['estado'] == 'EN_EVALUACION') $badgeClass = 'badge-warning';
                                elseif ($post['estado'] == 'RECHAZADA') $badgeClass = 'badge-danger';
                                elseif ($post['estado'] == 'EN_REVISION') $badgeClass = 'badge-primary';
                                ?>
                                <span class="badge <?php echo $badgeClass; ?>"><?php echo $post['estado']; ?></span>
                            </td>
                            <td class="text-center">
                                <a href="detalle.php?id=<?php echo $post['id']; ?>" class="btn btn-sm btn-primary">Ver Detalle</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
</div>

<?php require_once '../../includes/footer.php'; ?>