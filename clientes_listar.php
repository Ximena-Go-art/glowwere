<?php
include "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* Eliminación lógica */
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_cliente = intval($_GET['eliminar']);
    $sql_eliminar = "UPDATE clientes SET deleted = 1 WHERE id_cliente = $id_cliente";
    
    if (mysqli_query($cnn, $sql_eliminar)) {
        registrar_accion($cnn, "Clientes", "Eliminó el cliente #$id_cliente");
        echo "<script>window.location='index.php?seccion=clientes&accion=listar';</script>";
    } else {
        echo "<div class='alert alert-danger m-3'>Error al eliminar: " . mysqli_error($cnn) . "</div>";
    }
}

/* Consulta */
$sql = "SELECT id_cliente, cliente, documento FROM clientes WHERE deleted = 0";
$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Clientes</h2>
            <p class="text-muted small">Gestión y listado de clientes registrados</p>
        </div>
        <a href="index.php?seccion=clientes&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Cliente
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Nombre del Cliente</th>
                            <th>Documento</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_cliente'] ?></td>
                            <td>
                                <div class="fw-bold"><?= htmlspecialchars($fila['cliente']) ?></div>
                            </td>
                            <td><?= htmlspecialchars($fila['documento']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=clientes&accion=modificar&id=<?= $fila['id_cliente'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">
                                   Editar
                                </a>
                                <a href="index.php?seccion=clientes&accion=listar&eliminar=<?= $fila['id_cliente'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar este cliente?');">
                                   Eliminar
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>