<?php
/**
 * DATOS SIMULADOS PARA SISTEMA FONDEP
 * Base de Datos II - UTP
 * 
 * Este archivo centraliza todos los datos de prueba para funcionamiento
 * sin conexión a bases de datos reales
 */

// =============================================================================
// REGIONES DEL PERÚ
// =============================================================================
function obtenerRegiones() {
    return [
        ['id' => 1, 'nombre' => 'Lima', 'codigo' => 'LIM'],
        ['id' => 2, 'nombre' => 'Cusco', 'codigo' => 'CUS'],
        ['id' => 3, 'nombre' => 'Arequipa', 'codigo' => 'ARE'],
        ['id' => 4, 'nombre' => 'La Libertad', 'codigo' => 'LAL'],
        ['id' => 5, 'nombre' => 'Piura', 'codigo' => 'PIU'],
        ['id' => 6, 'nombre' => 'Junín', 'codigo' => 'JUN'],
        ['id' => 7, 'nombre' => 'Lambayeque', 'codigo' => 'LAM'],
        ['id' => 8, 'nombre' => 'Puno', 'codigo' => 'PUN'],
        ['id' => 9, 'nombre' => 'Loreto', 'codigo' => 'LOR'],
        ['id' => 10, 'nombre' => 'Ayacucho', 'codigo' => 'AYA']
    ];
}

// =============================================================================
// CONVOCATORIAS
// =============================================================================
function obtenerConvocatorias() {
    return [
        [
            'id' => 1,
            'codigo' => 'CONV-2024-001',
            'titulo' => 'Innovación Educativa en Ciencias 2024',
            'descripcion' => 'Proyectos de innovación para mejora de enseñanza en ciencias naturales',
            'fecha_inicio' => '2024-01-15',
            'fecha_fin' => '2024-03-15',
            'presupuesto_total' => 500000.00,
            'estado' => 'CERRADA',
            'requisitos' => 'Escuelas públicas nivel secundaria',
            'monto_max_proyecto' => 50000.00,
            'areas_tematicas' => 'Ciencias, Tecnología'
        ],
        [
            'id' => 2,
            'codigo' => 'CONV-2024-002',
            'titulo' => 'Inclusión Digital y TIC 2024',
            'descripcion' => 'Proyectos para integración de tecnologías en aulas rurales',
            'fecha_inicio' => '2024-03-01',
            'fecha_fin' => '2024-05-31',
            'presupuesto_total' => 750000.00,
            'estado' => 'EN_EVALUACION',
            'requisitos' => 'Escuelas rurales con acceso limitado a internet',
            'monto_max_proyecto' => 40000.00,
            'areas_tematicas' => 'Tecnología, Inclusión Digital'
        ],
        [
            'id' => 3,
            'codigo' => 'CONV-2024-003',
            'titulo' => 'Lectura y Comprensión Lectora 2024',
            'descripcion' => 'Estrategias innovadoras para mejorar comprensión lectora en primaria',
            'fecha_inicio' => '2024-05-01',
            'fecha_fin' => '2024-07-31',
            'presupuesto_total' => 600000.00,
            'estado' => 'ABIERTA',
            'requisitos' => 'Escuelas públicas nivel primaria',
            'monto_max_proyecto' => 35000.00,
            'areas_tematicas' => 'Lenguaje, Pedagogía'
        ],
        [
            'id' => 4,
            'codigo' => 'CONV-2024-004',
            'titulo' => 'Educación Ambiental y Sostenibilidad',
            'descripcion' => 'Proyectos de conciencia ambiental y prácticas sostenibles',
            'fecha_inicio' => '2024-08-01',
            'fecha_fin' => '2024-10-31',
            'presupuesto_total' => 400000.00,
            'estado' => 'PLANIFICADA',
            'requisitos' => 'Todas las escuelas públicas',
            'monto_max_proyecto' => 30000.00,
            'areas_tematicas' => 'Medio Ambiente, Ciencias'
        ],
        [
            'id' => 5,
            'codigo' => 'CONV-2024-005',
            'titulo' => 'Matemáticas Aplicadas en Secundaria',
            'descripcion' => 'Metodologías innovadoras para enseñanza de matemáticas',
            'fecha_inicio' => '2024-09-01',
            'fecha_fin' => '2024-11-30',
            'presupuesto_total' => 550000.00,
            'estado' => 'ABIERTA',
            'requisitos' => 'Escuelas públicas nivel secundaria',
            'monto_max_proyecto' => 45000.00,
            'areas_tematicas' => 'Matemáticas, STEM'
        ]
    ];
}

