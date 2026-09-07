<?php
include_once "conexion.php";
include_once "paginador.php";
$cnn = conection();

// Lógica de eliminación
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id = intval($_GET['eliminar']);
    // Restaurar stock antes de eliminar
    $detalle = mysqli_query($cnn, "SELECT id_producto, cantidad_productos FROM ventas_detalles WHERE id_ventas_detalles = $id AND deleted = 0");
    if ($detalle && $fila = mysqli_fetch_assoc($detalle)) {
        mysqli_query($cnn, "UPDATE productos SET stock = stock + " . intval($fila['cantidad_productos']) . " WHERE id_producto = " . intval($fila['id_producto']));
    }
    mysqli_query($cnn, "UPDATE ventas_detalles SET deleted = 1 WHERE id_ventas_detalles = $id");
    registrar_accion($cnn, "Detalle de Ventas", "Eliminó el detalle #$id");
    header("Location: index.php?seccion=ventas_detalles&accion=listar");
    exit;
}

// Filtros
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_venta  = (isset($_GET['f_venta']) && is_numeric($_GET['f_venta'])) ? intval($_GET['f_venta']) : 0;

$condiciones = array("vd.deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "p.nombre LIKE '%$buscarSeguro%'";
}
if ($filtro_venta > 0) {
    $condiciones[] = "vd.id_ventas = $filtro_venta";
}
$where = implode(" AND ", $condiciones);

/* Ordenamiento */
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'id_ventas_detalles';
$direccion = (isset($_GET['dir']) && $_GET['dir'] === 'DESC') ? 'DESC' : 'ASC';

$sql = "SELECT vd.id_ventas_detalles, vd.monto_venta, vd.cantidad_productos, vd.monto_total,
               v.id_ventas, p.nombre AS nombre_producto
        FROM ventas_detalles vd
        INNER JOIN ventas v ON vd.id_ventas = v.id_ventas
        INNER JOIN productos p ON vd.id_producto = p.id_producto
        WHERE $where
        ORDER BY $orden $direccion";

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];
$hayFiltros = ($filtro_buscar != '' || $filtro_venta > 0);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-list-ul" style="color:#F48FB1"></i> Detalle de Ventas</h3>
        <a href="index.php?seccion=ventas_detalles&accion=modificar" class="btn rounded-pill px-4 shadow-sm" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            <i class="fas fa-plus"></i> Nuevo Detalle
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="ventas_detalles">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-5">
                        <label class="form-label small fw-bold text-muted">Buscar producto</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Nombre del producto..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">N° Venta</label>
                        <input type="number" name="f_venta" class="form-control" placeholder="ID de venta..."
                               value="<?= $filtro_venta > 0 ? $filtro_venta : '' ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=ventas_detalles&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color:#FFF9F5">
                        <tr>
                            <th class="ps-4">
                                <?php $nuevaDir = ($direccion === 'ASC') ? 'DESC' : 'ASC'; $sp = $_GET; $sp['orden'] = 'id_ventas_detalles'; $sp['dir'] = $nuevaDir; unset($sp['pagina']); ?>
                                <a href="index.php?<?= http_build_query($sp) ?>" style="text-decoration:none; color:inherit;">ID <?= $direccion === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>' ?></a>
                            </th>
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
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="border-color:#E6C24D;color:#E6C24D"><i class="fas fa-pen"></i></a>
                                <button type="button" onclick="confirmarEliminacion(<?= $fila['id_ventas_detalles'] ?>)" 
                                        class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                        style="border-color:#E57373;color:#E57373"><i class="fas fa-trash"></i></button>
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

<script>
function confirmarEliminacion(id) {
    Swal.fire({
        title: '¿Eliminar este registro?',
        text: "Esta acción no se puede deshacer.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F48FB1',
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