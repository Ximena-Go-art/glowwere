<?php
include "conexion.php";
$cnn = conection();

/* Variables iniciales */

$datos = [
    'id_tipo_documento' => '',
    'descripcion' => ''
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_tipo_documento = intval($_POST['id_tipo_documento']);

    $descripcion = mysqli_real_escape_string(
        $cnn,
        trim($_POST['descripcion'])
    );

    /* Nuevo */

    if ($id_tipo_documento == 0) {

        $sql = "
        INSERT INTO tipos_documentos
        (
            descripcion
        )
        VALUES
        (
            '$descripcion'
        )";

    } else {

        /* Modificar */

        $sql = "
        UPDATE tipos_documentos
        SET descripcion = '$descripcion'
        WHERE id_tipo_documento = $id_tipo_documento";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        if ($id_tipo_documento == 0) {
            registrar_accion($cnn, "Tipos de Documentos", "Registró el tipo de documento '$descripcion'");
        } else {
            registrar_accion($cnn, "Tipos de Documentos", "Modificó el tipo de documento #$id_tipo_documento ($descripcion)");
        }

        echo "
        <script>
             window.location='index.php?seccion=tipos_documentos&accion=listar';
        </script>";

    } else {

        echo "Error al guardar: " . mysqli_error($cnn);
    }
}

/* Cargar datos */

if (isset($_GET['id'])) {

    $id_tipo_documento = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM tipos_documentos
    WHERE id_tipo_documento = $id_tipo_documento
    ";

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);

    } else {

        echo "
        <div class='alert alert-danger'>
            No se encontró el tipo de documento.
        </div>";
    }
}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_tipo_documento'] != '') ? 'Editar Tipo de Documento' : 'Nuevo Tipo de Documento' ?></h2>
            <p class="text-muted small">Completa los datos para <?= ($datos['id_tipo_documento'] != '') ? 'actualizar' : 'registrar' ?> el tipo de documento</p>
        </div>
        <a href="index.php?seccion=tipos_documentos&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_tipo_documento" value="<?= $datos['id_tipo_documento'] ?>">

                        <div class="mb-4">
                            <label for="descripcion" class="form-label fw-bold small text-muted">Descripción</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="descripcion" name="descripcion" 
                                   value="<?= htmlspecialchars($datos['descripcion']) ?>" required placeholder="Ej: Factura A, Ticket, etc.">
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