// =============================================================================
// ESCUELAS
// =============================================================================
function obtenerEscuelas() {
    return [
        ['id' => 1, 'codigo' => 'ESC-001', 'nombre' => 'I.E. José María Arguedas', 'region_id' => 1, 'tipo' => 'Pública'],
        ['id' => 2, 'codigo' => 'ESC-002', 'nombre' => 'I.E. Micaela Bastidas', 'region_id' => 2, 'tipo' => 'Pública'],
        ['id' => 3, 'codigo' => 'ESC-003', 'nombre' => 'I.E. César Vallejo', 'region_id' => 3, 'tipo' => 'Pública'],
        ['id' => 4, 'codigo' => 'ESC-004', 'nombre' => 'I.E. Inca Garcilaso de la Vega', 'region_id' => 4, 'tipo' => 'Pública'],
        ['id' => 5, 'codigo' => 'ESC-005', 'nombre' => 'I.E. Túpac Amaru', 'region_id' => 5, 'tipo' => 'Pública'],
        ['id' => 6, 'codigo' => 'ESC-006', 'nombre' => 'I.E. Ricardo Palma', 'region_id' => 1, 'tipo' => 'Pública'],
        ['id' => 7, 'codigo' => 'ESC-007', 'nombre' => 'I.E. Abraham Valdelomar', 'region_id' => 6, 'tipo' => 'Pública'],
        ['id' => 8, 'codigo' => 'ESC-008', 'nombre' => 'I.E. José Carlos Mariátegui', 'region_id' => 7, 'tipo' => 'Pública']
    ];
}

// =============================================================================
// POSTULACIONES
// =============================================================================
function obtenerPostulaciones() {
    return [
        [
            'id' => 1,
            'codigo' => 'POST-2024-001',
            'convocatoria_id' => 1,
            'escuela_id' => 1,
            'titulo_proyecto' => 'Laboratorio Virtual de Química',
            'monto_solicitado' => 45000.00,
            'fecha_postulacion' => '2024-02-10',
            'estado' => 'APROBADA',
            'puntaje_final' => 92.5,
            'observaciones' => 'Proyecto innovador con alto impacto'
        ],
        [
            'id' => 2,
            'codigo' => 'POST-2024-002',
            'convocatoria_id' => 1,
            'escuela_id' => 2,
            'titulo_proyecto' => 'Huerto Escolar Científico',
            'monto_solicitado' => 38000.00,
            'fecha_postulacion' => '2024-02-15',
            'estado' => 'APROBADA',
            'puntaje_final' => 88.0,
            'observaciones' => 'Excelente propuesta de aprendizaje activo'
        ],
        [
            'id' => 3,
            'codigo' => 'POST-2024-003',
            'convocatoria_id' => 2,
            'escuela_id' => 3,
            'titulo_proyecto' => 'Aulas Digitales Rurales',
            'monto_solicitado' => 40000.00,
            'fecha_postulacion' => '2024-04-20',
            'estado' => 'EN_EVALUACION',
            'puntaje_final' => null,
            'observaciones' => null
        ],
        [
            'id' => 4,
            'codigo' => 'POST-2024-004',
            'convocatoria_id' => 2,
            'escuela_id' => 4,
            'titulo_proyecto' => 'Conectividad para Todos',
            'monto_solicitado' => 39500.00,
            'fecha_postulacion' => '2024-04-25',
            'estado' => 'EN_EVALUACION',
            'puntaje_final' => null,
            'observaciones' => null
        ],
        [
            'id' => 5,
            'codigo' => 'POST-2024-005',
            'convocatoria_id' => 3,
            'escuela_id' => 5,
            'titulo_proyecto' => 'Biblioteca Digital Interactiva',
            'monto_solicitado' => 32000.00,
            'fecha_postulacion' => '2024-06-15',
            'estado' => 'EN_REVISION',
            'puntaje_final' => null,
            'observaciones' => 'Pendiente revisión de requisitos'
        ],
        [
            'id' => 6,
            'codigo' => 'POST-2024-006',
            'convocatoria_id' => 3,
            'escuela_id' => 6,
            'titulo_proyecto' => 'Clubes de Lectura Virtuales',
            'monto_solicitado' => 28000.00,
            'fecha_postulacion' => '2024-06-20',
            'estado' => 'RECHAZADA',
            'puntaje_final' => 65.0,
            'observaciones' => 'No cumple requisito de impacto mínimo'
        ],
        [
            'id' => 7,
            'codigo' => 'POST-2024-007',
            'convocatoria_id' => 5,
            'escuela_id' => 7,
            'titulo_proyecto' => 'Matemática Lúdica con Robótica',
            'monto_solicitado' => 44000.00,
            'fecha_postulacion' => '2024-10-05',
            'estado' => 'EN_REVISION',
            'puntaje_final' => null,
            'observaciones' => null
        ],
        [
            'id' => 8,
            'codigo' => 'POST-2024-008',
            'convocatoria_id' => 5,
            'escuela_id' => 8,
            'titulo_proyecto' => 'Geometría en 3D',
            'monto_solicitado' => 35000.00,
            'fecha_postulacion' => '2024-10-10',
            'estado' => 'OBSERVADA',
            'puntaje_final' => null,
            'observaciones' => 'Requiere ajustes en presupuesto'
        ]
    ];
}

