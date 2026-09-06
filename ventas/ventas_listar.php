<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_ventas = intval($_GET['eliminar']);

    $sqlEliminar = "
    UPDATE ventas
    SET deleted = 1
    WHERE id_ventas = $id_ventas
    ";

    mysqli_query($cnn, $sqlEliminar);

    registrar_accion($cnn, "Ventas", "Eliminó la venta #$id_ventas");

    echo "
    <script>
        window.location='index.php?seccion=ventas&accion=listar';
    </script>";
    exit;
}

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
            v.fecha_registro 
        FROM ventas v 
        INNER JOIN usuarios u ON v.id_usuario = u.id_usuario 
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente 
        INNER JOIN tipos_documentos td ON v.tipo_documento = td.id_tipo_documento 
        INNER JOIN formas_pagos fp ON v.id_formas_pago = fp.id_formas_pago 
        WHERE v.deleted = 0 
        ORDER BY v.fecha_registro DESC";

$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0">Ventas</h2>
            <p class="text-muted small">Historial de ventas realizadas</p>
        </div>
        <a href="index.php?seccion=ventas&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nueva Venta
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">ID</th>
                            <th>Usuario</th>
                            <th>Cliente</th>
                            <th>Doc.</th>
                            <th>Pago</th>
                            <th>Total</th>
                            <th>Estado</th>
                            <th>Fecha</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold text-muted"><?= $fila['id_ventas'] ?></td>
                            <td><?= htmlspecialchars($fila['usuario']) ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($fila['cliente']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= htmlspecialchars($fila['tipo_documento']) ?></span></td>
                            <td><?= htmlspecialchars($fila['formas_pago']) ?></td>
                            <td class="fw-bold text-danger">$<?= number_format($fila['monto_total'], 2, ',', '.') ?></td>
                            <td>
                                <?php if(strtoupper($fila['estado']) == 'PAGADO' || strtoupper($fila['estado']) == 'COMPLETADO'){ ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill"><?= htmlspecialchars($fila['estado']) ?></span>
                                <?php }else{ ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill"><?= htmlspecialchars($fila['estado']) ?></span>
                                <?php } ?>
                            </td>
                            <td><?= date('d/m/Y', strtotime($fila['fecha_registro'])) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=ventas&accion=modificar&id=<?= $fila['id_ventas'] ?>" 
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="index.php?seccion=ventas&accion=listar&eliminar=<?= $fila['id_ventas'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar esta venta?');">Eliminar</a>
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