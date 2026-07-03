<?php
session_start();

include("conexion.php");

$cnn = conection();

// Logout
if (isset($_GET['logout'])) {

    $_SESSION = [];
    session_destroy();

    header("Location: login.php");
    exit;
}

// Login
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario = $_POST["username"];
    $password = $_POST["password"];

    $sql = "SELECT * FROM usuarios
            WHERE usuario='$usuario'
            AND actividad_usuario=1";

    $resultado = mysqli_query($cnn, $sql);

    if (mysqli_num_rows($resultado) == 1) {

        $fila = mysqli_fetch_assoc($resultado);

        if ($password == $fila["pass"]) {

            $_SESSION["logueado"] = true;
            $_SESSION["id_usuario"] = $fila["id_usuario"];
            $_SESSION["usuario"] = $fila["usuario"];

            header("Location: index.php");
            exit;
        }
    }

    header("Location: login.php?error=1");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso al Sistema</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="bg-light d-flex align-items-center min-vh-100">

    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                <div class="card border-0 shadow-lg rounded-4 p-4">
                    <div class="text-center mb-4">
                        <i class="fas fa-lock fa-3x text-danger mb-3"></i>
                        <h4 class="fw-bold">Bienvenido</h4>
                        <p class="text-muted small">Ingresa tus credenciales para continuar</p>
                    </div>

                    <form method="POST">
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Usuario</label>
                            <input type="text" name="username" class="form-control rounded-3" required>
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Contraseña</label>
                            <input type="password" name="password" class="form-control rounded-3" required>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-danger rounded-pill shadow-sm">Iniciar Sesión</button>
                        </div>
                    </form>

                    <div class="text-center mt-3">
                        <a href="registro.php" class="text-decoration-none small text-muted">¿No tienes cuenta? Regístrate</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>