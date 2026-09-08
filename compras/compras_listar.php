<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_compra = intval($_GET['eliminar']);

    // Cascade soft-delete a detalles
    mysqli_query($cnn, "UPDATE compra_detalles SET deleted = 1 WHERE id_compra = $id_compra");

    // Soft-delete la compra
    mysqli_query($cnn, "UPDATE compras SET deleted = 1 WHERE id_compra = $id_compra");

    registrar_accion($cnn, "Compras", "Eliminó la compra #$id_compra");

    echo "
    <script>
        window.location='index.php?seccion=compras&accion=listar';
    </script>";
    exit;
}

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_desde  = (isset($_GET['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_desde'])) ? $_GET['f_desde'] : '';
$filtro_hasta  = (isset($_GET['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_hasta'])) ? $_GET['f_hasta'] : '';

$condiciones = array("c.deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "p.proveedor LIKE '%$buscarSeguro%'";
}
if ($filtro_desde != '') {
    $condiciones[] = "c.fecha_registro >= '$filtro_desde'";
}
if ($filtro_hasta != '') {
    $condiciones[] = "c.fecha_registro <= '$filtro_hasta 23:59:59'";
}
$where = implode(" AND ", $condiciones);
$hayFiltros = ($filtro_buscar != '' || $filtro_desde != '' || $filtro_hasta != '');

/* Consulta */

$sql = "
SELECT
    c.id_compra,
    u.usuario,
    p.proveedor,
    td.descripcion AS tipo_documento,
    c.numero_documento,
    c.monto_total,
    c.fecha_registro
FROM compras c

INNER JOIN usuarios u
    ON c.id_usuario = u.id_usuario

INNER JOIN proveedores p
    ON c.id_proveedor = p.id_proveedor

INNER JOIN tipos_documentos td
    ON c.id_tipo_documento = td.id_tipo_documento

WHERE $where
ORDER BY id_compra DESC
";

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Compras</h2>
            <p class="text-muted small">Registro de compras realizadas a proveedores</p>
        </div>
        <a href="index.php?seccion=compras&accion=modificar" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
            + Nueva Compra
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="compras">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">Buscar proveedor</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Nombre del proveedor..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Desde</label>
                        <input type="date" name="f_desde" class="form-control" value="<?= $filtro_desde ?>">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold text-muted">Hasta</label>
                        <input type="date" name="f_hasta" class="form-control" value="<?= $filtro_hasta ?>">
                    </div>
                    <div class="col-md-4 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=compras&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Limpiar</a>
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
                            <th class="ps-4">ID</th>
                            <th>Usuario</th>
                            <th>Proveedor</th>
                            <th>Tipo Doc.</th>
                            <th>N° Documento</th>
                            <th>Total</th>
                            <th>Fecha</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_compra'] ?></td>
                            <td><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['proveedor']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['tipo_documento']) ?></span></td>
                            <td><?= htmlspecialchars($fila['numero_documento']) ?></td>
                            <td class="fw-bold text-danger">$ <?= number_format($fila['monto_total'], 2, ',', '.') ?></td>
                            <td><?= date('d/m/Y', strtotime($fila['fecha_registro'])) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=compras&accion=modificar&id=<?= $fila['id_compra'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="border-color:#E6C24D;color:#E6C24D"><i class="fas fa-pen"></i></a>
                                <a href="index.php?seccion=compra_detalles&accion=listar&f_compra=<?= $fila['id_compra'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Ver detalles"
                                   style="border-color:#4A90D9;color:#4A90D9"><i class="fas fa-eye"></i></a>
                                <a href="index.php?seccion=compras&accion=listar&eliminar=<?= $fila['id_compra'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="border-color:#E57373;color:#E57373"
                                   onclick="return confirm('¿Está seguro de eliminar esta compra?');"><i class="fas fa-trash"></i></a>
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