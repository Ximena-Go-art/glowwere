<?php
include_once "conexion.php";
$cnn = conection();

// --- 1. LÓGICA DE GUARDADO ---
if (isset($_POST['btnGuardar'])) {
    $id = intval($_POST['id_ventas_detalles']);
    $id_ventas = intval($_POST['id_ventas']);
    $id_producto = intval($_POST['id_producto']);
    $monto_venta = floatval($_POST['monto_venta']);
    $cantidad = intval($_POST['cantidad_productos']);
    $total = $monto_venta * $cantidad;

    if ($id == 0) {
        $sql = "INSERT INTO ventas_detalles (id_ventas, id_producto, monto_venta, cantidad_productos, monto_total) 
                VALUES ($id_ventas, $id_producto, $monto_venta, $cantidad, $total)";
    } else {
        $sql = "UPDATE ventas_detalles SET id_ventas=$id_ventas, id_producto=$id_producto, monto_venta=$monto_venta, 
                cantidad_productos=$cantidad, monto_total=$total WHERE id_ventas_detalles=$id";
    }

    if (mysqli_query($cnn, $sql)) {
        $nuevo_id = mysqli_insert_id($cnn);
        if ($id == 0) {
            registrar_accion($cnn, "Detalle de Ventas", "Registró el detalle #$nuevo_id de la venta #$id_ventas");
        } else {
            registrar_accion($cnn, "Detalle de Ventas", "Modificó el detalle #$id de la venta #$id_ventas");
        }
        echo "<script>window.location='index.php?seccion=ventas_detalles&accion=listar';</script>";
        exit;
    }
}

// --- 2. CONSULTAS PARA COMBOS Y CARGA ---
$ventas = mysqli_query($cnn, "SELECT id_ventas FROM ventas WHERE deleted = 0");
$productos = mysqli_query($cnn, "SELECT id_producto, nombre FROM productos WHERE deleted = 0");

$datos = ['id_ventas_detalles' => '', 'id_ventas' => '', 'id_producto' => '', 'monto_venta' => '', 'cantidad_productos' => '', 'monto_total' => ''];

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $res = mysqli_query($cnn, "SELECT * FROM ventas_detalles WHERE id_ventas_detalles = $id");
    if ($res && mysqli_num_rows($res) > 0) $datos = mysqli_fetch_assoc($res);
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-danger"><i class="fas fa-list-ol"></i> <?= empty($datos['id_ventas_detalles']) ? 'Nuevo Detalle' : 'Editar Detalle' ?></h3>
        <a href="index.php?seccion=ventas_detalles&accion=listar" class="btn btn-outline-secondary rounded-pill">Volver</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="id_ventas_detalles" value="<?= $datos['id_ventas_detalles'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Venta ID</label>
                        <select name="id_ventas" class="form-select"><?php mysqli_data_seek($ventas, 0); while($v = mysqli_fetch_assoc($ventas)){ ?>
                            <option value="<?= $v['id_ventas'] ?>" <?= ($datos['id_ventas']==$v['id_ventas']) ? 'selected' : '' ?>>Venta #<?= $v['id_ventas'] ?></option>
                        <?php } ?></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Producto</label>
                        <select name="id_producto" class="form-select"><?php mysqli_data_seek($productos, 0); while($p = mysqli_fetch_assoc($productos)){ ?>
                            <option value="<?= $p['id_producto'] ?>" <?= ($datos['id_producto']==$p['id_producto']) ? 'selected' : '' ?>><?= htmlspecialchars($p['nombre']) ?></option>
                        <?php } ?></select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Precio Unitario</label>
                        <input type="number" step="0.01" name="monto_venta" id="precio" class="form-control" value="<?= $datos['monto_venta'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Cantidad</label>
                        <input type="number" name="cantidad_productos" id="cantidad" class="form-control" value="<?= $datos['cantidad_productos'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted">Total (Auto)</label>
                        <input type="number" step="0.01" id="total" class="form-control bg-light" value="<?= $datos['monto_total'] ?>" readonly>
                    </div>
                </div>

                <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg px-5 rounded-pill mt-3">
                    <i class="fas fa-save me-2"></i> Guardar Detalle
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const precio = document.getElementById('precio');
    const cantidad = document.getElementById('cantidad');
    const total = document.getElementById('total');
    function calc() { total.value = (parseFloat(precio.value || 0) * parseInt(cantidad.value || 0)).toFixed(2); }
    precio.addEventListener('input', calc);
    cantidad.addEventListener('input', calc);
</script>