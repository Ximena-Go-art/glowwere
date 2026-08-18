<?php
include "conexion.php";
$cnn = conection();

/* ============================
   LISTADO DE REGISTROS
============================ */

$sql = "SELECT
            r.*,
            u.usuario
        FROM registros r
        LEFT JOIN usuarios u
            ON r.id_usuario = u.id_usuario
        WHERE r.deleted = 0
        ORDER BY r.fecha_hora DESC";

$result = mysqli_query($cnn, $sql);

/* ============================
   ELIMINAR (BAJA LÓGICA)
============================ */

if (isset($_GET['ideliminar'])) {

    $idEliminar = intval($_GET['ideliminar']);

    $sql = "UPDATE registros
            SET deleted = 1
            WHERE id_registros = $idEliminar";

    $resp = mysqli_query($cnn, $sql);

    if (!$resp) {

        die("Error: " . mysqli_error($cnn));

    } else {

        echo "<script>

            alert('Registro eliminado correctamente.');

            window.location.href='index.php?seccion=registros&accion=listar';

        </script>";

    }

}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Registros del Sistema</h2>
            <p class="text-muted small">Bitácora de actividades del sistema</p>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Fecha y Hora</th>
                            <th>Usuario</th>
                            <th>Sección</th>
                            <th>Acción</th>
                            <th>Link</th>
                            <th>S.O.</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result)>0){ while($fila = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_registros'] ?></td>
                            <td><?= date("d/m/Y H:i:s", strtotime($fila['fecha_hora'])) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['seccion']) ?></span></td>
                            <td><?= htmlspecialchars($fila['accion']) ?></td>
                            <td><small class="text-muted"><?= htmlspecialchars($fila['link']) ?></small></td>
                            <td><?= htmlspecialchars($fila['S.O']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=registros&accion=listar&ideliminar=<?= $fila['id_registros'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar este registro?');">Eliminar</a>
                            </td>
                        </tr>
                        <?php } }else{ ?>
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No existen registros.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>