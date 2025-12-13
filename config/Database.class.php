<?php
/**
 * Clase de Conexión a PostgreSQL
 * Implementa patrón Singleton para conexión única
 */
class DatabasePGSQL {
    private static $instance = null;
    private $connection;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        try {
            $dsn = sprintf(
                "pgsql:host=%s;port=%s;dbname=%s",
                DB_PGSQL_HOST,
                DB_PGSQL_PORT,
                DB_PGSQL_NAME
            );
            
            $this->connection = new PDO(
                $dsn,
                DB_PGSQL_USER,
                DB_PGSQL_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
        } catch (PDOException $e) {
            die("Error de conexión a PostgreSQL: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener la conexión PDO
     */
    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Ejecutar consulta SELECT
     */
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            throw new Exception("Error en consulta: " . $e->getMessage());
        }
    }
    
    /**
     * Ejecutar consulta INSERT/UPDATE/DELETE
     */
    public function execute($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            return $stmt->execute($params);
        } catch (PDOException $e) {
            throw new Exception("Error en ejecución: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener último ID insertado
     */
    public function lastInsertId() {
        return $this->connection->lastInsertId();
    }
}

/**
 * Clase de Conexión a MongoDB
 * Usa la biblioteca mongodb/mongodb instalada con Composer
 */
class DatabaseMongoDB {
    private static $instance = null;
    private $client;
    private $database;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        try {
            // Construir URI de conexión
            if (!empty(DB_MONGO_USER) && !empty(DB_MONGO_PASS)) {
                $uri = sprintf(
                    "mongodb://%s:%s@%s:%s",
                    DB_MONGO_USER,
                    DB_MONGO_PASS,
                    DB_MONGO_HOST,
                    DB_MONGO_PORT
                );
            } else {
                $uri = sprintf(
                    "mongodb://%s:%s",
                    DB_MONGO_HOST,
                    DB_MONGO_PORT
                );
            }
            
            // Crear cliente MongoDB
            $this->client = new MongoDB\Client($uri);
            $this->database = $this->client->{DB_MONGO_NAME};
            
        } catch (Exception $e) {
            die("Error de conexión a MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Obtener instancia única de la conexión
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Obtener la base de datos
     */
    public function getDatabase() {
        return $this->database;
    }
    
    /**
     * Obtener una colección
     */
    public function getCollection($name) {
        return $this->database->{$name};
    }
    
    /**
     * Insertar documento
     */
    public function insertOne($collection, $document) {
        try {
            $result = $this->database->{$collection}->insertOne($document);
            return $result->getInsertedId();
        } catch (Exception $e) {
            throw new Exception("Error al insertar en MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar documentos
     */
    public function find($collection, $filter = [], $options = []) {
        try {
            $cursor = $this->database->{$collection}->find($filter, $options);
            return $cursor->toArray();
        } catch (Exception $e) {
            throw new Exception("Error al buscar en MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Buscar un documento
     */
    public function findOne($collection, $filter = [], $options = []) {
        try {
            return $this->database->{$collection}->findOne($filter, $options);
        } catch (Exception $e) {
            throw new Exception("Error al buscar en MongoDB: " . $e->getMessage());
        }
    }
    
    /**
     * Contar documentos
     */
    public function count($collection, $filter = []) {
        try {
            return $this->database->{$collection}->countDocuments($filter);
        } catch (Exception $e) {
            throw new Exception("Error al contar en MongoDB: " . $e->getMessage());
        }
    }
}

/**
 * Clase de Conexión a Cassandra
 * Usa cURL para comunicarse con la API REST de Cassandra
 */
class DatabaseCassandra {
    private static $instance = null;
    private $host;
    private $port;
    private $keyspace;
    
    /**
     * Constructor privado (Singleton)
     */
    private function __construct() {
        $this->host = DB_CASSANDRA_HOST;
        $this->port = DB_CASSANDRA_PORT;
        $this->keyspace = DB_CASSANDRA_KEYSPACE;
    }
    
    /**
     * Obtener instancia única
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    /**
     * Ejecutar consulta CQL (simulado)
     * NOTA: Para el proyecto académico, esta es una implementación simplificada
     * En producción usarías el driver DataStax PHP
     */
    public function query($cql) {
        // Por ahora retornamos datos de ejemplo
        // Más adelante implementaremos la conexión real
        return [
            'success' => true,
            'message' => 'Consulta ejecutada (modo simulación)',
            'data' => []
        ];
    }
    
    /**
     * Insertar evento (simulado)
     */
    public function insertEvento($tipo, $datos) {
        // Simulación de inserción
        return [
            'success' => true,
            'timestamp' => time(),
            'tipo' => $tipo,
            'datos' => $datos
        ];
    }
}
?>