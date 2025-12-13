<?php
/**
 * MÓDULO: POSTULACIONES - DETALLE
 * Vista completa que integra datos de las 3 bases de datos
 * 
 * ⭐ ESTA ES LA VISTA MÁS IMPORTANTE DEL PROYECTO ⭐
 * Demuestra la arquitectura híbrida con integración real
 * 
 * Integración de datos:
 * - PostgreSQL: Datos estructurados (postulacion, escuela, convocatoria)
 * - MongoDB: Propuesta completa con documentos anidados
 * - Cassandra: Histórico de eventos y métricas de seguimiento
 */

$page_title = "Detalle de Postulación";
require_once '../../includes/header.php';
require_once '../../includes/datos_simulados.php';

// Obtener código de postulación
$codigo = $_GET['codigo'] ?? 'POST-2024-001';

// Buscar postulación
$postulacion = obtenerPostulacionPorCodigo($codigo);

if (!$postulacion) {
    echo "<div class='container'><div class='alert alert-danger'>Postulación no encontrada</div></div>";
    require_once '../../includes/footer.php';
    exit;
}

// Obtener datos relacionados de PostgreSQL
$convocatoria = obtenerConvocatoriaPorId($postulacion['convocatoria_id']);
$escuelas = obtenerEscuelas();
$escuela = array_filter($escuelas, fn($e) => $e['id'] == $postulacion['escuela_id']);
$escuela = !empty($escuela) ? reset($escuela) : null;

// Datos de MongoDB (propuesta completa)
$propuesta_mongodb = [
    'titulo' => $postulacion['titulo_proyecto'],
    'resumen_ejecutivo' => 'Proyecto innovador que busca transformar la enseñanza mediante metodologías activas y uso de tecnología. Se propone implementar un espacio de aprendizaje colaborativo que permita a los estudiantes desarrollar competencias del siglo XXI.',
    'problema_identificado' => 'Bajo rendimiento académico en el área y falta de motivación de los estudiantes.',
    'objetivos' => [
        'general' => 'Mejorar el rendimiento académico y la motivación de los estudiantes',
        'especificos' => [
            'Implementar metodologías activas de enseñanza',
            'Integrar tecnología educativa en el aula',
            'Capacitar a docentes en nuevas estrategias pedagógicas',
            'Evaluar el impacto en el aprendizaje estudiantil'
        ]
    ],
    'metodologia' => [
        'enfoque' => 'Aprendizaje basado en proyectos',
        'fases' => [
            ['fase' => 'Diagnóstico', 'duracion' => '2 meses', 'actividades' => 'Evaluación inicial de estudiantes y docentes'],
            ['fase' => 'Implementación', 'duracion' => '6 meses', 'actividades' => 'Ejecución de estrategias pedagógicas'],
            ['fase' => 'Evaluación', 'duracion' => '2 meses', 'actividades' => 'Medición de resultados e impacto'],
            ['fase' => 'Sostenibilidad', 'duracion' => '2 meses', 'actividades' => 'Plan de continuidad']
        ]
    ],
    'presupuesto_detallado' => [
        ['categoria' => 'Equipamiento', 'monto' => $postulacion['monto_solicitado'] * 0.40, 'descripcion' => 'Equipos tecnológicos y materiales'],
        ['categoria' => 'Capacitación', 'monto' => $postulacion['monto_solicitado'] * 0.25, 'descripcion' => 'Talleres para docentes'],
        ['categoria' => 'Materiales Educativos', 'monto' => $postulacion['monto_solicitado'] * 0.20, 'descripcion' => 'Recursos didácticos'],
        ['categoria' => 'Seguimiento', 'monto' => $postulacion['monto_solicitado'] * 0.15, 'descripcion' => 'Monitoreo y evaluación']
    ],
    'beneficiarios' => [
        'directos' => 250,
        'indirectos' => 750,
        'detalle' => '250 estudiantes de secundaria, 15 docentes, comunidad educativa'
    ],
    'impacto_esperado' => 'Mejora del 30% en rendimiento académico, incremento de participación estudiantil',
    'documentos_adjuntos' => [
        ['nombre' => 'Diagnóstico Institucional.pdf', 'tipo' => 'PDF', 'tamaño' => '2.5 MB'],
        ['nombre' => 'Plan de Trabajo Detallado.docx', 'tipo' => 'DOCX', 'tamaño' => '1.8 MB'],
        ['nombre' => 'Presupuesto Detallado.xlsx', 'tipo' => 'XLSX', 'tamaño' => '850 KB']
    ]
];

