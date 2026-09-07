<?php

include_once "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$filtro_origen = isset($_GET['f_origen']) ? trim($_GET['f_origen']) : '';
$filtro_desde  = (isset($_GET['f_desde']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_desde'])) ? $_GET['f_desde'] : '';
$filtro_hasta  = (isset($_GET['f_hasta']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['f_hasta'])) ? $_GET['f_hasta'] : '';

$condiciones = array("1=1");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(detalle LIKE '%$buscarSeguro%' OR usuario LIKE '%$buscarSeguro%' OR proveedor LIKE '%$buscarSeguro%')";
}
if ($filtro_origen != '') {
    $origenSeguro = mysqli_real_escape_string($cnn, $filtro_origen);
    $condiciones[] = "origen = '$origenSeguro'";
}
if ($filtro_desde != '') {
    $condiciones[] = "fecha_movimiento >= '$filtro_desde 00:00:00'";
}
if ($filtro_hasta != '') {
    $condiciones[] = "fecha_movimiento <= '$filtro_hasta 23:59:59'";
}
$whereFiltros = implode(" AND ", $condiciones);
$hayFiltros = ($filtro_buscar != '' || $filtro_origen != '' || $filtro_desde != '' || $filtro_hasta != '');

/* Ordenamiento */
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'fecha_movimiento';
$direccion = (isset($_GET['dir']) && $_GET['dir'] === 'DESC') ? 'DESC' : 'ASC';

/* Consulta: libro de caja unificado */

$sqlUnion = "SELECT * FROM (

    /* Ventas: suman */
    SELECT
        v.fecha_registro AS fecha_movimiento,
        'Venta' AS origen,
        CONCAT('Venta #', v.id_ventas, ' - ', cl.cliente) AS detalle,
        u.usuario,
        NULL AS proveedor,
        IFNULL(fp.descripcion, '-') AS forma_pago,
        v.monto_total AS importe
    FROM ventas v
    INNER JOIN usuarios u ON v.id_usuario = u.id_usuario
    INNER JOIN clientes cl ON v.id_cliente = cl.id_cliente
    LEFT JOIN formas_pagos fp ON v.id_formas_pago = fp.id_formas_pago
    WHERE v.deleted = 0

    UNION ALL

    /* Compras: restan */
    SELECT
        c.fecha_registro AS fecha_movimiento,
        'Compra' AS origen,
        CONCAT('Compra #', c.id_compra) AS detalle,
        u.usuario,
        p.proveedor,
        '-' AS forma_pago,
        -c.monto_total AS importe
    FROM compras c
    INNER JOIN usuarios u ON c.id_usuario = u.id_usuario
    INNER JOIN proveedores p ON c.id_proveedor = p.id_proveedor
    WHERE c.deleted = 0

    UNION ALL

    /* Ajustes manuales de caja */
    SELECT
        cj.fecha_hora AS fecha_movimiento,
        'Ajuste' AS origen,
        cj.detalle_movimiento AS detalle,
        u.usuario,
        IFNULL(p.proveedor, '') AS proveedor,
        IFNULL(fp.descripcion, '-') AS forma_pago,
        cj.importe AS importe
    FROM cajas cj
    INNER JOIN usuarios u ON cj.id_usuario = u.id_usuario
    LEFT JOIN formas_pagos fp ON cj.id_formas_pago = fp.id_formas_pago
    LEFT JOIN proveedores p ON cj.id_proveedor = p.id_proveedor
    WHERE cj.deleted = 0

) AS movimientos WHERE $whereFiltros";

$sqlSaldo = "SELECT IFNULL(SUM(importe), 0) AS saldo FROM ($sqlUnion) AS movimientos_saldo";
$resSaldo = mysqli_query($cnn, $sqlSaldo);
$filaSaldo = mysqli_fetch_assoc($resSaldo);
$total_caja = floatval($filaSaldo['saldo']);

