<?php
include "conexion.php";
$cnn = conection();

/* Variables iniciales */

$datos = [
    'id_cliente' => '',
    'cliente' => '',
    'documento' => ''
];


/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_cliente = intval($_POST['id_cliente']);

    $cliente = mysqli_real_escape_string(
        $cnn,
        trim($_POST['cliente'])
    );

    $documento = mysqli_real_escape_string(
        $cnn,
        trim($_POST['documento'])
    );


    /* Nuevo */

    if ($id_cliente == 0) {

        $sql = "
        INSERT INTO clientes
        (
            cliente,
            documento
        )
        VALUES
        (
            '$cliente',
            '$documento'
        )";


    } else {

        /* Modificar */

        $sql = "
        UPDATE clientes
        SET
            cliente = '$cliente',
            documento = '$documento'
        WHERE id_cliente = $id_cliente";

    }


    $resultado = mysqli_query($cnn, $sql);


    if ($resultado) {

        $nuevo_id = mysqli_insert_id($cnn);

        if ($id_cliente == 0) {
            registrar_accion($cnn, "Clientes", "Registró el cliente '$cliente'");
        } else {
            registrar_accion($cnn, "Clientes", "Modificó el cliente #$id_cliente ($cliente)");
        }

        echo "
        <script>

        Swal.fire({
            title: '¡Guardado correctamente!',
            text: 'El cliente fue registrado con éxito.',
            icon: 'success',
            confirmButtonText: 'Aceptar'
        }).then((result)=>{

            if(result.isConfirmed){

                window.location='index.php?seccion=clientes&accion=listar';

            }

        });

        </script>";


    } else {


        echo "
        <script>

        Swal.fire({
            title: 'Error',
            text: 'No se pudo guardar el cliente.',
            icon: 'error',
            confirmButtonText: 'Aceptar'
        });

        </script>";


    }

}



/* Cargar datos */

if (isset($_GET['id'])) {

    $id_cliente = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM clientes
    WHERE id_cliente = $id_cliente
    ";


    $resultado = mysqli_query($cnn, $sql);


    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);


    } else {


        echo "
        <script>

        Swal.fire({
            title: 'Cliente no encontrado',
            text: 'No existe un cliente con ese ID.',
            icon: 'warning',
            confirmButtonText: 'Aceptar'
        });

        </script>";

    }

}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_cliente'] != '') ? 'Editar Cliente' : 'Nuevo Cliente' ?></h2>
            <p class="text-muted small">Completa los datos para <?= ($datos['id_cliente'] != '') ? 'actualizar' : 'registrar' ?> el perfil</p>
        </div>
        <a href="index.php?seccion=clientes&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">
            Volver
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_cliente" value="<?= $datos['id_cliente'] ?>">

                        <div class="mb-3">
                            <label for="cliente" class="form-label fw-bold small text-muted">Nombre del Cliente</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="cliente" name="cliente" 
                                   value="<?= htmlspecialchars($datos['cliente']) ?>" required placeholder="Nombre completo">
                        </div>

                        <div class="mb-4">
                            <label for="documento" class="form-label fw-bold small text-muted">Documento</label>
                            <input type="text" class="form-control form-control-lg rounded-3" id="documento" name="documento" 
                                   value="<?= htmlspecialchars($datos['documento']) ?>" required placeholder="Ej: 12345678">
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