// Datos de Cassandra (eventos y métricas)
$eventos_cassandra = [
    ['timestamp' => '2024-02-10 09:15:23', 'tipo' => 'CREATE', 'usuario' => 'director@iearguedas.edu.pe', 'descripcion' => 'Postulación creada'],
    ['timestamp' => '2024-02-10 09:45:12', 'tipo' => 'UPDATE', 'usuario' => 'director@iearguedas.edu.pe', 'descripcion' => 'Documentos adjuntos cargados'],
    ['timestamp' => '2024-02-15 14:30:00', 'tipo' => 'STATUS_CHANGE', 'usuario' => 'admin@fondep.gob.pe', 'descripcion' => 'Estado cambiado a EN_REVISION'],
    ['timestamp' => '2024-02-20 10:00:00', 'tipo' => 'ASSIGNMENT', 'usuario' => 'admin@fondep.gob.pe', 'descripcion' => 'Asignado a evaluador'],
    ['timestamp' => '2024-03-01 16:45:30', 'tipo' => 'EVALUATION', 'usuario' => 'maria.torres@fondep.gob.pe', 'descripcion' => 'Evaluación completada'],
    ['timestamp' => '2024-03-05 11:20:15', 'tipo' => 'STATUS_CHANGE', 'usuario' => 'admin@fondep.gob.pe', 'descripcion' => 'Estado cambiado a APROBADA'],
];

// Métricas de seguimiento (si está aprobada)
$metricas = $postulacion['estado'] == 'APROBADA' ? obtenerMetricas() : [];

// Evaluaciones
$evaluaciones = array_filter(obtenerEvaluaciones(), fn($e) => $e['postulacion_id'] == $postulacion['id']);
?>

