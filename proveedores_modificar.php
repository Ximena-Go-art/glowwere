<?php

include "conexion.php";

$cnn = conection();

/* Variables iniciales */

$datos = [
    'id_proveedor' => '',
    'proveedor' => '',
    'documento' => '',
    'telefono' => '',
    'correo' => '',
    'proveedor_activo' => 1
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_proveedor = intval($_POST['id_proveedor']);

    $proveedor = mysqli_real_escape_string(
        $cnn,
        trim($_POST['proveedor'])
    );

    $documento = mysqli_real_escape_string(
        $cnn,
        trim($_POST['documento'])
    );

    $telefono = mysqli_real_escape_string(
        $cnn,
        trim($_POST['telefono'])
    );

    $correo = mysqli_real_escape_string(
        $cnn,
        trim($_POST['correo'])
    );

    $proveedor_activo =
        isset($_POST['proveedor_activo']) ? 1 : 0;

    /* Nuevo */

    if ($id_proveedor == 0) {

        $sql = "
        INSERT INTO proveedores
        (
            proveedor,
            documento,
            telefono,
            correo,
            proveedor_activo
        )
        VALUES
        (
            '$proveedor',
            '$documento',
            '$telefono',
            '$correo',
            $proveedor_activo
        )";

    } else {

        /* Modificar */

        $sql = "
        UPDATE proveedores
        SET
            proveedor = '$proveedor',
            documento = '$documento',
            telefono = '$telefono',
            correo = '$correo',
            proveedor_activo = $proveedor_activo
        WHERE id_proveedor = $id_proveedor";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        echo "
        <script>
            window.location='index.php?seccion=proveedores&accion=listar';
        </script>";

        exit;

    } else {

        echo "Error al guardar: " . mysqli_error($cnn);
    }
}

/* Cargar datos */

if (isset($_GET['id'])) {

    $id_proveedor = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM proveedores
    WHERE id_proveedor = $id_proveedor
    ";

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);
    }
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_proveedor'] != '') ? 'Editar Proveedor' : 'Nuevo Proveedor' ?></h2>
            <p class="text-muted small">Completa los datos para <?= ($datos['id_proveedor'] != '') ? 'actualizar' : 'registrar' ?> el proveedor</p>
        </div>
        <a href="index.php?seccion=proveedores&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">
            Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_proveedor" value="<?= $datos['id_proveedor'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Proveedor</label>
                            <input type="text" class="form-control form-control-lg rounded-3" name="proveedor" 
                                   value="<?= htmlspecialchars($datos['proveedor']) ?>" required placeholder="Nombre del proveedor">
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Documento</label>
                                <input type="text" class="form-control form-control-lg rounded-3" name="documento" 
                                       value="<?= htmlspecialchars($datos['documento']) ?>" placeholder="Ej: 12345678">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Teléfono</label>
                                <input type="text" class="form-control form-control-lg rounded-3" name="telefono" 
                                       value="<?= htmlspecialchars($datos['telefono']) ?>" placeholder="Ej: 1122334455">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Correo</label>
                            <input type="email" class="form-control form-control-lg rounded-3" name="correo" 
                                   value="<?= htmlspecialchars($datos['correo']) ?>" placeholder="correo@ejemplo.com">
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input type="checkbox" class="form-check-input" name="proveedor_activo" value="1" id="activo"
                                   <?= ($datos['proveedor_activo']) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Proveedor Activo</label>
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