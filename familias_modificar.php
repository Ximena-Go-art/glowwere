<?php

// ========================================
// CONEXIÓN A LA BASE DE DATOS
// ========================================
include "conexion.php";
$cnn = conection();


// ========================================
// INICIALIZACIÓN DE VARIABLES
// Se utilizan para cargar valores vacíos
// cuando se crea una nueva familia.
// ========================================
$datos = [
    'id_familia' => '',
    'familia' => '',
    'descripcion' => ''
];


// ========================================
// PROCESO DE GUARDADO
// Se ejecuta al enviar el formulario.
// Permite crear o modificar una familia.
// ========================================
if (isset($_POST['btnGuardar'])) {

    // Obtiene y limpia los datos recibidos
    $id_familia = intval($_POST['id_familia']);
    $familia = mysqli_real_escape_string($cnn, trim($_POST['familia']));
    $descripcion = mysqli_real_escape_string($cnn, trim($_POST['descripcion']));

    // ========================================
    // NUEVO REGISTRO
    // Si el ID es 0 o vacío se realiza un INSERT
    // ========================================
    if ($id_familia == 0) {

        $sql = "
        INSERT INTO familias
        (
            familia,
            descripcion
        )
        VALUES
        (
            '$familia',
            '$descripcion'
        )";

    } else {

        // ========================================
        // ACTUALIZACIÓN DE REGISTRO
        // Si existe un ID se actualiza la familia
        // ========================================
        $sql = "
        UPDATE familias
        SET
            familia = '$familia',
            descripcion = '$descripcion'
        WHERE id_familia = $id_familia";
    }

    // Ejecuta la consulta correspondiente
    $resultado = mysqli_query($cnn, $sql);

    // Verifica si la operación fue exitosa
    if ($resultado) {

        // Regresa al listado de familias
        echo "
        <script>
             window.location='index.php?seccion=familias&accion=listar';
        </script>";
        exit;

    } else {

        echo "Error al guardar: " . mysqli_error($cnn);
    }
}


// ========================================
// CARGA DE DATOS PARA MODIFICACIÓN
// Si se recibe un ID por GET se buscan
// los datos de la familia seleccionada.
// ========================================
if (isset($_GET['id'])) {

    $id_familia = intval($_GET['id']);

    $sql = "
        SELECT *
        FROM familias
        WHERE id_familia = $id_familia
    ";

    $resultado = mysqli_query($cnn, $sql);

    // Verifica que la familia exista
    if ($resultado && mysqli_num_rows($resultado) > 0) {

        // Carga los datos en el array para
        // completar automáticamente el formulario
        $datos = mysqli_fetch_assoc($resultado);

    } else {

        echo "
        <div class='alert alert-danger'>
            No se encontró la familia.
        </div>";
    }
}

?>

<!-- =======================================
     VISTA: FORMULARIO DE ALTA Y MODIFICACIÓN
======================================== -->

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold text-secondary">
                        <i class="fas fa-edit me-2"></i> 
                        <?= isset($datos['id_familia']) ? 'Modificar Familia' : 'Nueva Familia' ?>
                    </h4>
                    <a href="index.php?seccion=familias&accion=listar" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                        <i class="fas fa-arrow-left me-1"></i> Volver
                    </a>
                </div>

                <form id="formFamilia" method="POST">
                    <input type="hidden" name="id_familia" value="<?= $datos['id_familia'] ?? '' ?>">

                    <div class="mb-3">
                        <label for="familia" class="form-label fw-semibold">Nombre de la Familia</label>
                        <input type="text" class="form-control rounded-3" id="familia" name="familia" 
                               value="<?= htmlspecialchars($datos['familia'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="descripcion" class="form-label fw-semibold">Descripción</label>
                        <textarea class="form-control rounded-3" id="descripcion" name="descripcion" rows="3"><?= htmlspecialchars($datos['descripcion'] ?? '') ?></textarea>
                    </div>

                    <div class="d-grid">
                        <button type="button" onclick="confirmarGuardado()" class="btn btn-danger rounded-pill py-2 shadow-sm">
                            <i class="fas fa-save me-2"></i> Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarGuardado() {
    Swal.fire({
        title: '¿Confirmar cambios?',
        text: "Los datos de la familia serán actualizados.",
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, guardar'
    }).then((result) => {
        if (result.isConfirmed) {
            // Envío del formulario
            document.getElementById('formFamilia').submit();
        }
    });
}
</script>