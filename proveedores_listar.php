<?php

include "conexion.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_proveedor = intval($_GET['eliminar']);

    $sql_eliminar = "
    UPDATE proveedores
    SET deleted = 1
    WHERE id_proveedor = $id_proveedor
    ";

    if (mysqli_query($cnn, $sql_eliminar)) {

        registrar_accion($cnn, "Proveedores", "Eliminó el proveedor #$id_proveedor");

        echo "
        <script>
            window.location='index.php?seccion=proveedores&accion=listar';
        </script>";
        exit;
    }
}

/* Consulta */

$sql = "
SELECT
    id_proveedor,
    proveedor,
    documento,
    telefono,
    correo,
    proveedor_activo
FROM proveedores
WHERE deleted = 0
";

$resultado = mysqli_query($cnn, $sql);

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Proveedores</h2>
            <p class="text-muted small">Gestión y listado de proveedores registrados</p>
        </div>
        <a href="index.php?seccion=proveedores&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Proveedor
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Proveedor</th>
                            <th>Documento</th>
                            <th>Teléfono</th>
                            <th>Correo</th>
                            <th>Activo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_proveedor'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['proveedor']) ?></td>
                            <td><?= htmlspecialchars($fila['documento']) ?></td>
                            <td><?= htmlspecialchars($fila['telefono']) ?></td>
                            <td><?= htmlspecialchars($fila['correo']) ?></td>
                            <td>
                                <?php if($fila['proveedor_activo']){ ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activo</span>
                                <?php }else{ ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=proveedores&accion=modificar&id=<?= $fila['id_proveedor'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">
                                   Editar
                                </a>
                                <a href="index.php?seccion=proveedores&accion=listar&eliminar=<?= $fila['id_proveedor'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar este proveedor?');">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>