<?php

include "conexion.php";

$cnn = conection();

/* Obtener formas de pago activas */

$sql = "
SELECT
    id_formas_pago,
    descripcion
FROM formas_pagos
WHERE deleted = 0
";

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_formas_pago = intval($_GET['eliminar']);

    $sql_eliminar = "
    UPDATE formas_pago
    SET deleted = 1
    WHERE id_formas_pago = $id_formas_pago
    ";

    if (mysqli_query($cnn, $sql_eliminar)) {

        echo "<script>window.location='index.php?seccion=formas_pago&accion=listar ';</script>";
        exit;

    } else {

        echo "
        <div class='alert alert-danger'>
            Error al eliminar: " . mysqli_error($cnn) . "
        </div>";
    }
}

$resultado = mysqli_query($cnn, $sql);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Formas de Pago</h2>
            <p class="text-muted small">Métodos de pago disponibles en el sistema</p>
        </div>
        <a href="index.php?seccion=formas_pago&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nueva Forma de Pago
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Descripción</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_formas_pago'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['descripcion']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=formas_pago&accion=modificar&id=<?= $fila['id_formas_pago'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="index.php?seccion=formas_pago&accion=listar&eliminar=<?= $fila['id_formas_pago'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar esta forma de pago?');">Eliminar</a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>