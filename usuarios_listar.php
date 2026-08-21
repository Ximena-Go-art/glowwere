<?php
include "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* ============================
   LISTADO DE USUARIOS
============================ */

$sql = "SELECT
            u.*,
            r.nombre AS rol
        FROM usuarios u
        LEFT JOIN roles r
            ON u.id_rol = r.id_rol
        WHERE u.deleted = 0
        ORDER BY u.usuario ASC";

$pag = paginar_consulta($cnn, $sql, 10);
$result = $pag['data'];

/* ============================
   ELIMINAR (BAJA LÓGICA)
============================ */

if (isset($_GET['ideliminar'])) {

    $idEliminar = intval($_GET['ideliminar']);

    $sql = "UPDATE usuarios
            SET deleted = 1
            WHERE id_usuario = $idEliminar";

    $resp = mysqli_query($cnn, $sql);

    if (!$resp) {

        die("Error: " . mysqli_error($cnn));

    } else {

        registrar_accion($cnn, "Usuarios", "Eliminó el usuario #$idEliminar");

        echo "<script>

            alert('Usuario eliminado correctamente.');

            window.location.href='index.php?seccion=usuarios&accion=listar';

        </script>";

    }

}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Usuarios</h2>
            <p class="text-muted small">Gestión de usuarios del sistema</p>
        </div>
        <a href="index.php?seccion=usuarios&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Usuario
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Usuario</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Fecha Registro</th>
                            <th>Activo</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(mysqli_num_rows($result)>0){ while($fila = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_usuario'] ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td><?= htmlspecialchars($fila['email']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= ($fila['rol']!="") ? htmlspecialchars($fila['rol']) : "Sin rol" ?></span></td>
                            <td><?= date("d/m/Y H:i", strtotime($fila['fecha_registro'])) ?></td>
                            <td>
                                <?php if($fila['actividad_usuario']==1){ ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Activo</span>
                                <?php }else{ ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill">Inactivo</span>
                                <?php } ?>
                            </td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=usuarios&accion=modificar&id=<?= $fila['id_usuario'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="index.php?seccion=usuarios&accion=listar&ideliminar=<?= $fila['id_usuario'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar este usuario?');">Eliminar</a>
                            </td>
                        </tr>
                        <?php } }else{ ?>
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No existen usuarios registrados.</td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>