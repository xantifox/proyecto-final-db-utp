<?php
/**
 * Funciones Helper para el Sistema FONDEP
 * Funciones reutilizables en todo el proyecto
 */

/**
 * Formatear fecha a formato peruano
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return '-';
    $timestamp = is_string($fecha) ? strtotime($fecha) : $fecha;
    return date('d/m/Y', $timestamp);
}

/**
 * Formatear fecha con hora
 */
function formatearFechaHora($fecha) {
    if (empty($fecha)) return '-';
    $timestamp = is_string($fecha) ? strtotime($fecha) : $fecha;
    return date('d/m/Y H:i', $timestamp);
}

/**
 * Formatear monto en soles
 */
function formatearMonto($monto) {
    if (empty($monto)) return 'S/ 0.00';
    return 'S/ ' . number_format($monto, 2, '.', ',');
}

/**
 * Obtener clase CSS para badge según estado
 */
function obtenerClaseBadge($estado) {
    $clases = [
        'ACTIVA' => 'badge-success',
        'ABIERTA' => 'badge-success',
        'APROBADA' => 'badge-success',
        'APROBADO' => 'badge-success',
        
        'EN_EVALUACION' => 'badge-warning',
        'EN_REVISION' => 'badge-info',
        'REGISTRADA' => 'badge-info',
        
        'CERRADA' => 'badge-secondary',
        'FINALIZADA' => 'badge-secondary',
        'COMPLETADA' => 'badge-secondary',
        
        'RECHAZADA' => 'badge-danger',
        'RECHAZADO' => 'badge-danger',
        'OBSERVADA' => 'badge-danger',
        
        'DESEMBOLSADO' => 'badge-success',
        'PENDIENTE' => 'badge-warning',
    ];
    
    return $clases[$estado] ?? 'badge-secondary';
}

/**
 * Traducir estado a texto legible
 */
function traducirEstado($estado) {
    $traducciones = [
        'ACTIVA' => 'Activa',
        'ABIERTA' => 'Abierta',
        'CERRADA' => 'Cerrada',
        'FINALIZADA' => 'Finalizada',
        'EN_EVALUACION' => 'En Evaluación',
        'EN_REVISION' => 'En Revisión',
        'REGISTRADA' => 'Registrada',
        'APROBADA' => 'Aprobada',
        'RECHAZADA' => 'Rechazada',
        'OBSERVADA' => 'Observada',
        'DESEMBOLSADO' => 'Desembolsado',
        'PENDIENTE' => 'Pendiente',
    ];
    
    return $traducciones[$estado] ?? $estado;
}

/**
 * Obtener nombre corto de región
 */
function obtenerNombreCortoRegion($nombre_completo) {
    // Remover prefijos comunes
    $nombre = str_replace(['Región ', 'Departamento de '], '', $nombre_completo);
    return $nombre;
}

/**
 * Generar enlace a módulo
 */
function urlModulo($modulo, $accion = '', $params = []) {
    global $base_url;
    $url = $base_url . '/modules/' . $modulo . '/';
    
    if (!empty($accion)) {
        $url .= $accion . '.php';
    } else {
        $url .= 'listar.php';
    }
    
    if (!empty($params)) {
        $url .= '?' . http_build_query($params);
    }
    
    return $url;
}

/**
 * Sanitizar entrada de usuario
 */
function sanitizar($input) {
    if (is_array($input)) {
        return array_map('sanitizar', $input);
    }
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

/**
 * Verificar si una fecha es válida
 */
function esFechaValida($fecha) {
    if (empty($fecha)) return false;
    $d = DateTime::createFromFormat('Y-m-d', $fecha);
    return $d && $d->format('Y-m-d') === $fecha;
}

/**
 * Calcular días transcurridos
 */
function diasTranscurridos($fecha_inicio, $fecha_fin = null) {
    if (empty($fecha_inicio)) return 0;
    
    $inicio = new DateTime($fecha_inicio);
    $fin = $fecha_fin ? new DateTime($fecha_fin) : new DateTime();
    
    $diferencia = $inicio->diff($fin);
    return $diferencia->days;
}

/**
 * Generar código único
 */
function generarCodigo($prefijo, $longitud = 5) {
    $numero = str_pad(rand(1, pow(10, $longitud) - 1), $longitud, '0', STR_PAD_LEFT);
    return $prefijo . '-' . date('Y') . '-' . $numero;
}
?>