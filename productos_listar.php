<?php
include_once "conexion.php";
$cnn = conection();

// Consulta incluyendo la columna 'costo'
$sql = "SELECT p.*, f.familia AS familia 
        FROM productos p 
        LEFT JOIN familias f ON p.id_familia = f.id_familia 
        WHERE p.deleted = 0 ORDER BY p.nombre";
$result = mysqli_query($cnn, $sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Productos</h2>
            <p class="text-muted small">Inventario y stock de productos disponibles</p>
        </div>
        <a href="index.php?seccion=productos&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Producto
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Código</th>
                            <th>Nombre</th>
                            <th>Familia</th>
                            <th>Costo</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($result)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-secondary"><?php echo $fila['codigo']; ?></td>
                            <td><?php echo $fila['nombre']; ?></td>
                            <td><span class="badge bg-light text-dark border"><?php echo $fila['familia']; ?></span></td>
                            <td class="text-danger">$<?php echo number_format($fila['costo'], 2, ',', '.'); ?></td>
                            <td class="fw-bold">$<?php echo number_format($fila['precio'], 2, ',', '.'); ?></td>
                            <td><?php echo number_format($fila['stock'], 0, ',', '.'); ?></td>
                            <td class="text-center">
                                <a href="index.php?seccion=productos&accion=modificar&id=<?php echo $fila['id_producto']; ?>" class="btn btn-sm btn-outline-warning">Editar</a>
                                <button type="button" onclick="confirmarEliminacion(<?php echo $fila['id_producto']; ?>)" class="btn btn-sm btn-outline-danger">Eliminar</button>
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
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?seccion=productos&accion=listar&ideliminar=' + id;
        }
    })
}
</script>