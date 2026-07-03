<?php

include "conexion.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id = intval($_GET['eliminar']);

    $sqlEliminar = "
        UPDATE ventas_detalles
        SET deleted = 1
        WHERE id_ventas_detalles = $id
    ";

    mysqli_query($cnn, $sqlEliminar);

    echo "
    <script>
        window.location='index.php?seccion=ventas_detalles&accion=listar';
    </script>";
    exit;
}

/* Consulta */

$sql = "
SELECT

    vd.id_ventas_detalles,

    v.id_ventas,


    p.id_producto,

    vd.monto_venta,

    vd.cantidad_productos,

    vd.monto_total

FROM ventas_detalles vd

INNER JOIN ventas v
    ON vd.id_ventas = v.id_ventas


INNER JOIN productos p
    ON vd.id_producto = p.id_producto

WHERE vd.deleted = 0

ORDER BY vd.id_ventas_detalles DESC
";

$resultado = mysqli_query($cnn, $sql);

?>

<div class="container mt-4">

    <h2>Detalle de Ventas</h2>

    <table class="table table-striped table-hover">

        <thead class="table-dark">

            <tr>

                <th>ID</th>
                <th>Venta</th>
                <th>Producto</th>
                <th>Precio Venta</th>
                <th>Cantidad</th>
                <th>Total</th>
                <th>Acciones</th>

            </tr>

        </thead>

        <tbody>

        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>

            <tr>

                <td><?= $fila['id_ventas_detalles'] ?></td>

                <td>#<?= $fila['id_ventas'] ?></td>

                <td><?= htmlspecialchars($fila['cliente']) ?></td>

                <td><?= htmlspecialchars($fila['producto']) ?></td>

                <td>$<?= number_format($fila['monto_venta'],2) ?></td>

                <td><?= $fila['cantidad_productos'] ?></td>

                <td>
                    <strong>
                        $<?= number_format($fila['monto_total'],2) ?>
                    </strong>
                </td>

                <td>

                    <a
                        href="index.php?seccion=ventas_detalles&accion=modificar&id=<?= $fila['id_ventas_detalles'] ?>"
                        class="btn btn-warning btn-sm">
                        Modificar
                    </a>

                    <a
                        href="index.php?seccion=ventas_detalles&accion=listar&eliminar=<?= $fila['id_ventas_detalles'] ?>"
                        class="btn btn-danger btn-sm"
                        onclick="return confirm('¿Desea eliminar este detalle de venta?')">
                        Eliminar
                    </a>

                </td>

            </tr>

        <?php } ?>

        </tbody>

    </table>

</div>