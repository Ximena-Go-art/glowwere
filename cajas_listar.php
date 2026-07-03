<?php
include_once "conexion.php";
$cnn = conection();

$sql = "SELECT c.*, fp.descripcion AS formas_pago
        FROM cajas c
        INNER JOIN formas_pagos fp ON c.id_formas_pago = fp.id_formas_pago
        WHERE c.deleted = 0
        ORDER BY c.fecha_hora DESC, c.id_caja DESC";

$resultado = mysqli_query($cnn, $sql);
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold"><i class="fas fa-cash-register text-danger"></i> Registro de Caja</h3>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Fecha / Hora</th>
                            <th>Detalle</th>
                            <th>Forma de Pago</th>
                            <th class="text-end pe-4">Importe</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4">
                                <small class="text-muted d-block"><?= date('d/m/Y', strtotime($fila['fecha_hora'])) ?></small>
                                <span class="fw-bold"><?= date('H:i', strtotime($fila['fecha_hora'])) ?></span>
                            </td>
                            <td><?= htmlspecialchars($fila['detalle_movimiento']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['formas_pago']) ?></span></td>
                            <td class="text-end pe-4 fw-bold <?php echo ($fila['importe'] >= 0) ? 'text-success' : 'text-danger'; ?>">
                                $<?= number_format($fila['importe'], 2, ',', '.') ?>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>