<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_ventas = intval($_GET['eliminar']);

    // Restaurar stock de los productos de esta venta
    $detalles = mysqli_query($cnn, "SELECT id_producto, cantidad_productos FROM ventas_detalles WHERE id_ventas = $id_ventas AND deleted = 0");
    if ($detalles) {
        while ($d = mysqli_fetch_assoc($detalles)) {
            mysqli_query($cnn, "UPDATE productos SET stock = stock + " . intval($d['cantidad_productos']) . " WHERE id_producto = " . intval($d['id_producto']));
        }
    }

    // Cascade soft-delete a detalles y pagos
    mysqli_query($cnn, "UPDATE ventas_detalles SET deleted = 1 WHERE id_ventas = $id_ventas");
    mysqli_query($cnn, "UPDATE ventas_pagos SET deleted = 1 WHERE id_ventas = $id_ventas");

    // Soft-delete la venta
    mysqli_query($cnn, "UPDATE ventas SET deleted = 1 WHERE id_ventas = $id_ventas");

    registrar_accion($cnn, "Ventas", "Eliminó la venta #$id_ventas");

    echo "
    <script>
        window.location='index.php?seccion=ventas&accion=listar';
    </script>";
    exit;
}

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_estado = isset($_GET['f_estado']) ? trim($_GET['f_estado']) : '';
$filtro_desde  = (isset($_GET['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_desde'])) ? $_GET['f_desde'] : '';
$filtro_hasta  = (isset($_GET['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_hasta'])) ? $_GET['f_hasta'] : '';

$condiciones = array("v.deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "c.cliente LIKE '%$buscarSeguro%'";
}
if ($filtro_estado != '') {
    $estadoSeguro = mysqli_real_escape_string($cnn, $filtro_estado);
    $condiciones[] = "v.estado = '$estadoSeguro'";
}
if ($filtro_desde != '') {
    $condiciones[] = "v.fecha_registro >= '$filtro_desde'";
}
if ($filtro_hasta != '') {
    $condiciones[] = "v.fecha_registro <= '$filtro_hasta 23:59:59'";
}
$where = implode(" AND ", $condiciones);
$hayFiltros = ($filtro_buscar != '' || $filtro_estado != '' || $filtro_desde != '' || $filtro_hasta != '');

/* Consulta */

