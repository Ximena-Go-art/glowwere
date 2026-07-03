<?php

include "conexion.php";

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

$resultado = mysqli_query($cnn, $sql);

?>

<div class="container mt-4">

    <h2>Ventas</h2>

    <a
        href="index.php?seccion=ventas&accion=modificar"
        class="btn btn-success mb-3">
        Nueva Venta
    </a>

    <table class="table table-striped">

        <thead>

            <tr>
                <th>ID</th>
                <th>Usuario</th>
                <th>Cliente</th>
                <th>Documento</th>
                <th>Forma Pago</th>
                <th>Total</th>
                <th>Pago</th>
                <th>Cambio</th>
                <th>Estado</th>
                <th>Fecha</th>
                <th>Acciones</th>
            </tr>

        </thead>

        <tbody>

        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

            <tr>

                <td><?= $fila['id_ventas'] ?></td>
                <td><?= htmlspecialchars($fila['usuario']) ?></td>
                <td><?= htmlspecialchars($fila['cliente']) ?></td>
                <td><?= htmlspecialchars($fila['tipo_documento']) ?></td>
                <td><?= htmlspecialchars($fila['forma_pago']) ?></td>
                <td>$<?= number_format($fila['monto_total'],2) ?></td>
                <td>$<?= number_format($fila['monto_pago'],2) ?></td>
                <td>$<?= number_format($fila['monto_cambio'],2) ?></td>
                <td><?= htmlspecialchars($fila['estado']) ?></td>
                <td><?= $fila['fecha_registro'] ?></td>

                <td>

                    <a
                        href="index.php?seccion=ventas&accion=modificar&id=<?= $fila['id_ventas'] ?>"
                        class="btn btn-warning btn-sm">
                        Modificar
                    </a>

                    <a
                        href="index.php?seccion=ventas&accion=listar&eliminar=<?= $fila['id_ventas'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('¿Desea eliminar esta venta?')">
                        Eliminar
                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>