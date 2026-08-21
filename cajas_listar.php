<?php

include_once "conexion.php";

$cnn = conection();

/* Consulta: libro de caja unificado */

$sql = "

SELECT * FROM (

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

) AS movimientos

ORDER BY fecha_movimiento DESC, importe DESC
";

$resultado = mysqli_query($cnn, $sql);

$total_caja = 0;
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><i class="fas fa-cash-register text-danger me-2"></i>Libro de Caja</h2>
            <p class="text-muted small">Las ventas suman y las compras restan automáticamente</p>
        </div>
        <a href="index.php?seccion=cajas&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nuevo Ajuste de Caja
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha / Hora</th>
                            <th>Origen</th>
                            <th>Detalle</th>
                            <th>Usuario</th>
                            <th>Proveedor</th>
                            <th>Forma de Pago</th>
                            <th class="text-end pe-4">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <?php $total_caja += floatval($fila['importe']); ?>
                        <tr>
                            <td class="ps-4">
                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($fila['fecha_movimiento'])) ?></small>
                                <span class="fw-bold"><?= date('H:i', strtotime($fila['fecha_movimiento'])) ?></span>
                            </td>
                            <td>
                                <?php if ($fila['origen'] == 'Venta') { ?>
                                    <span class="badge bg-success-subtle text-success">Venta</span>
                                <?php } elseif ($fila['origen'] == 'Compra') { ?>
                                    <span class="badge bg-danger-subtle text-danger">Compra</span>
                                <?php } else { ?>
                                    <span class="badge bg-secondary-subtle text-secondary">Ajuste</span>
                                <?php } ?>
                            </td>
                            <td><?= htmlspecialchars($fila['detalle']) ?></td>
                            <td><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td><?= ($fila['proveedor'] != '') ? htmlspecialchars($fila['proveedor']) : '<span class="text-muted">—</span>' ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['forma_pago']) ?></span></td>
                            <td class="text-end pe-4 fw-bold <?= ($fila['importe'] >= 0) ? 'text-success' : 'text-danger' ?>">
                                <?= ($fila['importe'] >= 0) ? '+' : '' ?>$<?= number_format($fila['importe'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                    <tfoot class="table-light">
                        <tr>
                            <td colspan="6" class="ps-4 fw-bold text-uppercase small">Saldo en Caja</td>
                            <td class="text-end pe-4 fw-bold <?= ($total_caja >= 0) ? 'text-success' : 'text-danger' ?>">
                                $<?= number_format($total_caja, 2, ',', '.') ?>
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