$sql = "SELECT 
            v.id_ventas, 
            u.usuario, 
            c.cliente, 
            td.descripcion AS tipo_documento, 
            fp.descripcion AS formas_pago, 
            v.monto_total, 
            v.monto_pago, 
            v.monto_cambio, 
            v.estado, 
            v.fecha_registro,
            (SELECT COUNT(*) FROM ventas_detalles vd WHERE vd.id_ventas = v.id_ventas AND vd.deleted = 0) AS cantidad_items,
            (SELECT GROUP_CONCAT(CONCAT(fp2.descripcion, ' \$', FORMAT(vp.monto, 2)) SEPARATOR ' / ')
             FROM ventas_pagos vp
             INNER JOIN formas_pagos fp2 ON vp.id_formas_pago = fp2.id_formas_pago
             WHERE vp.id_ventas = v.id_ventas AND vp.deleted = 0) AS medios_pago
        FROM ventas v 
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario 
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente 
        INNER JOIN tipos_documentos td ON v.tipo_documento = td.id_tipo_documento 
        INNER JOIN formas_pagos fp ON v.id_formas_pago = fp.id_formas_pago 
        WHERE $where
        ORDER BY id_ventas DESC";

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0" style="color: #29252A;">Ventas</h2>
            <p class="small" style="color: #4a454a;">Historial de ventas realizadas</p>
        </div>
        <a href="index.php?seccion=ventas&accion=modificar" class="btn rounded-pill px-4" style="background: #F48FB1; color: #fff; border: none;">
            + Nueva Venta
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FCE4EC;">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="ventas">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold" style="color: #29252A;">Buscar cliente</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Nombre del cliente..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold" style="color: #29252A;">Estado</label>
                        <select name="f_estado" class="form-select" style="border-color: #F8BBD0;">
                            <option value="">Todos</option>
                            <option value="PAGADO" <?= ($filtro_estado === 'PAGADO') ? 'selected' : '' ?>>Pagado</option>
                            <option value="PENDIENTE" <?= ($filtro_estado === 'PENDIENTE') ? 'selected' : '' ?>>Pendiente</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold" style="color: #29252A;">Desde</label>
                        <input type="date" name="f_desde" class="form-control" value="<?= $filtro_desde ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold" style="color: #29252A;">Hasta</label>
                        <input type="date" name="f_hasta" class="form-control" value="<?= $filtro_hasta ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background: #F48FB1; color: #fff; border: none;"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=ventas&accion=listar" class="btn rounded-pill px-4" style="color: #29252A; border: 1px solid #C5B4E3; background: transparent;">Limpiar</a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="background: #FCE4EC;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #FFF9F5;">
                        <tr>
                            <th class="ps-4" style="color: #29252A;">ID</th>
                            <th style="color: #29252A;">Usuario</th>
                            <th style="color: #29252A;">Cliente</th>
                            <th style="color: #29252A;">Doc.</th>
                            <th class="text-center" style="color: #29252A;">Cant.</th>
                            <th style="color: #29252A;">Medios de Pago</th>
                            <th style="color: #29252A;">Total</th>
                            <th class="text-end" style="color: #29252A;">Cambio</th>
                            <th style="color: #29252A;">Estado</th>
                            <th style="color: #29252A;">Fecha</th>
                            <th class="text-end pe-4" style="color: #29252A;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color: #4a454a;"><?= $fila['id_ventas'] ?></td>
                            <td style="color: #29252A;"><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['cliente']) ?></td>
                            <td><span class="badge" style="background: #FFF9F5; color: #29252A; border: 1px solid #F8BBD0;"><?= htmlspecialchars($fila['tipo_documento']) ?></span></td>
                            <td class="text-center"><span class="badge rounded-pill" style="background: #F3E5F5; color: #9575CD;"><?= $fila['cantidad_items'] ?></span></td>
                            <td><small style="color: #4a454a;"><?= htmlspecialchars($fila['medios_pago'] ?? '-') ?></small></td>
                            <td class="fw-bold" style="color: #E57373;">$<?= number_format($fila['monto_total'], 2, ',', '.') ?></td>
                            <td class="text-end" style="color: #4a454a;">$<?= number_format($fila['monto_cambio'], 2, ',', '.') ?></td>
                            <td>
                                <?php if(strtoupper($fila['estado']) == 'PAGADO' || strtoupper($fila['estado']) == 'COMPLETADO'){ ?>
                                    <span class="badge rounded-pill" style="background: #E8F5E9; color: #4CAF50;"><?= htmlspecialchars($fila['estado']) ?></span>
                                <?php }else{ ?>
                                    <span class="badge rounded-pill" style="background: #FFF8E1; color: #E6C24D;"><?= htmlspecialchars($fila['estado']) ?></span>
                                <?php } ?>
                            </td>
                            <td style="color: #29252A;"><?= date('d/m/Y', strtotime($fila['fecha_registro'])) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=ventas&accion=modificar&id=<?= $fila['id_ventas'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="background: transparent; color: #E6C24D; border: 1px solid #E6C24D;">
                                   <i class="fas fa-pen"></i>
                                </a>
                                <a href="index.php?seccion=ventas_detalles&accion=listar&f_venta=<?= $fila['id_ventas'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Ver detalles"
                                   style="background: transparent; color: #4A90D9; border: 1px solid #4A90D9;">
                                   <i class="fas fa-eye"></i>
                                </a>
                                <a href="index.php?seccion=ventas&accion=listar&eliminar=<?= $fila['id_ventas'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="background: transparent; color: #E57373; border: 1px solid #E57373;"
                                   onclick="return confirm('¿Está seguro de eliminar esta venta?');">
                                   <i class="fas fa-trash"></i>
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