// =============================================================================
// EVALUADORES
// =============================================================================
function obtenerEvaluadores() {
    return [
        [
            'id' => 1,
            'nombres' => 'María Elena',
            'apellidos' => 'Torres Vega',
            'email' => 'maria.torres@fondep.gob.pe',
            'especialidad' => 'Ciencias Naturales',
            'estado' => 'ACTIVO'
        ],
        [
            'id' => 2,
            'nombres' => 'Carlos Alberto',
            'apellidos' => 'Mendoza Ríos',
            'email' => 'carlos.mendoza@fondep.gob.pe',
            'especialidad' => 'Tecnología Educativa',
            'estado' => 'ACTIVO'
        ],
        [
            'id' => 3,
            'nombres' => 'Ana Sofía',
            'apellidos' => 'Gutiérrez Campos',
            'email' => 'ana.gutierrez@fondep.gob.pe',
            'especialidad' => 'Pedagogía y Didáctica',
            'estado' => 'ACTIVO'
        ],
        [
            'id' => 4,
            'nombres' => 'Jorge Luis',
            'apellidos' => 'Paredes Soto',
            'email' => 'jorge.paredes@fondep.gob.pe',
            'especialidad' => 'Matemáticas',
            'estado' => 'ACTIVO'
        ]
    ];
}

// =============================================================================
// EVALUACIONES
// =============================================================================
function obtenerEvaluaciones() {
    return [
        [
            'id' => 1,
            'postulacion_id' => 1,
            'evaluador_id' => 1,
            'puntaje_pertinencia' => 95,
            'puntaje_viabilidad' => 90,
            'puntaje_impacto' => 92,
            'puntaje_sostenibilidad' => 93,
            'puntaje_total' => 92.5,
            'comentarios' => 'Excelente proyecto con clara metodología científica',
            'recomendacion' => 'APROBAR',
            'fecha_evaluacion' => '2024-03-01',
            'estado' => 'COMPLETADA'
        ],
        [
            'id' => 2,
            'postulacion_id' => 2,
            'evaluador_id' => 1,
            'puntaje_pertinencia' => 90,
            'puntaje_viabilidad' => 85,
            'puntaje_impacto' => 88,
            'puntaje_sostenibilidad' => 89,
            'puntaje_total' => 88.0,
            'comentarios' => 'Propuesta sólida con enfoque interdisciplinario',
            'recomendacion' => 'APROBAR',
            'fecha_evaluacion' => '2024-03-05',
            'estado' => 'COMPLETADA'
        ],
        [
            'id' => 3,
            'postulacion_id' => 3,
            'evaluador_id' => 2,
            'puntaje_pertinencia' => null,
            'puntaje_viabilidad' => null,
            'puntaje_impacto' => null,
            'puntaje_sostenibilidad' => null,
            'puntaje_total' => null,
            'comentarios' => null,
            'recomendacion' => null,
            'fecha_evaluacion' => null,
            'estado' => 'ASIGNADA'
        ]
    ];
}

// =============================================================================
// EVENTOS DEL SISTEMA (CASSANDRA)
// =============================================================================
function obtenerEventos($limite = 50) {
    $tipos_evento = ['LOGIN', 'INSERT', 'UPDATE', 'DELETE', 'EXPORT', 'APPROVAL', 'REJECTION'];
    $tablas = ['postulaciones', 'convocatorias', 'evaluaciones', 'usuarios'];
    $usuarios = ['admin@fondep.gob.pe', 'evaluador1@fondep.gob.pe', 'escuela1@edu.pe'];
    $criticidad = ['INFO', 'WARNING', 'ERROR', 'CRITICAL'];
    
    $eventos = [];
    for ($i = 1; $i <= $limite; $i++) {
        $eventos[] = [
            'id' => 'EVT-' . str_pad($i, 6, '0', STR_PAD_LEFT),
            'tipo_evento' => $tipos_evento[array_rand($tipos_evento)],
            'usuario' => $usuarios[array_rand($usuarios)],
            'tabla_afectada' => $tablas[array_rand($tablas)],
            'nivel_criticidad' => $criticidad[array_rand($criticidad)],
            'descripcion' => 'Evento de prueba ' . $i,
            'ip_origen' => '192.168.1.' . rand(1, 255),
            'timestamp' => date('Y-m-d H:i:s', strtotime('-' . rand(1, 30) . ' days'))
        ];
    }
    return $eventos;
}