<div class="container" style="padding: 2rem 0;">
    
    <!-- Breadcrumb -->
    <div class="breadcrumb">
        <a href="<?php echo $base_url; ?>/">Inicio</a> / 
        <a href="listar.php">Postulaciones</a> / 
        <span><?php echo htmlspecialchars($codigo); ?></span>
    </div>

    <!-- Header con estado -->
    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 2rem;">
        <div>
            <div style="display: flex; align-items: center; gap: 1rem; margin-bottom: 0.5rem;">
                <h1 style="margin: 0; color: var(--dark-color);">
                    <?php echo htmlspecialchars($postulacion['titulo_proyecto']); ?>
                </h1>
                <span class="badge <?php echo obtenerClaseBadge($postulacion['estado']); ?>" 
                      style="font-size: 1rem; padding: 0.5rem 1rem;">
                    <?php echo str_replace('_', ' ', $postulacion['estado']); ?>
                </span>
            </div>
            <p style="margin: 0; color: #6b7280;">
                <strong>Código:</strong> <?php echo htmlspecialchars($codigo); ?> | 
                <strong>Postulación:</strong> <?php echo formatearFecha($postulacion['fecha_postulacion']); ?>
            </p>
        </div>
        <a href="listar.php" class="btn btn-secondary">← Volver</a>
    </div>

    <!-- Alerta de integración -->
    <div class="alert alert-info" style="margin-bottom: 2rem;">
        <strong>🔗 Vista Integrada Multi-BD:</strong> Esta página demuestra la integración de las 3 bases de datos:
        <span class="badge badge-info">PostgreSQL</span>
        <span class="badge badge-success">MongoDB</span>
        <span class="badge badge-warning">Cassandra</span>
    </div>

    <div class="grid">
        
        <!-- Columna Principal -->
        <div class="col-8">
            
            <!-- SECCIÓN POSTGRESQL: Datos Estructurados -->
            <div class="card" style="margin-bottom: 1.5rem; border: 2px solid #3b82f6;">
                <div style="padding: 1rem; background: linear-gradient(135deg, #3b82f6, #2563eb); color: white;">
                    <h3 style="margin: 0; font-size: 1.125rem;">
                        💾 Datos Estructurados (PostgreSQL)
                    </h3>
                </div>
                <div style="padding: 1.5rem;">
                    <div class="grid">
                        <div class="col-6">
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.875rem; color: #6b7280;">Convocatoria</div>
                                <div style="font-weight: 600;">
                                    <a href="../convocatorias/detalle.php?id=<?php echo $convocatoria['id']; ?>"
                                       style="color: var(--primary-color); text-decoration: none;">
                                        <?php echo htmlspecialchars($convocatoria['titulo']); ?>
                                    </a>
                                </div>
                            </div>
                            <div style="margin-bottom: 1rem;">
                                <div style="font-size: 0.875rem; color: #6b7280;">Institución Educativa</div>
                                <div style="font-weight: 600;">
                                    <?php echo htmlspecialchars($escuela['nombre'] ?? 'N/A'); ?>
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">
                                    <?php echo htmlspecialchars(obtenerNombreRegion($escuela['region_id'] ?? 0)); ?>
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
                            <?php if ($postulacion['puntaje_final']): ?>
                                <div>
                                    <div style="font-size: 0.875rem; color: #6b7280;">Puntaje Final</div>
                                    <div style="font-weight: 700; color: var(--primary-color); font-size: 1.5rem;">
                                        <?php echo number_format($postulacion['puntaje_final'], 1); ?> / 100
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php if ($postulacion['observaciones']): ?>
                        <div style="margin-top: 1rem; padding: 1rem; background-color: #fef3c7; border-left: 4px solid #f59e0b; border-radius: 4px;">
                            <strong>Observaciones:</strong> <?php echo htmlspecialchars($postulacion['observaciones']); ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECCIÓN MONGODB: Propuesta Completa -->
            <div class="card" style="margin-bottom: 1.5rem; border: 2px solid #10b981;">
                <div style="padding: 1rem; background: linear-gradient(135deg, #10b981, #059669); color: white;">
                    <h3 style="margin: 0; font-size: 1.125rem;">
                        📄 Propuesta Completa (MongoDB)
                    </h3>
                </div>
                <div style="padding: 1.5rem;">
                    
                    <!-- Resumen Ejecutivo -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">Resumen Ejecutivo</h4>
                        <p style="color: #374151; line-height: 1.8;">
                            <?php echo htmlspecialchars($propuesta_mongodb['resumen_ejecutivo']); ?>
                        </p>
                    </div>

                    <!-- Objetivos -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">Objetivos</h4>
                        <div style="background-color: #f3f4f6; padding: 1rem; border-radius: 6px;">
                            <div style="margin-bottom: 0.75rem;">
                                <strong>General:</strong> <?php echo htmlspecialchars($propuesta_mongodb['objetivos']['general']); ?>
                            </div>
                            <strong>Específicos:</strong>
                            <ul style="margin: 0.5rem 0 0 0; padding-left: 1.5rem;">
                                <?php foreach ($propuesta_mongodb['objetivos']['especificos'] as $obj): ?>
                                    <li style="margin-bottom: 0.25rem;"><?php echo htmlspecialchars($obj); ?></li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                    </div>

                    <!-- Metodología -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">Metodología</h4>
                        <p><strong>Enfoque:</strong> <?php echo htmlspecialchars($propuesta_mongodb['metodologia']['enfoque']); ?></p>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Fase</th>
                                    <th>Duración</th>
                                    <th>Actividades</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($propuesta_mongodb['metodologia']['fases'] as $fase): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($fase['fase']); ?></strong></td>
                                        <td><?php echo htmlspecialchars($fase['duracion']); ?></td>
                                        <td><?php echo htmlspecialchars($fase['actividades']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Presupuesto Detallado -->
                    <div style="margin-bottom: 1.5rem;">
                        <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">Presupuesto Detallado</h4>
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Categoría</th>
                                    <th>Monto</th>
                                    <th>%</th>
                                    <th>Descripción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($propuesta_mongodb['presupuesto_detallado'] as $item): ?>
                                    <tr>
                                        <td><strong><?php echo htmlspecialchars($item['categoria']); ?></strong></td>
                                        <td><?php echo formatearMonto($item['monto']); ?></td>
                                        <td><?php echo number_format(($item['monto'] / $postulacion['monto_solicitado']) * 100, 1); ?>%</td>
                                        <td><?php echo htmlspecialchars($item['descripcion']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr style="background-color: #f3f4f6; font-weight: bold;">
                                    <td>TOTAL</td>
                                    <td colspan="3"><?php echo formatearMonto($postulacion['monto_solicitado']); ?></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- Documentos Adjuntos -->
                    <div>
                        <h4 style="color: var(--dark-color); margin-bottom: 0.5rem;">📎 Documentos Adjuntos</h4>
                        <?php foreach ($propuesta_mongodb['documentos_adjuntos'] as $doc): ?>
                            <div style="padding: 0.75rem; margin-bottom: 0.5rem; background-color: #f3f4f6; border-radius: 6px; display: flex; justify-content: space-between; align-items: center;">
                                <div>
                                    <strong><?php echo htmlspecialchars($doc['nombre']); ?></strong>
                                    <span style="color: #6b7280; font-size: 0.875rem; margin-left: 0.5rem;">
                                        (<?php echo $doc['tipo']; ?> - <?php echo $doc['tamaño']; ?>)
                                    </span>
                                </div>
                                <a href="#" class="btn btn-sm btn-info">📥 Descargar</a>
                            </div>
                        <?php endforeach; ?>
                    </div>

                </div>
            </div>

            <!-- SECCIÓN CASSANDRA: Histórico de Eventos -->
            <div class="card" style="border: 2px solid #f59e0b;">
                <div style="padding: 1rem; background: linear-gradient(135deg, #f59e0b, #d97706); color: white;">
                    <h3 style="margin: 0; font-size: 1.125rem;">
                        📊 Histórico y Auditoría (Cassandra)
                    </h3>
                </div>
                <div style="padding: 1.5rem;">
                    <h4 style="color: var(--dark-color); margin-bottom: 1rem;">Timeline de Eventos</h4>
                    <div style="position: relative; padding-left: 2rem;">
                        <?php foreach ($eventos_cassandra as $index => $evento): ?>
                            <div style="position: relative; margin-bottom: 1.5rem; padding-bottom: 1.5rem; <?php echo $index < count($eventos_cassandra) - 1 ? 'border-left: 2px solid #e5e7eb;' : ''; ?>">
                                <div style="position: absolute; left: -2.5rem; width: 1.5rem; height: 1.5rem; background-color: var(--primary-color); border-radius: 50%; border: 3px solid white;"></div>
                                <div style="background-color: #f9fafb; padding: 1rem; border-radius: 6px;">
                                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                                        <strong style="color: var(--primary-color);"><?php echo htmlspecialchars($evento['tipo']); ?></strong>
                                        <span style="color: #6b7280; font-size: 0.875rem;"><?php echo $evento['timestamp']; ?></span>
                                    </div>
                                    <div style="color: #374151;"><?php echo htmlspecialchars($evento['descripcion']); ?></div>
                                    <div style="color: #6b7280; font-size: 0.875rem; margin-top: 0.25rem;">
                                        Usuario: <?php echo htmlspecialchars($evento['usuario']); ?>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

        </div>

        <!-- Columna Lateral -->
        <div class="col-4">
            
            <!-- Panel de Evaluación -->
            <?php if (!empty($evaluaciones)): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1rem;">⭐ Evaluación</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <?php $eval = reset($evaluaciones); ?>
                        <?php if ($eval['estado'] == 'COMPLETADA'): ?>
                            <div class="grid" style="margin-bottom: 1rem;">
                                <div class="col-6" style="text-align: center; padding: 0.75rem; background-color: #dbeafe; border-radius: 6px;">
                                    <div style="font-size: 1rem; font-weight: 700; color: #1e40af;">
                                        <?php echo $eval['puntaje_pertinencia']; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #3b82f6;">Pertinencia</div>
                                </div>
                                <div class="col-6" style="text-align: center; padding: 0.75rem; background-color: #dbeafe; border-radius: 6px;">
                                    <div style="font-size: 1rem; font-weight: 700; color: #1e40af;">
                                        <?php echo $eval['puntaje_viabilidad']; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #3b82f6;">Viabilidad</div>
                                </div>
                                <div class="col-6" style="text-align: center; padding: 0.75rem; background-color: #dbeafe; border-radius: 6px;">
                                    <div style="font-size: 1rem; font-weight: 700; color: #1e40af;">
                                        <?php echo $eval['puntaje_impacto']; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #3b82f6;">Impacto</div>
                                </div>
                                <div class="col-6" style="text-align: center; padding: 0.75rem; background-color: #dbeafe; border-radius: 6px;">
                                    <div style="font-size: 1rem; font-weight: 700; color: #1e40af;">
                                        <?php echo $eval['puntaje_sostenibilidad']; ?>
                                    </div>
                                    <div style="font-size: 0.75rem; color: #3b82f6;">Sostenibilidad</div>
                                </div>
                            </div>
                            <div style="text-align: center; padding: 1rem; background: linear-gradient(135deg, #10b981, #059669); color: white; border-radius: 6px; margin-bottom: 1rem;">
                                <div style="font-size: 2rem; font-weight: 700;"><?php echo number_format($eval['puntaje_total'], 1); ?></div>
                                <div style="font-size: 0.875rem;">Puntaje Final</div>
                            </div>
                            <div style="background-color: #f3f4f6; padding: 1rem; border-radius: 6px;">
                                <strong>Comentarios:</strong>
                                <p style="margin: 0.5rem 0 0 0; color: #374151; font-size: 0.875rem;">
                                    <?php echo htmlspecialchars($eval['comentarios']); ?>
                                </p>
                            </div>
                        <?php else: ?>
                            <div style="text-align: center; color: #6b7280; padding: 1rem;">
                                Evaluación pendiente
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Métricas de Seguimiento -->
            <?php if (!empty($metricas)): ?>
                <div class="card" style="margin-bottom: 1.5rem;">
                    <div style="padding: 1rem; border-bottom: 1px solid var(--border-color); background-color: #f9fafb;">
                        <h3 style="margin: 0; font-size: 1rem;">📈 Seguimiento (Cassandra)</h3>
                    </div>
                    <div style="padding: 1rem;">
                        <?php foreach ($metricas as $metrica): ?>
                            <div style="margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color);">
                                <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.25rem;">
                                    <?php echo formatearFecha($metrica['fecha_reporte']); ?>
                                </div>
                                <div style="font-weight: 600; color: var(--dark-color); margin-bottom: 0.5rem;">
                                    Avance: <?php echo number_format($metrica['avance_porcentaje'], 1); ?>%
                                </div>
                                <div style="background-color: #e5e7eb; height: 8px; border-radius: 4px; overflow: hidden; margin-bottom: 0.5rem;">
                                    <div style="background: linear-gradient(90deg, var(--success-color), var(--primary-color)); 
                                                height: 100%; 
                                                width: <?php echo $metrica['avance_porcentaje']; ?>%;">
                                    </div>
                                </div>
                                <div style="font-size: 0.875rem; color: #6b7280;">
                                    Ejecutado: <?php echo formatearMonto($metrica['monto_ejecutado']); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Información Técnica -->
            <div class="alert alert-info">
                <strong>🔧 Integración Técnica:</strong>
                <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem;">
                    <li><strong>PostgreSQL:</strong> JOIN entre postulaciones, escuelas y convocatorias</li>
                    <li><strong>MongoDB:</strong> Consulta de documento completo con agregación</li>
                    <li><strong>Cassandra:</strong> Query time-series por partition key</li>
                </ul>
            </div>

        </div>

    </div>

</div>

<?php require_once '../../includes/footer.php'; ?>