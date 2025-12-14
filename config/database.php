<?php
/**
 * Configuración de Conexiones a Bases de Datos
 * Sistema FONDEP - Base de Datos II
 * 
 * Este archivo centraliza todas las configuraciones de conexión
 * a las 3 bases de datos del proyecto:
 * - PostgreSQL: Datos transaccionales (OLTP)
 * - MongoDB: Documentos y propuestas
 * - Cassandra: Time-series y eventos
 */

// Cargar autoload de Composer (para MongoDB)
require_once __DIR__ . '/../vendor/autoload.php';

// =====================================================
// CONFIGURACIÓN POSTGRESQL
// =====================================================
define('DB_PGSQL_HOST', 'localhost');
define('DB_PGSQL_PORT', '5432');
define('DB_PGSQL_NAME', 'fondep');
define('DB_PGSQL_USER', 'postgres');
define('DB_PGSQL_PASS', '12345678');

// =====================================================
// CONFIGURACIÓN MONGODB
// =====================================================
define('DB_MONGO_HOST', 'localhost');
define('DB_MONGO_PORT', '27017');
define('DB_MONGO_NAME', 'fondep_innovacion');
define('DB_MONGO_USER', '');
define('DB_MONGO_PASS', '');

// =====================================================
// CONFIGURACIÓN CASSANDRA
// =====================================================
define('DB_CASSANDRA_HOST', 'localhost');
define('DB_CASSANDRA_PORT', '9042');
define('DB_CASSANDRA_KEYSPACE', 'fondep_eventos');
define('DB_CASSANDRA_USER', '');
define('DB_CASSANDRA_PASS', '');

// =====================================================
// CONFIGURACIÓN GENERAL
// =====================================================
define('APP_NAME', 'Sistema FONDEP - Gestión de Proyectos de Innovación Educativa');
define('APP_VERSION', '1.0.0');
define('APP_ENV', 'development'); // development o production

// Zona horaria
date_default_timezone_set('America/Lima');

// Manejo de errores en desarrollo
if (APP_ENV === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}
?>