// =============================================================================
// MÉTRICAS DE SEGUIMIENTO (CASSANDRA)
// =============================================================================
function obtenerMetricas() {
    return [
        [
            'postulacion_id' => 1,
            'fecha_reporte' => '2024-05-15',
            'avance_porcentaje' => 25.5,
            'monto_ejecutado' => 11250.00,
            'actividades_completadas' => 3,
            'observaciones' => 'Adquisición de equipos completada'
        ],
        [
            'postulacion_id' => 1,
            'fecha_reporte' => '2024-08-15',
            'avance_porcentaje' => 58.3,
            'monto_ejecutado' => 26235.00,
            'actividades_completadas' => 7,
            'observaciones' => 'Instalación y capacitación en curso'
        ],
        [
            'postulacion_id' => 2,
            'fecha_reporte' => '2024-06-01',
            'avance_porcentaje' => 15.0,
            'monto_ejecutado' => 5700.00,
            'actividades_completadas' => 2,
            'observaciones' => 'Preparación de terreno finalizada'
        ]
    ];
}

// =============================================================================
// USUARIOS DEL SISTEMA
// =============================================================================
function obtenerUsuarios() {
    return [
        [
            'id' => 1,
            'usuario' => 'admin',
            'password' => password_hash('admin123', PASSWORD_DEFAULT),
            'nombres' => 'Administrador',
            'apellidos' => 'FONDEP',
            'email' => 'admin@fondep.gob.pe',
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO'
        ],
        [
            'id' => 2,
            'usuario' => 'evaluador1',
            'password' => password_hash('eval123', PASSWORD_DEFAULT),
            'nombres' => 'María Elena',
            'apellidos' => 'Torres Vega',
            'email' => 'maria.torres@fondep.gob.pe',
            'rol' => 'EVALUADOR',
            'estado' => 'ACTIVO'
        ],
        [
            'id' => 3,
            'usuario' => 'escuela1',
            'password' => password_hash('escuela123', PASSWORD_DEFAULT),
            'nombres' => 'Director',
            'apellidos' => 'I.E. Arguedas',
            'email' => 'director@iearguedas.edu.pe',
            'rol' => 'ESCUELA',
            'estado' => 'ACTIVO'
        ]
    ];
}

// =============================================================================
// FUNCIONES AUXILIARES
// =============================================================================

/**
 * Obtener nombre de región por ID
 */
function obtenerNombreRegion($region_id) {
    $regiones = obtenerRegiones();
    foreach ($regiones as $region) {
        if ($region['id'] == $region_id) {
            return $region['nombre'];
        }
    }
    return 'Desconocida';
}

/**
 * Obtener nombre de escuela por ID
 */
function obtenerNombreEscuela($escuela_id) {
    $escuelas = obtenerEscuelas();
    foreach ($escuelas as $escuela) {
        if ($escuela['id'] == $escuela_id) {
            return $escuela['nombre'];
        }
    }
    return 'Escuela no encontrada';
}

/**
 * Obtener convocatoria por ID
 */
function obtenerConvocatoriaPorId($convocatoria_id) {
    $convocatorias = obtenerConvocatorias();
    foreach ($convocatorias as $conv) {
        if ($conv['id'] == $convocatoria_id) {
            return $conv;
        }
    }
    return null;
}

/**
 * Obtener postulación por código
 */
function obtenerPostulacionPorCodigo($codigo) {
    $postulaciones = obtenerPostulaciones();
    foreach ($postulaciones as $post) {
        if ($post['codigo'] == $codigo) {
            return $post;
        }
    }
    return null;
}

/**
 * Formatear fecha en español
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return '-';
    $timestamp = strtotime($fecha);
    $meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
    return date('d', $timestamp) . ' ' . $meses[date('n', $timestamp) - 1] . ' ' . date('Y', $timestamp);
}

/**
 * Formatear monto en soles
 */
function formatearMonto($monto) {
    return 'S/ ' . number_format($monto, 2, '.', ',');
}

/**
 * Obtener clase CSS para badge de estado
 */
function obtenerClaseBadge($estado) {
    $clases = [
        'APROBADA' => 'badge-success',
        'RECHAZADA' => 'badge-danger',
        'EN_EVALUACION' => 'badge-warning',
        'EN_REVISION' => 'badge-info',
        'OBSERVADA' => 'badge-secondary',
        'ABIERTA' => 'badge-success',
        'CERRADA' => 'badge-secondary',
        'PLANIFICADA' => 'badge-info',
        'ACTIVO' => 'badge-success',
        'INACTIVO' => 'badge-secondary',
        'COMPLETADA' => 'badge-success',
        'ASIGNADA' => 'badge-warning'
    ];
    return $clases[$estado] ?? 'badge-secondary';
}