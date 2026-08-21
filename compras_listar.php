<?php

include "conexion.php";
include_once "paginador.php";

$cnn = conection();

/* Eliminación lógica */

if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {

    $id_compra = intval($_GET['eliminar']);

    $sqlEliminar = "
    UPDATE compras
    SET deleted = 1
    WHERE id_compra = $id_compra
    ";

    mysqli_query($cnn, $sqlEliminar);

    registrar_accion($cnn, "Compras", "Eliminó la compra #$id_compra");

    echo "
    <script>
        window.location='index.php?seccion=compras&accion=listar';
    </script>";
    exit;
}

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

WHERE c.deleted = 0
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
        <a href="index.php?seccion=compras&accion=modificar" class="btn btn-danger rounded-pill px-4">
            + Nueva Compra
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
                                   class="btn btn-outline-warning btn-sm rounded-pill px-3 me-1">Editar</a>
                                <a href="index.php?seccion=compras&accion=listar&eliminar=<?= $fila['id_compra'] ?>" 
                                   class="btn btn-outline-danger btn-sm rounded-pill px-3"
                                   onclick="return confirm('¿Está seguro de eliminar esta compra?');">Eliminar</a>
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