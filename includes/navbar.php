<?php
/**
 * NAVBAR - Sistema FONDEP
 * Menú de navegación principal con todos los módulos
 */

// Usar la variable $base_url definida en header.php
if (!isset($base_url)) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $base_url = $protocol . "://" . $host;
}

// Detectar página activa para resaltar en menú
$uri = $_SERVER['REQUEST_URI'];
$current_page = basename($uri, '.php');
?>
<nav class="navbar">
    <div class="container">
        <div class="navbar-brand">
            <a href="<?php echo $base_url; ?>/">
                <strong>FONDEP</strong> | Sistema de Gestión
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li>
                <a href="<?php echo $base_url; ?>/" 
                   class="<?php echo ($current_page == 'index' || $current_page == '') ? 'active' : ''; ?>">
                    📊 Dashboard
                </a>
            </li>
            
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">📢 Convocatorias</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>/modules/convocatorias/listar.php">📋 Listar</a></li>
                    <li><a href="<?php echo $base_url; ?>/modules/convocatorias/crear.php">➕ Nueva Convocatoria</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">📝 Postulaciones</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>/modules/postulaciones/listar.php">📋 Listar</a></li>
                    <li><a href="<?php echo $base_url; ?>/modules/postulaciones/crear.php">➕ Nueva Postulación</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">⭐ Evaluaciones</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>/modules/evaluaciones/listar.php">📋 Mis Evaluaciones</a></li>
                    <li><a href="<?php echo $base_url; ?>/modules/evaluaciones/resultados.php">📊 Resultados</a></li>
                </ul>
            </li>
            
            <li class="dropdown">
                <a href="#" class="dropdown-toggle">📈 Reportes</a>
                <ul class="dropdown-menu">
                    <li><a href="<?php echo $base_url; ?>/modules/reportes/estadisticas.php">📊 Estadísticas</a></li>
                    <li><a href="<?php echo $base_url; ?>/modules/reportes/auditoria.php">🔍 Auditoría</a></li>
                    <li><a href="<?php echo $base_url; ?>/modules/reportes/seguimiento.php">📅 Seguimiento</a></li>
                </ul>
            </li>
            
            <li>
                <a href="<?php echo $base_url; ?>/modules/auth/login.php" class="btn-login">
                    🔐 Salir
                </a>
            </li>
        </ul>
    </div>
</nav>

<style>
/* Estilos adicionales para dropdown */
.navbar-menu .dropdown {
    position: relative;
}

.navbar-menu .dropdown-toggle {
    cursor: pointer;
    padding-bottom: 1.5rem; /* Espacio invisible para mantener hover */
}

.navbar-menu .dropdown-menu {
    display: none;
    position: absolute;
    top: calc(100% - 0.5rem); /* Reducir gap para evitar que desaparezca */
    left: 0;
    background: white;
    min-width: 220px;
    box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    border-radius: 8px;
    padding: 0.5rem 0;
    z-index: 1000;
    border: 1px solid #e5e7eb;
    animation: fadeIn 0.2s ease-in-out;
}

@keyframes fadeIn {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

/* Mantener dropdown visible cuando mouse está sobre él o sobre el toggle */
.navbar-menu .dropdown:hover .dropdown-menu,
.navbar-menu .dropdown .dropdown-menu:hover {
    display: block;
}

/* Área invisible para mantener conexión entre toggle y menú */
.navbar-menu .dropdown::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    height: 0.5rem; /* Puente invisible */
    display: block;
}

.navbar-menu .dropdown-menu li {
    list-style: none;
}

.navbar-menu .dropdown-menu a {
    display: block;
    padding: 0.75rem 1.25rem;
    color: var(--text-color);
    text-decoration: none;
    transition: all 0.2s ease;
    font-size: 0.9rem;
}

.navbar-menu .dropdown-menu a:hover {
    background-color: #f3f4f6;
    color: var(--primary-color);
    padding-left: 1.5rem; /* Efecto de desplazamiento */
}

.navbar-menu .active {
    color: var(--primary-color);
    font-weight: 600;
}

.btn-login {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white !important;
    padding: 0.5rem 1rem;
    border-radius: 6px;
    transition: all 0.3s ease;
}

.btn-login:hover {
    background: linear-gradient(135deg, #dc2626, #b91c1c);
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(220, 38, 38, 0.4);
}

/* Indicador visual de dropdown */
.navbar-menu .dropdown-toggle::after {
    content: '▼';
    font-size: 0.7rem;
    margin-left: 0.4rem;
    transition: transform 0.2s ease;
}

.navbar-menu .dropdown:hover .dropdown-toggle::after {
    transform: rotate(180deg);
}
</style>