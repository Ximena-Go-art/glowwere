<?php

include "conexion.php";

$cnn = conection();

/* Combos */

$usuarios =
mysqli_query(
    $cnn,
    "SELECT id_usuario, usuario
     FROM usuarios
     WHERE deleted = 0"
);

$formas_pago =
mysqli_query(
    $cnn,
    "SELECT id_formas_pago, descripcion
     FROM formas_pagos
     WHERE deleted = 0"
);

$proveedores =
mysqli_query(
    $cnn,
    "SELECT id_proveedor, proveedor
     FROM proveedores
     WHERE deleted = 0"
);

/* Datos */

$datos = [
    'id_caja' => '',
    'id_usuario' => '',
    'detalle_movimiento' => '',
    'importe' => '',
    'id_formas_pago' => '',
    'id_proveedor' => '',
    'fecha_hora' => date('Y-m-d\TH:i')
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_caja = intval($_POST['id_caja']);

    $id_usuario = intval($_POST['id_usuario']);
    $id_formas_pago = intval($_POST['id_formas_pago']);

    $id_proveedor =
    intval($_POST['id_proveedor']);

    $id_proveedor_sql =
    ($id_proveedor > 0) ? "$id_proveedor" : "NULL";

    $detalle_log = trim($_POST['detalle_movimiento']);

    $detalle_movimiento =
    mysqli_real_escape_string(
        $cnn,
        trim($_POST['detalle_movimiento'])
    );

    $importe =
    floatval($_POST['importe']);

    $fecha_hora =
    mysqli_real_escape_string(
        $cnn,
        str_replace('T', ' ', $_POST['fecha_hora'])
    );

    if ($id_caja == 0) {

        $sql = "
        INSERT INTO cajas
        (
            id_usuario,
            detalle_movimiento,
            importe,
            id_formas_pago,
            id_proveedor,
            fecha_hora,
            deleted
        )
        VALUES
        (
            $id_usuario,
            '$detalle_movimiento',
            $importe,
            $id_formas_pago,
            $id_proveedor_sql,
            '$fecha_hora',
            0
        )";

    } else {

        $sql = "
        UPDATE cajas
        SET
            id_usuario = $id_usuario,
            detalle_movimiento = '$detalle_movimiento',
            importe = $importe,
            id_formas_pago = $id_formas_pago,
            id_proveedor = $id_proveedor_sql,
            fecha_hora = '$fecha_hora'
        WHERE id_caja = $id_caja";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        if ($id_caja == 0) {
            registrar_accion($cnn, "Caja", "Registró el ajuste de caja '$detalle_log' ($importe)");
        } else {
            registrar_accion($cnn, "Caja", "Modificó el ajuste de caja #$id_caja");
        }

        echo "
        <script>
        window.location='index.php?seccion=cajas&accion=listar';
        </script>";
        exit;
    }

    echo mysqli_error($cnn);
}

/* Cargar */

if (isset($_GET['id'])) {

    $id_caja = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM cajas
    WHERE id_caja = $id_caja
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
            <h2 class="fw-bold m-0"><?= ($datos['id_caja'] != '') ? 'Editar Ajuste de Caja' : 'Nuevo Ajuste de Caja' ?></h2>
            <p class="text-muted small">Las ventas y compras ya se registran solas en el libro; use este formulario para ingresos/egresos manuales</p>
        </div>
        <a href="index.php?seccion=cajas&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_caja" value="<?= $datos['id_caja'] ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Usuario que mueve la caja</label>
                                <select name="id_usuario" class="form-select form-select-lg rounded-3" required>
                                    <option value="">Seleccione</option>
                                    <?php while($u = mysqli_fetch_assoc($usuarios)){ ?>
                                    <option value="<?= $u['id_usuario'] ?>" <?= ($datos['id_usuario']==$u['id_usuario']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['usuario']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Forma de Pago</label>
                                <select name="id_formas_pago" class="form-select form-select-lg rounded-3" required>
                                    <option value="">Seleccione</option>
                                    <?php mysqli_data_seek($formas_pago, 0); while($fp = mysqli_fetch_assoc($formas_pago)){ ?>
                                    <option value="<?= $fp['id_formas_pago'] ?>" <?= ($datos['id_formas_pago']==$fp['id_formas_pago']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($fp['descripcion']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Proveedor (opcional)</label>
                                <select name="id_proveedor" class="form-select form-select-lg rounded-3">
                                    <option value="">— Sin proveedor —</option>
                                    <?php while($p = mysqli_fetch_assoc($proveedores)){ ?>
                                    <option value="<?= $p['id_proveedor'] ?>" <?= ($datos['id_proveedor']==$p['id_proveedor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['proveedor']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Detalle del Movimiento</label>
                                <input type="text" name="detalle_movimiento" class="form-control form-control-lg rounded-3"
                                       value="<?= htmlspecialchars($datos['detalle_movimiento']) ?>" required placeholder="Ej: Apertura de caja / Gastos varios">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Importe (+ ingreso / − egreso)</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="importe" class="form-control form-control-lg rounded-3"
                                           value="<?= $datos['importe'] ?>" required placeholder="0.00" style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                                <small class="text-muted">Positivo suma al saldo, negativo resta</small>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Fecha y Hora</label>
                                <input type="datetime-local" name="fecha_hora" class="form-control form-control-lg rounded-3"
                                       value="<?= substr(str_replace(' ', 'T', $datos['fecha_hora']), 0, 16) ?>">
                            </div>
                        </div>

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
