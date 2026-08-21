<?php

// ========================================
// CONEXIÓN A LA BASE DE DATOS
// ========================================
include "conexion.php";
$cnn = conection();


// ========================================
// INICIALIZACIÓN DE VARIABLES
// ========================================
$datos = [
    'id_formas_pago' => '',
    'descripcion' => ''
];


// ========================================
// PROCESO DE GUARDADO
// ========================================
if (isset($_POST['btnGuardar'])) {

    $id_formas_pago = intval($_POST['id_formas_pago']);
    $descripcion = mysqli_real_escape_string(
        $cnn,
        trim($_POST['descripcion'])
    );

    // ========================================
    // NUEVO REGISTRO
    // ========================================
    if ($id_formas_pago == 0) {

        $sql = "
        INSERT INTO formas_pago
        (
            descripcion
        )
        VALUES
        (
            '$descripcion'
        )";

    } else {

        // ========================================
        // MODIFICACIÓN
        // ========================================
        $sql = "
        UPDATE formas_pago
        SET
            descripcion = '$descripcion'
        WHERE id_formas_pago = $id_formas_pago";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        if ($id_formas_pago == 0) {
            registrar_accion($cnn, "Formas de Pago", "Registró la forma de pago '$descripcion'");
        } else {
            registrar_accion($cnn, "Formas de Pago", "Modificó la forma de pago #$id_formas_pago ($descripcion)");
        }

        echo "
        <script>
             window.location='index.php?seccion=formas_pago&accion=listar';
        </script>";
        exit;

    } else {

        echo "Error al guardar: " . mysqli_error($cnn);
    }
}


// ========================================
// CARGA DE DATOS PARA MODIFICACIÓN
// ========================================
if (isset($_GET['id'])) {

    $id_formas_pago = intval($_GET['id']);

    $sql = "
        SELECT *
        FROM formas_pago
        WHERE id_formas_pago = $id_formas_pago
    ";

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);

    } else {

        echo "
        <div class='alert alert-danger'>
            No se encontró la forma de pago.
        </div>";
    }
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_formas_pago'] != '') ? 'Editar Forma de Pago' : 'Nueva Forma de Pago' ?></h2>
            <p class="text-muted small">Completa los datos para <?= ($datos['id_formas_pago'] != '') ? 'actualizar' : 'registrar' ?> la forma de pago</p>
        </div>
        <a href="index.php?seccion=formas_pago&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_formas_pago" value="<?= $datos['id_formas_pago'] ?>">

                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold small text-muted">Descripción</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="descripcion" name="descripcion" 
                                   value="<?= htmlspecialchars($datos['descripcion']) ?>" required placeholder="Ej: Efectivo, Tarjeta, etc.">
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