$pag = paginar_consulta($cnn, "$sqlUnion ORDER BY $orden $direccion", 10);
$resultado = $pag['data'];
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0" style="color: #29252A;"><i class="fas fa-cash-register me-2" style="color: #F48FB1;"></i>Libro de Caja</h2>
            <p class="small" style="color: #4a454a;">Las ventas suman y las compras restan automáticamente</p>
        </div>
        <a href="index.php?seccion=cajas&accion=modificar" class="btn rounded-pill px-4" style="background: #F48FB1; color: #fff; border: none;">
            + Nuevo Ajuste de Caja
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FCE4EC;">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="cajas">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-3">
                        <label class="form-label small fw-bold" style="color: #29252A;">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Detalle, usuario, proveedor..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label small fw-bold" style="color: #29252A;">Origen</label>
                        <select name="f_origen" class="form-select" style="border-color: #F8BBD0;">
                            <option value="">Todos</option>
                            <option value="Venta" <?= ($filtro_origen === 'Venta') ? 'selected' : '' ?>>Venta</option>
                            <option value="Compra" <?= ($filtro_origen === 'Compra') ? 'selected' : '' ?>>Compra</option>
                            <option value="Ajuste" <?= ($filtro_origen === 'Ajuste') ? 'selected' : '' ?>>Ajuste</option>
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
                            <a href="index.php?seccion=cajas&accion=listar" class="btn rounded-pill px-4" style="color: #29252A; border: 1px solid #C5B4E3; background: transparent;">Limpiar</a>
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
                            <th class="ps-4" style="color: #29252A;">
                                <?php $nuevaDir = ($direccion === 'ASC') ? 'DESC' : 'ASC'; $sp = $_GET; $sp['orden'] = 'fecha_movimiento'; $sp['dir'] = $nuevaDir; unset($sp['pagina']); ?>
                                <a href="index.php?<?= http_build_query($sp) ?>" style="text-decoration:none; color:inherit;">Fecha / Hora <?= $direccion === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>' ?></a>
                            </th>
                            <th style="color: #29252A;">Origen</th>
                            <th style="color: #29252A;">Detalle</th>
                            <th style="color: #29252A;">Usuario</th>
                            <th style="color: #29252A;">Proveedor</th>
                            <th style="color: #29252A;">Forma de Pago</th>
                            <th class="text-end pe-4" style="color: #29252A;">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <?php $total_caja += floatval($fila['importe']); ?>
                        <tr>
                            <td class="ps-4">
                                <small class="d-block" style="color: #4a454a;"><?= date('d/m/Y', strtotime($fila['fecha_movimiento'])) ?></small>
                                <span class="fw-bold" style="color: #29252A;"><?= date('H:i', strtotime($fila['fecha_movimiento'])) ?></span>
                            </td>
                            <td>
                                <?php if ($fila['origen'] == 'Venta') { ?>
                                    <span class="badge" style="background: #E8F5E9; color: #4CAF50;">Venta</span>
                                <?php } elseif ($fila['origen'] == 'Compra') { ?>
                                    <span class="badge" style="background: #FFEBEE; color: #E57373;">Compra</span>
                                <?php } else { ?>
                                    <span class="badge" style="background: #E3F2FD; color: #5BAFD4;">Ajuste</span>
                                <?php } ?>
                            </td>
                            <td style="color: #29252A;"><?= htmlspecialchars($fila['detalle']) ?></td>
                            <td style="color: #29252A;"><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td><?= ($fila['proveedor'] != '') ? '<span style="color: #29252A;">' . htmlspecialchars($fila['proveedor']) . '</span>' : '<span style="color: #4a454a;">—</span>' ?></td>
                            <td><span class="badge" style="background: #FFF9F5; color: #29252A; border: 1px solid #F8BBD0;"><?= htmlspecialchars($fila['forma_pago']) ?></span></td>
                            <td class="text-end pe-4 fw-bold" style="color: <?= ($fila['importe'] >= 0) ? '#4CAF50' : '#E57373' ?>;">
                                <?= ($fila['importe'] >= 0) ? '+' : '' ?>$<?= number_format($fila['importe'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot style="background: #FFF9F5;">
                        <tr>
                            <td colspan="6" class="ps-4 fw-bold text-uppercase small" style="color: #29252A;">Saldo en Caja</td>
                            <td class="text-end pe-4 fw-bold" style="color: <?= ($total_caja >= 0) ? '#4CAF50' : '#E57373' ?>;">
                                $<?= number_format($total_caja, 2, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>
