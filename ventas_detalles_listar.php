<?php
include_once "conexion.php";
$cnn = conection();

// Lógica de eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    mysqli_query($cnn, "UPDATE ventas_detalles SET deleted = 1 WHERE id_ventas_detalles = $id");
    registrar_accion($cnn, "Detalle de Ventas", "Eliminó el detalle #$id");
    header("Location: index.php?seccion=ventas_detalles&accion=listar");
    exit;
}

// Consulta corregida: ahora incluye 'p.nombre AS nombre_producto'
$sql = "SELECT vd.*, v.id_ventas, p.nombre AS nombre_producto
        FROM ventas_detalles vd
        INNER JOIN ventas v ON vd.id_ventas = v.id_ventas
        INNER JOIN productos p ON vd.id_producto = p.id_producto
        WHERE vd.deleted = 0
        ORDER BY vd.id_ventas_detalles DESC";

$resultado = mysqli_query($cnn, $sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-list-ul text-danger"></i> Detalle de Ventas</h3>
        <a href="index.php?seccion=ventas_detalles&accion=modificar" class="btn btn-danger rounded-pill px-4 shadow-sm">
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
                            <th>Venta</th>
                            <th>Producto</th>
                            <th>Precio Venta</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 text-muted">#<?= $fila['id_ventas_detalles'] ?></td>
                            <td><span class="badge bg-secondary">Venta #<?= $fila['id_ventas'] ?></span></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['nombre_producto']) ?></td>
                            <td>$<?= number_format($fila['monto_venta'], 2, ',', '.') ?></td>
                            <td><?= $fila['cantidad_productos'] ?></td>
                            <td class="fw-bold text-danger">$<?= number_format($fila['monto_total'], 2, ',', '.') ?></td>
                            <td class="text-center">
                                <a href="index.php?seccion=ventas_detalles&accion=modificar&id=<?= $fila['id_ventas_detalles'] ?>" 
                                   class="btn btn-sm btn-outline-warning">Editar</a>
                                <button type="button" onclick="confirmarEliminacion(<?= $fila['id_ventas_detalles'] ?>)" 
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
        title: '¿Eliminar este registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?seccion=ventas_detalles&accion=listar&eliminar=' + id;
        }
    })
}
</script>