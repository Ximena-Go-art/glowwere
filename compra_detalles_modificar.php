<?php

include "conexion.php";

$cnn = conection();

/* Combos */

$compras = mysqli_query(
    $cnn,
    "SELECT id_compra, numero_documento
     FROM compras
     WHERE deleted = 0"
);

$productos = mysqli_query(
    $cnn,
    "SELECT id_producto, nombre
     FROM productos
     WHERE deleted = 0"
);

/* Datos */

$datos = [
    'id_compra_detalle' => '',
    'id_compra' => '',
    'id_producto' => '',
    'cantidad' => '',
    'precio_unitario' => '',
    'precio_total' => ''
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_compra_detalle = intval($_POST['id_compra_detalle']);

    $id_compra = intval($_POST['id_compra']);

    $id_producto = intval($_POST['id_producto']);

    $cantidad = intval($_POST['cantidad']);

    $precio_unitario = floatval($_POST['precio_unitario']);

    $precio_total = floatval($_POST['precio_total']);

    if ($id_compra_detalle == 0) {

        $sql = "
        INSERT INTO compra_detalles
        (
            id_compra,
            id_producto,
            cantidad,
            precio_unitario,
            precio_total
        )
        VALUES
        (
            $id_compra,
            $id_producto,
            $cantidad,
            $precio_unitario,
            $precio_total
        )";

    } else {

        $sql = "
        UPDATE compra_detalles
        SET
            id_compra = $id_compra,
            id_producto = $id_producto,
            cantidad = $cantidad,
            precio_unitario = $precio_unitario,
            precio_total = $precio_total
        WHERE id_compra_detalle = $id_compra_detalle";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        $nuevo_id = mysqli_insert_id($cnn);

        if ($id_compra_detalle == 0) {
            registrar_accion($cnn, "Detalle de Compras", "Registró el detalle #$nuevo_id de la compra #$id_compra");
        } else {
            registrar_accion($cnn, "Detalle de Compras", "Modificó el detalle #$id_compra_detalle de la compra #$id_compra");
        }

        echo "
        <script>
            window.location='index.php?seccion=compra_detalles&accion=listar';
        </script>";

        exit;
    }

    echo mysqli_error($cnn);
}

/* Cargar */

if (isset($_GET['id'])) {

    $id = intval($_GET['id']);

    $sql = "
        SELECT *
        FROM compra_detalles
        WHERE id_compra_detalle = $id
    ";

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado && mysqli_num_rows($resultado) > 0) {

        $datos = mysqli_fetch_assoc($resultado);

    }

}

?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= ($datos['id_compra_detalle'] != '') ? 'Editar Detalle' : 'Nuevo Detalle de Compra' ?></h2>
            <p class="text-muted small">Registra los productos incluidos en una compra</p>
        </div>
        <a href="index.php?seccion=compra_detalles&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_compra_detalle" value="<?= $datos['id_compra_detalle'] ?>">

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Compra</label>
                            <select name="id_compra" class="form-select form-select-lg rounded-3" required>
                                <option value="">Seleccione</option>
                                <?php mysqli_data_seek($compras, 0); while($c = mysqli_fetch_assoc($compras)){ ?>
                                <option value="<?= $c['id_compra'] ?>" <?= ($datos['id_compra']==$c['id_compra']) ? 'selected' : '' ?>>
                                    #<?= $c['id_compra'] ?> - <?= htmlspecialchars($c['numero_documento']) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Producto</label>
                            <select name="id_producto" class="form-select form-select-lg rounded-3" required>
                                <option value="">Seleccione</option>
                                <?php mysqli_data_seek($productos, 0); while($p = mysqli_fetch_assoc($productos)){ ?>
                                <option value="<?= $p['id_producto'] ?>" <?= ($datos['id_producto']==$p['id_producto']) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nombre']) ?>
                                </option>
                                <?php } ?>
                            </select>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Cantidad</label>
                                <input type="number" name="cantidad" class="form-control form-control-lg rounded-3" 
                                       value="<?= $datos['cantidad'] ?>" required placeholder="0">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Precio Unit.</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="precio_unitario" id="precio_unit" class="form-control form-control-lg rounded-3" 
                                           value="<?= $datos['precio_unitario'] ?>" required placeholder="0.00" style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" id="total_calc" class="form-control form-control-lg rounded-3 bg-light" 
                                           value="<?= $datos['precio_total'] ?>" readonly style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                            </div>
                        </div>

                        <input type="hidden" name="precio_total" id="precio_total" value="<?= $datos['precio_total'] ?>">

                        <div class="d-grid mt-3">
                            <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg rounded-pill shadow-sm">
                                <i class="fas fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
const cant = document.querySelector('input[name="cantidad"]');
const pUnit = document.getElementById('precio_unit');
const tCalc = document.getElementById('total_calc');
const tHidden = document.getElementById('precio_total');
function calcTotal() {
    const val = (parseFloat(pUnit.value || 0) * parseInt(cant.value || 0)).toFixed(2);
    tCalc.value = val;
    tHidden.value = val;
}
cant.addEventListener('input', calcTotal);
pUnit.addEventListener('input', calcTotal);
</script>