<?php
session_start();

if (
    !isset($_SESSION["logueado"]) ||
    $_SESSION["logueado"] != true
) {
    header("Location: login.php");
    exit;
}

// Si la sesion no tiene el rol (usuarios con sesion previa al cambio), consultarlo
if (!isset($_SESSION["rol"])) {
    include "conexion.php";
    $cnn = conection();
    $sqlRol = "SELECT r.nombre AS rol FROM usuarios u INNER JOIN roles r ON u.id_rol = r.id_rol WHERE u.id_usuario = " . intval($_SESSION['id_usuario']);
    $resRol = mysqli_query($cnn, $sqlRol);
    $filaRol = mysqli_fetch_assoc($resRol);
    $_SESSION["rol"] = $filaRol ? $filaRol['rol'] : 'Sin rol';
}

$seccion = "";
$accion = "";

// Obtener parametros
if (isset($_GET["seccion"])) {
    $seccion = $_GET["seccion"];
}

if (isset($_GET["accion"])) {
    $accion = $_GET["accion"];
}

// Construir el nombre del archivo
$archivo = $seccion . "_" . $accion . ".php";

// Si no existe en raiz, buscar en subcarpeta {seccion}/
if (!file_exists($archivo)) {
    $archivo = $seccion . "/" . $seccion . "_" . $accion . ".php";
}

// Si tampoco existe, mostrar inicio
if (!file_exists($archivo)) {
    $archivo = "inicio.php";
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Glowware</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;500;600;700&family=Playfair+Display:wght@600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-page: #FFF9F5;
            --gradient-header: linear-gradient(135deg, #F8BBD0, #C5B4E3);
            --btn-primary: #F48FB1;
            --hover-lavender: #C5B4E3;
            --card-bg: #FCE4EC;
            --detail-blue: #A9DDF5;
            --detail-yellow: #FFE8A3;
            --text-main: #29252A;
        }
        body {
            background-color: var(--bg-page);
            color: var(--text-main);
            font-family: 'Montserrat', sans-serif;
        }
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Playfair Display', serif;
        }
        .navbar {
            background: var(--gradient-header) !important;
        }
        .navbar .dropdown-menu {
            border: none;
            box-shadow: 0 8px 24px rgba(0,0,0,.12);
            border-radius: .5rem;
            padding: .5rem 0;
            background: #fff;
        }
        .navbar .dropdown-menu .dropdown-item {
            padding: .45rem 1.2rem;
            font-size: .9rem;
            color: var(--text-main);
        }
        .navbar .dropdown-menu .dropdown-item:hover {
            background-color: var(--hover-lavender);
            color: #fff;
        }
        .navbar-nav .nav-link {
            font-weight: 500;
            color: #fff !important;
        }
        .navbar-nav .nav-link.active {
            border-bottom: 2px solid #fff;
        }
    </style>
</head>
<body class="bg-light">

    <!-- NAVBAR SUPERIOR -->
    <nav class="navbar navbar-expand-lg navbar-dark shadow-sm fixed-top">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="index.php?seccion=inicio&accion=mostrar">Glowware</a>
            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse" data-bs-target="#menuPrincipal">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="menuPrincipal">
                <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                    <!-- Inicio (link suelto) -->
                    <li class="nav-item">
                        <a class="nav-link <?php echo ($_SESSION["usuario"] == 'inicio' || $_SESSION["usuario"] == '') ? 'active fw-bold' : ''; ?>"
                           href="index.php?seccion=inicio&accion=mostrar">
                            <i class="fas fa-home me-1"></i> Inicio
                        </a>
                    </li>

                    <!-- 1. Operación Diaria -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo (in_array($_SESSION["usuario"], ['ventas','ventas_detalles','cajas','clientes'])) ? 'active fw-bold' : ''; ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-bolt me-1"></i> Operación Diaria
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?seccion=ventas&accion=listar"><i class="fas fa-cash-register me-2 text-muted"></i>Ventas</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=cajas&accion=listar"><i class="fas fa-wallet me-2 text-muted"></i>Caja</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=clientes&accion=listar"><i class="fas fa-user me-2 text-muted"></i>Clientes</a></li>
                        </ul>
                    </li>

                    <!-- 2. Stock y Abastecimiento -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo (in_array($_SESSION["usuario"], ['productos','compras','compra_detalles','proveedores'])) ? 'active fw-bold' : ''; ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-boxes-stacked me-1"></i> Stock y Abastecimiento
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?seccion=productos&accion=listar"><i class="fas fa-cube me-2 text-muted"></i>Productos</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=compras&accion=listar"><i class="fas fa-cart-shopping me-2 text-muted"></i>Compras</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=proveedores&accion=listar"><i class="fas fa-truck me-2 text-muted"></i>Proveedores</a></li>
                        </ul>
                    </li>

                    <!-- 3. Datos Maestros -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo (in_array($_SESSION["usuario"], ['familias','formas_pago'])) ? 'active fw-bold' : ''; ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-database me-1"></i> Datos Maestros
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?seccion=familias&accion=listar"><i class="fas fa-layer-group me-2 text-muted"></i>Familias</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=formas_pago&accion=listar"><i class="fas fa-credit-card me-2 text-muted"></i>Formas de Pago</a></li>
                        </ul>
                    </li>

                    <!-- 4. Administración -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?php echo (in_array($_SESSION["usuario"], ['usuarios','roles','tipos_documentos','registros'])) ? 'active fw-bold' : ''; ?>"
                           href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-gear me-1"></i> Administración
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="index.php?seccion=usuarios&accion=listar"><i class="fas fa-user-gear me-2 text-muted"></i>Usuarios</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=tipos_documentos&accion=listar"><i class="fas fa-id-card me-2 text-muted"></i>Tipos de Documentos</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=registros&accion=listar"><i class="fas fa-clock-rotate-left me-2 text-muted"></i>Registros</a></li>
                        </ul>
                    </li>

                </ul>

                <!-- Usuario a la derecha -->
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle me-1"></i>
                            <?= htmlspecialchars($_SESSION["usuario"]) ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li class="px-3 py-1">
                                <span class="badge bg-danger-subtle text-danger small"><?= htmlspecialchars($_SESSION["rol"]) ?></span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="login.php?logout=1"><i class="fas fa-sign-out-alt me-2"></i>Salir</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="container-fluid pt-4 pb-3" style="margin-top: 60px;">
        <div class="row">
            <div class="col-12 p-4">
                <?php include $archivo; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>