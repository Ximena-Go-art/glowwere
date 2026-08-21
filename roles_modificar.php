<?php

include "conexion.php";

$cnn = conection();

/* Variables iniciales */

$datos = [
    'id_rol' => '',
    'nombre' => ''
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_rol = intval($_POST['id_rol']);

    $nombre = mysqli_real_escape_string(
        $cnn,
        trim($_POST['nombre'])
    );

    /* Nuevo */

    if ($id_rol == 0) {

        $sql = "
        INSERT INTO roles
        (
            nombre
        )
        VALUES
        (
            '$nombre'
        )";

    } else {

        /* Modificar */

        $sql = "
        UPDATE roles
        SET nombre = '$nombre'
        WHERE id_rol = $id_rol";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        $nuevo_id = mysqli_insert_id($cnn);

        if ($id_rol == 0) {
            registrar_accion($cnn, "Roles", "Registró el rol '$nombre'");
        } else {
            registrar_accion($cnn, "Roles", "Modificó el rol #$id_rol ($nombre)");
        }

        echo "
        <script>
             window.location='index.php?seccion=roles&accion=listar';
        </script>";
        exit;

    } else {

        echo "Error al guardar: " . mysqli_error($cnn);
    }
}

/* Cargar datos */

if (isset($_GET['id'])) {

    $id_rol = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM roles
    WHERE id_rol = $id_rol
    ";

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);

    } else {

        echo "
        <div class='alert alert-danger'>
            No se encontró el rol.
        </div>";
    }
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_rol'] != '') ? 'Editar Rol' : 'Nuevo Rol' ?></h2>
            <p class="text-muted small">Completa los datos para <?= ($datos['id_rol'] != '') ? 'actualizar' : 'registrar' ?> el rol</p>
        </div>
        <a href="index.php?seccion=roles&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_rol" value="<?= $datos['id_rol'] ?>">

                        <div class="mb-4">
                            <label for="nombre" class="form-label fw-bold small text-muted">Nombre del Rol</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="nombre" name="nombre" 
                                   value="<?= htmlspecialchars($datos['nombre']) ?>" required placeholder="Ej: Administrador">
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>