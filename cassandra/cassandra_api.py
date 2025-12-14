"""
API REST para integración PHP-Cassandra
Proyecto FONDEP - Base de Datos II
Servidor: Cassandra Remoto
"""

from flask import Flask, request, jsonify
from flask_cors import CORS
from cassandra.cluster import Cluster
from cassandra.auth import PlainTextAuthProvider
from cassandra.query import SimpleStatement, ConsistencyLevel
import json
from datetime import datetime
import uuid

app = Flask(__name__)
CORS(app)  # Permitir peticiones desde PHP

# ========================================
# CONFIGURACIÓN DE CONEXIÓN A CASSANDRA
# ========================================

# TODO: MODIFICAR ESTOS DATOS SEGÚN TU SERVIDOR
CASSANDRA_CONFIG = {
    'contact_points': ['192.168.192.197'],  # ⚠️ CAMBIAR POR IP DE TU SERVIDOR
    'port': 9042,
    'keyspace': 'fondep',
    'username': 'usuario_app',  # ⚠️ Si no usa autenticación, comentar estas líneas
    'password': '123'  # ⚠️
}

cluster = None
session = None

def connect_cassandra():
    """
    Establece conexión con Cassandra remoto
    """
    global cluster, session
    
    try:
        print("🔄 Intentando conectar con Cassandra...")
        print(f"   Servidor: {CASSANDRA_CONFIG['contact_points'][0]}:{CASSANDRA_CONFIG['port']}")
        
        # Si el servidor requiere autenticación
        if 'username' in CASSANDRA_CONFIG and 'password' in CASSANDRA_CONFIG:
            auth_provider = PlainTextAuthProvider(
                username=CASSANDRA_CONFIG['username'],
                password=CASSANDRA_CONFIG['password']
            )
            cluster = Cluster(
                contact_points=CASSANDRA_CONFIG['contact_points'],
                port=CASSANDRA_CONFIG['port'],
                auth_provider=auth_provider,
                protocol_version=4  # Compatible con Cassandra 2.1+
            )
        else:
            # Sin autenticación
            cluster = Cluster(
                contact_points=CASSANDRA_CONFIG['contact_points'],
                port=CASSANDRA_CONFIG['port'],
                protocol_version=4
            )
        
        # Conectar al cluster
        session = cluster.connect()
        
        # Seleccionar keyspace
        session.set_keyspace(CASSANDRA_CONFIG['keyspace'])
        
        print(f"✅ Conectado exitosamente a Cassandra")
        print(f"   Keyspace activo: {CASSANDRA_CONFIG['keyspace']}")
        
        return True
        
    except Exception as e:
        print(f"❌ Error conectando a Cassandra: {e}")
        print(f"   Verifica que el servidor esté accesible y las credenciales sean correctas")
        return False

# ========================================
# ENDPOINTS DE LA API
# ========================================

@app.route('/ping', methods=['GET'])
def ping():
    """
    Verificar que la API está funcionando
    """
    if session is None:
        return jsonify({
            "status": "error",
            "message": "API funcionando pero Cassandra no conectado"
        }), 503
    
    return jsonify({
        "status": "ok",
        "message": "API Cassandra funcionando correctamente",
        "server": CASSANDRA_CONFIG['contact_points'][0],
        "keyspace": CASSANDRA_CONFIG['keyspace']
    })


@app.route('/health', methods=['GET'])
def health_check():
    """
    Verificar salud de la conexión con Cassandra
    """
    try:
        # Ejecutar query simple para verificar conexión
        result = session.execute("SELECT release_version FROM system.local")
        version = list(result)[0].release_version
        
        return jsonify({
            "status": "healthy",
            "cassandra_version": version,
            "keyspace": CASSANDRA_CONFIG['keyspace']
        })
    except Exception as e:
        return jsonify({
            "status": "unhealthy",
            "error": str(e)
        }), 500


