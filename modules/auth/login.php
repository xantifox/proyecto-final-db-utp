<?php
/**
 * MÓDULO: AUTENTICACIÓN - LOGIN
 * Página de inicio de sesión del sistema
 * 
 * Base de datos: PostgreSQL (tabla usuarios)
 */

session_start();

// Si ya hay sesión activa, redirigir al dashboard
if (isset($_SESSION['usuario_id'])) {
    header('Location: ../../index.php');
    exit;
}

$mensaje = '';
$tipo_mensaje = '';

// Procesar login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = $_POST['usuario'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Validación básica
    if (empty($usuario) || empty($password)) {
        $mensaje = '❌ Por favor complete todos los campos';
        $tipo_mensaje = 'danger';
    } else {
        // Simular validación (en producción se consulta PostgreSQL)
        $usuarios_validos = [
            'admin' => ['password' => 'admin123', 'nombre' => 'Administrador FONDEP', 'rol' => 'ADMINISTRADOR'],
            'evaluador1' => ['password' => 'eval123', 'nombre' => 'María Elena Torres', 'rol' => 'EVALUADOR'],
            'escuela1' => ['password' => 'escuela123', 'nombre' => 'Director I.E. Arguedas', 'rol' => 'ESCUELA']
        ];
        
        if (isset($usuarios_validos[$usuario]) && $usuarios_validos[$usuario]['password'] === $password) {
            // Login exitoso
            $_SESSION['usuario_id'] = $usuario;
            $_SESSION['usuario_nombre'] = $usuarios_validos[$usuario]['nombre'];
            $_SESSION['usuario_rol'] = $usuarios_validos[$usuario]['rol'];
            
            // Registrar evento en Cassandra (simulado)
            // INSERT INTO eventos_sistema...
            
            header('Location: ../../index.php');
            exit;
        } else {
            $mensaje = '❌ Usuario o contraseña incorrectos';
            $tipo_mensaje = 'danger';
        }
    }
}

$base_url = '/proyecto-final-db';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema FONDEP</title>
    <link rel="stylesheet" href="../../assets/css/styles.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; display: flex; align-items: center; justify-content: center;">

    <div class="container">
        <div style="max-width: 450px; margin: 0 auto;">
            
            <!-- Logo y Título -->
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="background: white; width: 80px; height: 80px; margin: 0 auto 1rem; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2.5rem; box-shadow: 0 10px 25px rgba(0,0,0,0.2);">
                    🎓
                </div>
                <h1 style="color: white; margin: 0 0 0.5rem 0; font-size: 2rem;">Sistema FONDEP</h1>
                <p style="color: rgba(255,255,255,0.9); margin: 0;">
                    Fondo Nacional para el Desarrollo de la Educación Peruana
                </p>
            </div>

            <!-- Card de Login -->
            <div class="card" style="box-shadow: 0 20px 60px rgba(0,0,0,0.3);">
                <div style="padding: 2rem;">
                    
                    <h2 style="margin: 0 0 1.5rem 0; text-align: center; color: var(--dark-color);">
                        Iniciar Sesión
                    </h2>

                    <!-- Mensajes -->
                    <?php if ($mensaje): ?>
                        <div class="alert alert-<?php echo $tipo_mensaje; ?>" style="margin-bottom: 1.5rem;">
                            <?php echo $mensaje; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Formulario -->
                    <form method="POST" action="">
                        
                        <div class="form-group">
                            <label class="form-label">Usuario</label>
                            <input type="text" 
                                   name="usuario" 
                                   class="form-control"
                                   placeholder="Ingrese su usuario"
                                   value="<?php echo htmlspecialchars($_POST['usuario'] ?? ''); ?>"
                                   autofocus
                                   required>
                        </div>

                        <div class="form-group">
                            <label class="form-label">Contraseña</label>
                            <input type="password" 
                                   name="password" 
                                   class="form-control"
                                   placeholder="Ingrese su contraseña"
                                   required>
                        </div>

                        <div class="form-group">
                            <label style="display: flex; align-items: center; font-size: 0.875rem; cursor: pointer;">
                                <input type="checkbox" 
                                       name="recordar" 
                                       style="margin-right: 0.5rem;">
                                Recordar mi sesión
                            </label>
                        </div>

                        <button type="submit" 
                                class="btn btn-primary" 
                                style="width: 100%; padding: 0.75rem; font-size: 1rem;">
                            🔐 Ingresar al Sistema
                        </button>

                    </form>

                    <hr style="margin: 1.5rem 0; border: none; border-top: 1px solid var(--border-color);">

                    <!-- Usuarios de Prueba -->
                    <div style="background-color: #f9fafb; padding: 1rem; border-radius: 6px;">
                        <div style="font-size: 0.875rem; color: #6b7280; margin-bottom: 0.5rem;">
                            <strong>👤 Usuarios de Prueba:</strong>
                        </div>
                        <div style="font-size: 0.75rem; color: #6b7280; line-height: 1.6;">
                            <div><strong>admin</strong> / admin123 (Administrador)</div>
                            <div><strong>evaluador1</strong> / eval123 (Evaluador)</div>
                            <div><strong>escuela1</strong> / escuela123 (Escuela)</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Footer -->
            <div style="text-align: center; margin-top: 2rem; color: rgba(255,255,255,0.8); font-size: 0.875rem;">
                <p style="margin: 0;">
                    Base de Datos II - Proyecto Final<br>
                    Universidad Tecnológica del Perú
                </p>
            </div>

            <!-- Información Técnica -->
            <div class="card" style="margin-top: 1.5rem; background-color: rgba(255,255,255,0.95);">
                <div style="padding: 1rem;">
                    <strong style="color: var(--primary-color);">🔒 Información del Sistema:</strong>
                    <ul style="margin: 0.5rem 0 0 0; padding-left: 1.25rem; font-size: 0.875rem; color: #6b7280;">
                        <li><strong>PostgreSQL:</strong> Validación de credenciales y roles</li>
                        <li><strong>Cassandra:</strong> Registro de eventos de login</li>
                        <li><strong>Sesiones:</strong> PHP Sessions para mantener estado</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>

</body>
</html>