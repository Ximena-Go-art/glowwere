<?php
include_once "conexion.php";
$cnn = conection();

// Lógica de eliminación con SweetAlert
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    mysqli_query($cnn, "UPDATE compra_detalles SET deleted = 1 WHERE id_compra_detalle = $id");
    registrar_accion($cnn, "Detalle de Compras", "Eliminó el detalle #$id");
    header("Location: index.php?seccion=compra_detalles&accion=listar");
    exit;
}

// Consulta corregida para traer el nombre del producto
$sql = "SELECT cd.*, c.id_compra, p.nombre AS nombre_producto
        FROM compra_detalles cd
        INNER JOIN compras c ON cd.id_compra = c.id_compra
        INNER JOIN productos p ON cd.id_producto = p.id_producto
        WHERE cd.deleted = 0
        ORDER BY cd.id_compra_detalle DESC";

$resultado = mysqli_query($cnn, $sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-list-ul text-danger"></i> Detalle de Compras</h3>
        <a href="index.php?seccion=compra_detalles&accion=modificar" class="btn btn-danger rounded-pill px-4 shadow-sm">
            <i class="fas fa-plus"></i> Nuevo Detalle
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Compra</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $fila['id_compra_detalle'] ?></td>
                            <td><span class="badge bg-secondary">Compra #<?= $fila['id_compra'] ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                            <td><?= number_format($fila['cantidad'], 0) ?></td>
                            <td>$<?= number_format($fila['precio_unitario'], 2, ',', '.') ?></td>
                            <td class="fw-bold text-danger">$<?= number_format($fila['precio_total'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <a href="index.php?seccion=compra_detalles&accion=modificar&id=<?= $fila['id_compra_detalle'] ?>" 
                                   class="btn btn-sm btn-outline-warning">Editar</a>
                                <button type="button" onclick="confirmarEliminacion(<?= $fila['id_compra_detalle'] ?>)" 
                                        class="btn btn-sm btn-outline-danger">Eliminar</button>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar este detalle?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        confirmButtonText: 'Sí, borrar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?seccion=compra_detalles&accion=listar&eliminar=' + id;
        }
    })
}
</script>