<?php
include_once "conexion.php";
$cnn = conection();

// --- 1. LÓGICA DE GUARDADO ---
if (isset($_POST['btnGuardar'])) {
    $id_ventas = intval($_POST['id_ventas']);
    $id_usuario = intval($_POST['id_usuario']);
    $id_cliente = intval($_POST['id_cliente']);
    $tipo_documento = intval($_POST['tipo_documento']);
    $id_forma_de_pago = intval($_POST['id_forma_de_pago']);
    $monto_total = floatval($_POST['monto_total']);
    $monto_pago = floatval($_POST['monto_pago']);
    $monto_cambio = $monto_pago - $monto_total;
    $estado = mysqli_real_escape_string($cnn, trim($_POST['estado']));
    $fecha_registro = $_POST['fecha_registro'];

    if ($id_ventas == 0) {
        $sql = "INSERT INTO ventas (id_usuario, id_cliente, tipo_documento, id_forma_de_pago, monto_total, monto_pago, monto_cambio, estado, fecha_registro) 
                VALUES ($id_usuario, $id_cliente, $tipo_documento, $id_forma_de_pago, $monto_total, $monto_pago, $monto_cambio, '$estado', '$fecha_registro')";
    } else {
        $sql = "UPDATE ventas SET id_usuario=$id_usuario, id_cliente=$id_cliente, tipo_documento=$tipo_documento, id_forma_de_pago=$id_forma_de_pago, 
                monto_total=$monto_total, monto_pago=$monto_pago, monto_cambio=$monto_cambio, estado='$estado', fecha_registro='$fecha_registro' 
                WHERE id_ventas=$id_ventas";
    }

    if (mysqli_query($cnn, $sql)) {
        echo "<script>window.location='index.php?seccion=ventas&accion=listar';</script>";
        exit;
    }
}

// --- 2. CONSULTAS PARA COMBOS Y CARGA ---
$usuarios = mysqli_query($cnn, "SELECT id_usuario, usuario FROM usuarios WHERE deleted = 0");
$clientes = mysqli_query($cnn, "SELECT id_cliente, cliente FROM clientes WHERE deleted = 0");
$tipos = mysqli_query($cnn, "SELECT id_tipo_documento, descripcion FROM tipos_documentos WHERE deleted = 0");
$formas = mysqli_query($cnn, "SELECT id_formas_pago, descripcion FROM formas_pagos WHERE deleted = 0");

$datos = ['id_ventas' => '', 'id_usuario' => '', 'id_cliente' => '', 'tipo_documento' => '', 'id_forma_de_pago' => '', 'monto_total' => '', 'monto_pago' => '', 'monto_cambio' => '', 'estado' => 'PENDIENTE', 'fecha_registro' => date('Y-m-d')];

if (isset($_GET['id'])) {
    $id_ventas = intval($_GET['id']);
    $res = mysqli_query($cnn, "SELECT * FROM ventas WHERE id_ventas = $id_ventas");
    if ($res && mysqli_num_rows($res) > 0) $datos = mysqli_fetch_assoc($res);
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-danger"><i class="fas fa-shopping-cart"></i> Gestión de Venta</h3>
        <a href="index.php?seccion=ventas&accion=listar" class="btn btn-outline-secondary rounded-pill">Volver</a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="id_ventas" value="<?= $datos['id_ventas'] ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Usuario</label>
                        <select name="id_usuario" class="form-select"><?php mysqli_data_seek($usuarios, 0); while($u = mysqli_fetch_assoc($usuarios)){ ?>
                            <option value="<?= $u['id_usuario'] ?>" <?= ($datos['id_usuario']==$u['id_usuario']) ? 'selected' : '' ?>><?= htmlspecialchars($u['usuario']) ?></option>
                        <?php } ?></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Cliente</label>
                        <select name="id_cliente" class="form-select"><?php mysqli_data_seek($clientes, 0); while($c = mysqli_fetch_assoc($clientes)){ ?>
                            <option value="<?= $c['id_cliente'] ?>" <?= ($datos['id_cliente']==$c['id_cliente']) ? 'selected' : '' ?>><?= htmlspecialchars($c['cliente']) ?></option>
                        <?php } ?></select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Tipo Documento</label>
                        <select name="tipo_documento" class="form-select"><?php mysqli_data_seek($tipos, 0); while($t = mysqli_fetch_assoc($tipos)){ ?>
                            <option value="<?= $t['id_tipo_documento'] ?>" <?= ($datos['tipo_documento']==$t['id_tipo_documento']) ? 'selected' : '' ?>><?= htmlspecialchars($t['descripcion']) ?></option>
                        <?php } ?></select>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Forma de Pago</label>
                        <select name="id_forma_de_pago" class="form-select"><?php mysqli_data_seek($formas, 0); while($f = mysqli_fetch_assoc($formas)){ ?>
                            <option value="<?= $f['id_formas_pago'] ?>" <?= ($datos['id_forma_de_pago']==$f['id_formas_pago']) ? 'selected' : '' ?>><?= htmlspecialchars($f['descripcion']) ?></option>
                        <?php } ?></select>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Monto Total</label>
                        <input type="number" step="0.01" name="monto_total" id="monto_total" class="form-control" value="<?= $datos['monto_total'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Monto Pago</label>
                        <input type="number" step="0.01" name="monto_pago" id="monto_pago" class="form-control" value="<?= $datos['monto_pago'] ?>" required>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold text-muted">Monto Cambio (Auto)</label>
                        <input type="number" step="0.01" id="monto_cambio" class="form-control bg-light" value="<?= $datos['monto_cambio'] ?>" readonly>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Estado</label>
                        <input type="text" name="estado" class="form-control" value="<?= htmlspecialchars($datos['estado']) ?>">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Fecha</label>
                        <input type="date" name="fecha_registro" class="form-control" value="<?= substr($datos['fecha_registro'],0,10) ?>">
                    </div>
                </div>

                <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg px-5 rounded-pill mt-3">
                    <i class="fas fa-save me-2"></i> Guardar Venta
                </button>
            </form>
        </div>
    </div>
</div>

<script>
    const total = document.getElementById('monto_total');
    const pago = document.getElementById('monto_pago');
    const cambio = document.getElementById('monto_cambio');
    function calcular() { cambio.value = (parseFloat(pago.value || 0) - parseFloat(total.value || 0)).toFixed(2); }
    total.addEventListener('input', calcular);
    pago.addEventListener('input', calcular);
</script>