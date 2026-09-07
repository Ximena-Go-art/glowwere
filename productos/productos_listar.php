<?php
include_once "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* Eliminación lógica */
if (isset($_GET['ideliminar']) && is_numeric($_GET['ideliminar'])) {
    $id_producto = intval($_GET['ideliminar']);
    mysqli_query($cnn, "UPDATE productos SET deleted = 1 WHERE id_producto = $id_producto");
    registrar_accion($cnn, "Productos", "Eliminó el producto #$id_producto");
    echo "<script>window.location='index.php?seccion=productos&accion=listar';</script>";
    exit;
}

/* Filtros */
$filtro_buscar  = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_familia = (isset($_GET['f_familia']) && is_numeric($_GET['f_familia'])) ? intval($_GET['f_familia']) : 0;
$filtro_stock   = isset($_GET['f_stock']) ? intval($_GET['f_stock']) : -1;

$condiciones = array("p.deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(p.codigo LIKE '%$buscarSeguro%' OR p.nombre LIKE '%$buscarSeguro%')";
}
if ($filtro_familia > 0) {
    $condiciones[] = "p.id_familia = $filtro_familia";
}
if ($filtro_stock >= 0) {
    $condiciones[] = "p.stock <= 5";
}
$where = implode(" AND ", $condiciones);

$listaFamilias = mysqli_query($cnn, "SELECT id_familia, familia FROM familias WHERE deleted = 0 ORDER BY familia ASC");

$sql = "SELECT p.*, f.familia AS familia 
        FROM productos p 
        LEFT JOIN familias f ON p.id_familia = f.id_familia 
        WHERE $where ORDER BY p.nombre";
$pag = paginar_consulta($cnn, $sql, 10);
$result = $pag['data'];
$hayFiltros = ($filtro_buscar != '' || $filtro_familia > 0 || $filtro_stock >= 0);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Productos</h2>
            <p class="text-muted small">Inventario y stock de productos disponibles</p>
        </div>
        <a href="index.php?seccion=productos&accion=modificar" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            + Nuevo Producto
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="productos">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Código o nombre..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Familia</label>
                        <select name="f_familia" class="form-select">
                            <option value="0">Todas</option>
                            <?php if($listaFamilias){ while($f = mysqli_fetch_assoc($listaFamilias)){ ?>
                                <option value="<?= $f['id_familia'] ?>" <?= ($filtro_familia == $f['id_familia']) ? 'selected' : '' ?>><?= htmlspecialchars($f['familia']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small fw-bold text-muted">Stock</label>
                        <select name="f_stock" class="form-select">
                            <option value="-1">Todos</option>
                            <option value="0" <?= ($filtro_stock === 0) ? 'selected' : '' ?>>Bajo stock (≤5)</option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=productos&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
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
                                <?php $nuevaDir = ($direccion === 'ASC') ? 'DESC' : 'ASC'; $sp = $_GET; $sp['orden'] = 'id_producto'; $sp['dir'] = $nuevaDir; unset($sp['pagina']); ?>
                                <a href="index.php?<?= http_build_query($sp) ?>" style="text-decoration:none; color:inherit;">Código <?= $direccion === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>' ?></a>
                            </th>
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
                                <a href="index.php?seccion=productos&accion=modificar&id=<?php echo $fila['id_producto']; ?>" class="btn btn-sm rounded-pill px-3" title="Editar" style="border-color:#E6C24D;color:#E6C24D"><i class="fas fa-pen"></i></a>
                                <button type="button" onclick="confirmarEliminacion(<?php echo $fila['id_producto']; ?>)" class="btn btn-sm rounded-pill px-3" title="Eliminar" style="border-color:#E57373;color:#E57373"><i class="fas fa-trash"></i></button>
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
        title: '¿Estás seguro?',
        text: "¡No podrás revertir esto!",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#F48FB1',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'index.php?seccion=productos&accion=listar&ideliminar=' + id;
        }
    })
}
</script>