@app.route('/query', methods=['POST'])
def execute_query():
    """
    Ejecutar query CQL genérica
    Body: {
        "query": "SELECT * FROM tabla WHERE campo = ?",
        "params": [valor1, valor2]
    }
    """
    try:
        data = request.get_json()
        query = data.get('query')
        params = data.get('params', [])
        
        if not query:
            return jsonify({"error": "Query no proporcionado"}), 400
        
        # Ejecutar query
        statement = SimpleStatement(query, consistency_level=ConsistencyLevel.ONE)
        
        if params:
            rows = session.execute(statement, params)
        else:
            rows = session.execute(statement)
        
        # Convertir resultados a lista de diccionarios
        results = []
        for row in rows:
            row_dict = {}
            for key, value in row._asdict().items():
                # Convertir tipos especiales a formato JSON-serializable
                if isinstance(value, uuid.UUID):
                    row_dict[key] = str(value)
                elif isinstance(value, datetime):
                    row_dict[key] = value.isoformat()
                else:
                    row_dict[key] = value
            results.append(row_dict)
        
        return jsonify({
            "success": True,
            "rows": results,
            "count": len(results)
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


# ========================================
# ENDPOINTS ESPECÍFICOS PARA FONDEP
# ========================================

@app.route('/eventos/postulacion', methods=['POST'])
def registrar_evento_postulacion():
    """
    Registrar evento de postulación en time-series
    Body: {
        "convocatoria_id": 1,
        "postulacion_id": "POST-2024-001",
        "escuela_id": "ESC-LIM-001",
        "tipo_evento": "postulacion_enviada",
        "datos_evento": {...}
    }
    """
    try:
        data = request.get_json()
        
        query = """
        INSERT INTO eventos_postulacion 
        (convocatoria_id, evento_id, timestamp, postulacion_id, 
         escuela_id, tipo_evento, datos_evento)
        VALUES (?, ?, ?, ?, ?, ?, ?)
        """
        
        # Generar UUID único para el evento
        evento_id = str(uuid.uuid4())
        timestamp = datetime.now()
        
        session.execute(query, (
            data['convocatoria_id'],
            uuid.UUID(evento_id),
            timestamp,
            data['postulacion_id'],
            data['escuela_id'],
            data['tipo_evento'],
            json.dumps(data.get('datos_evento', {}))
        ))
        
        return jsonify({
            "success": True,
            "message": "Evento registrado correctamente",
            "evento_id": evento_id,
            "timestamp": timestamp.isoformat()
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


@app.route('/eventos/postulacion/<int:convocatoria_id>', methods=['GET'])
def obtener_eventos_postulacion(convocatoria_id):
    """
    Obtener eventos de una convocatoria específica
    Query params:
        - limit: número máximo de eventos (default: 100)
        - tipo: filtrar por tipo de evento (opcional)
    """
    try:
        limit = request.args.get('limit', 100, type=int)
        tipo_evento = request.args.get('tipo', None)
        
        if tipo_evento:
            query = """
            SELECT * FROM eventos_postulacion 
            WHERE convocatoria_id = ? AND tipo_evento = ?
            ORDER BY timestamp DESC
            LIMIT ?
            """
            rows = session.execute(query, (convocatoria_id, tipo_evento, limit))
        else:
            query = """
            SELECT * FROM eventos_postulacion 
            WHERE convocatoria_id = ?
            ORDER BY timestamp DESC
            LIMIT ?
            """
            rows = session.execute(query, (convocatoria_id, limit))
        
        # Procesar resultados
        eventos = []
        for row in rows:
            evento = {
                'convocatoria_id': row.convocatoria_id,
                'evento_id': str(row.evento_id),
                'timestamp': row.timestamp.isoformat(),
                'postulacion_id': row.postulacion_id,
                'escuela_id': row.escuela_id,
                'tipo_evento': row.tipo_evento,
                'datos_evento': json.loads(row.datos_evento) if row.datos_evento else {}
            }
            eventos.append(evento)
        
        return jsonify({
            "success": True,
            "convocatoria_id": convocatoria_id,
            "eventos": eventos,
            "count": len(eventos)
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


@app.route('/metricas/registrar', methods=['POST'])
def registrar_metrica():
    """
    Registrar métrica de convocatoria
    Body: {
        "convocatoria_id": 1,
        "metrica": "postulaciones_recibidas",
        "valor": 150.0,
        "region": "Lima",
        "metadata": {...}
    }
    """
    try:
        data = request.get_json()
        
        query = """
        INSERT INTO metricas_convocatoria 
        (convocatoria_id, fecha_registro, metrica, valor, region, metadata)
        VALUES (?, ?, ?, ?, ?, ?)
        """
        
        session.execute(query, (
            data['convocatoria_id'],
            datetime.now(),
            data['metrica'],
            float(data['valor']),
            data.get('region', 'Nacional'),
            json.dumps(data.get('metadata', {}))
        ))
        
        return jsonify({
            "success": True,
            "message": "Métrica registrada correctamente"
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


@app.route('/metricas/convocatoria/<int:convocatoria_id>', methods=['GET'])
def obtener_metricas_convocatoria(convocatoria_id):
    """
    Obtener métricas históricas de una convocatoria
    Query params:
        - metrica: nombre específico de métrica (opcional)
        - region: filtrar por región (opcional)
        - limit: número máximo de registros (default: 100)
    """
    try:
        metrica = request.args.get('metrica', None)
        region = request.args.get('region', None)
        limit = request.args.get('limit', 100, type=int)
        
        # Construir query según filtros
        if metrica and region:
            query = """
            SELECT * FROM metricas_convocatoria 
            WHERE convocatoria_id = ? AND metrica = ? AND region = ?
            ORDER BY fecha_registro DESC
            LIMIT ?
            """
            rows = session.execute(query, (convocatoria_id, metrica, region, limit))
        elif metrica:
            query = """
            SELECT * FROM metricas_convocatoria 
            WHERE convocatoria_id = ? AND metrica = ?
            ORDER BY fecha_registro DESC
            LIMIT ?
            """
            rows = session.execute(query, (convocatoria_id, metrica, limit))
        else:
            query = """
            SELECT * FROM metricas_convocatoria 
            WHERE convocatoria_id = ?
            ORDER BY fecha_registro DESC
            LIMIT ?
            """
            rows = session.execute(query, (convocatoria_id, limit))
        
        # Procesar resultados
        metricas = []
        for row in rows:
            metrica_data = {
                'convocatoria_id': row.convocatoria_id,
                'fecha_registro': row.fecha_registro.isoformat(),
                'metrica': row.metrica,
                'valor': float(row.valor),
                'region': row.region,
                'metadata': json.loads(row.metadata) if row.metadata else {}
            }
            metricas.append(metrica_data)
        
        return jsonify({
            "success": True,
            "convocatoria_id": convocatoria_id,
            "metricas": metricas,
            "count": len(metricas)
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


@app.route('/metricas/agregadas/<int:convocatoria_id>', methods=['GET'])
def obtener_metricas_agregadas(convocatoria_id):
    """
    Obtener resumen estadístico de métricas
    """
    try:
        # Obtener todas las métricas
        query = """
        SELECT metrica, valor, region 
        FROM metricas_convocatoria 
        WHERE convocatoria_id = ?
        """
        rows = session.execute(query, (convocatoria_id,))
        
        # Procesar agregaciones en Python
        metricas_por_tipo = {}
        metricas_por_region = {}
        
        for row in rows:
            # Agrupar por tipo de métrica
            if row.metrica not in metricas_por_tipo:
                metricas_por_tipo[row.metrica] = []
            metricas_por_tipo[row.metrica].append(float(row.valor))
            
            # Agrupar por región
            if row.region not in metricas_por_region:
                metricas_por_region[row.region] = []
            metricas_por_region[row.region].append(float(row.valor))
        
        # Calcular estadísticas
        resumen = {
            'por_metrica': {},
            'por_region': {}
        }
        
        for metrica, valores in metricas_por_tipo.items():
            resumen['por_metrica'][metrica] = {
                'total': sum(valores),
                'promedio': sum(valores) / len(valores),
                'minimo': min(valores),
                'maximo': max(valores),
                'registros': len(valores)
            }
        
        for region, valores in metricas_por_region.items():
            resumen['por_region'][region] = {
                'total': sum(valores),
                'promedio': sum(valores) / len(valores),
                'registros': len(valores)
            }
        
        return jsonify({
            "success": True,
            "convocatoria_id": convocatoria_id,
            "resumen": resumen
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


@app.route('/analisis/timeline/<int:convocatoria_id>', methods=['GET'])
def obtener_timeline_eventos(convocatoria_id):
    """
    Obtener línea de tiempo de eventos para análisis
    """
    try:
        query = """
        SELECT tipo_evento, timestamp, escuela_id
        FROM eventos_postulacion 
        WHERE convocatoria_id = ?
        ORDER BY timestamp ASC
        """
        rows = session.execute(query, (convocatoria_id,))
        
        # Agrupar por tipo de evento
        timeline = {}
        for row in rows:
            tipo = row.tipo_evento
            if tipo not in timeline:
                timeline[tipo] = []
            
            timeline[tipo].append({
                'timestamp': row.timestamp.isoformat(),
                'escuela_id': row.escuela_id
            })
        
        # Calcular estadísticas por tipo
        estadisticas = {}
        for tipo, eventos in timeline.items():
            estadisticas[tipo] = {
                'total': len(eventos),
                'primer_evento': eventos[0]['timestamp'] if eventos else None,
                'ultimo_evento': eventos[-1]['timestamp'] if eventos else None
            }
        
        return jsonify({
            "success": True,
            "convocatoria_id": convocatoria_id,
            "timeline": timeline,
            "estadisticas": estadisticas
        })
        
    except Exception as e:
        return jsonify({
            "success": False,
            "error": str(e)
        }), 500


# ========================================
# INICIALIZACIÓN
# ========================================

if __name__ == '__main__':
    print("=" * 60)
    print("API REST CASSANDRA - PROYECTO FONDEP")
    print("=" * 60)
    
    # Conectar a Cassandra al iniciar
    if connect_cassandra():
        print("\n🚀 Iniciando servidor Flask...")
        print("   URL: http://127.0.0.1:5000")
        print("   Presiona CTRL+C para detener")
        print("=" * 60)
        
        # Iniciar API
        app.run(
            host='127.0.0.1',
            port=5000,
            debug=True
        )
    else:
        print("\n❌ No se pudo conectar a Cassandra. Verifica:")
        print("   1. El servidor Cassandra está ejecutándose")
        print("   2. La IP y puerto son correctos")
        print("   3. El firewall permite conexiones al puerto 9042")
        print("   4. Las credenciales son válidas")
        print("=" * 60)