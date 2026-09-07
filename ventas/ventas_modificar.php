<?php
include_once "conexion.php";
$cnn = conection();

// --- LÓGICA DE GUARDADO ---
if (isset($_POST['btnGuardar'])) {
    $id_ventas        = intval($_POST['id_ventas']);
    $id_usuario       = intval($_POST['id_usuario']);
    $id_cliente       = intval($_POST['id_cliente']);
    $tipo_documento   = intval($_POST['tipo_documento']);
    $monto_total      = floatval($_POST['monto_total']);
    $monto_pago       = floatval($_POST['monto_pago']);
    $monto_cambio     = $monto_pago - $monto_total;
    $estado           = ($monto_pago > 0 && $monto_pago >= $monto_total) ? 'PAGADO' : 'PENDIENTE';
    $fecha_registro   = $_POST['fecha_registro'];
    $id_formas_pago   = intval($_POST['id_formas_pago']);
    $carrito          = json_decode($_POST['carrito_json'] ?? '[]', true);
    $pagos_json       = json_decode($_POST['pagos_json'] ?? '[]', true);

    if ($id_ventas == 0) {
        $sql = "INSERT INTO ventas (id_usuario, id_cliente, tipo_documento, id_formas_pago, monto_total, monto_pago, monto_cambio, estado, fecha_registro)
                VALUES ($id_usuario, $id_cliente, $tipo_documento, $id_formas_pago, $monto_total, $monto_pago, $monto_cambio, '$estado', '$fecha_registro')";
    } else {
        $sql = "UPDATE ventas SET id_usuario=$id_usuario, id_cliente=$id_cliente, tipo_documento=$tipo_documento, id_formas_pago=$id_formas_pago,
                monto_total=$monto_total, monto_pago=$monto_pago, monto_cambio=$monto_cambio, estado='$estado', fecha_registro='$fecha_registro'
                WHERE id_ventas=$id_ventas";
    }

    if (mysqli_query($cnn, $sql)) {
        $venta_id = $id_ventas == 0 ? mysqli_insert_id($cnn) : $id_ventas;

        // Si es edición, revertir stock de detalles anteriores
        if ($id_ventas != 0) {
            $detallesActuales = mysqli_query($cnn, "SELECT id_producto, cantidad_productos FROM ventas_detalles WHERE id_ventas = $venta_id AND deleted = 0");
            if ($detallesActuales) {
                while ($d = mysqli_fetch_assoc($detallesActuales)) {
                    mysqli_query($cnn, "UPDATE productos SET stock = stock + " . intval($d['cantidad_productos']) . " WHERE id_producto = " . intval($d['id_producto']));
                }
            }
            mysqli_query($cnn, "UPDATE ventas_detalles SET deleted = 1 WHERE id_ventas = $venta_id");
            mysqli_query($cnn, "UPDATE ventas_pagos SET deleted = 1 WHERE id_ventas = $venta_id");
        }

        // Guardar ítems del carrito en ventas_detalles y descontar stock
        if (!empty($carrito)) {
            foreach ($carrito as $item) {
                $id_producto    = intval($item['id']);
                $cantidad       = intval($item['cantidad']);
                $monto_venta    = floatval($item['precio']);
                $monto_total_item = floatval($item['subtotal']);
                mysqli_query($cnn, "INSERT INTO ventas_detalles (id_ventas, id_producto, monto_venta, cantidad_productos, monto_total, deleted)
                                    VALUES ($venta_id, $id_producto, $monto_venta, $cantidad, $monto_total_item, 0)");
                mysqli_query($cnn, "UPDATE productos SET stock = stock - $cantidad WHERE id_producto = $id_producto");
            }
        }

        // Guardar métodos de pago en ventas_pagos
        if (!empty($pagos_json)) {
            foreach ($pagos_json as $pago) {
                $fp_id = intval($pago['id_formas_pago']);
                $monto = floatval($pago['monto']);
                if ($fp_id > 0 && $monto > 0) {
                    mysqli_query($cnn, "INSERT INTO ventas_pagos (id_ventas, id_formas_pago, monto, deleted)
                                        VALUES ($venta_id, $fp_id, $monto, 0)");
                }
            }
        }

        if ($id_ventas == 0) {
            registrar_accion($cnn, "Ventas", "Registró la venta #$venta_id por $" . number_format($monto_total, 2, ',', '.'));
        } else {
            registrar_accion($cnn, "Ventas", "Modificó la venta #$venta_id");
        }
        echo "<script>window.location='index.php?seccion=ventas&accion=listar';</script>";
        exit;
    } else {
        die("<div class='alert alert-danger m-3'><strong>Error:</strong> " . mysqli_error($cnn) . "</div>");
    }
}

// --- CONSULTAS ---
$usuarios  = mysqli_query($cnn, "SELECT id_usuario, usuario FROM usuarios WHERE deleted = 0");
$clientes  = mysqli_query($cnn, "SELECT id_cliente, cliente FROM clientes WHERE deleted = 0");
$productos = mysqli_query($cnn, "SELECT id_producto, nombre, precio FROM productos WHERE deleted = 0");
$tipos     = mysqli_query($cnn, "SELECT id_tipo_documento, descripcion FROM tipos_documentos WHERE deleted = 0");
$formas    = mysqli_query($cnn, "SELECT id_formas_pago, descripcion FROM formas_pagos WHERE deleted = 0");

// Buscar CONSUMIDOR FINAL
$cf_id = '';
if ($clientes) {
    mysqli_data_seek($clientes, 0);
    while ($c = mysqli_fetch_assoc($clientes)) {
        if (stripos($c['cliente'], 'CONSUMIDOR FINAL') !== false) {
            $cf_id = $c['id_cliente'];
            break;
        }
    }
}

// Tipo documento: buscar "Presupuesto"
$tipo_presupuesto_id = 1;
if ($tipos) {
    mysqli_data_seek($tipos, 0);
    while ($t = mysqli_fetch_assoc($tipos)) {
        if (stripos($t['descripcion'], 'presupuesto') !== false) {
            $tipo_presupuesto_id = $t['id_tipo_documento'];
            break;
        }
    }
}

// Métodos de pago desde la BD
$formas_arr = [];
if ($formas) {
    mysqli_data_seek($formas, 0);
    while ($f = mysqli_fetch_assoc($formas)) {
        $formas_arr[] = $f;
    }
}

// Mapa de productos para JS
$productos_map = [];
if ($productos) {
    mysqli_data_seek($productos, 0);
    while ($p = mysqli_fetch_assoc($productos)) {
        $productos_map[$p['id_producto']] = ['nombre' => $p['nombre'], 'precio' => floatval($p['precio'])];
    }
}

// Datos por defecto
$datos = [
    'id_ventas'      => '',
    'id_usuario'     => $_SESSION['id_usuario'] ?? 1,
    'id_cliente'     => $cf_id,
    'tipo_documento' => $tipo_presupuesto_id,
    'monto_total'    => 0,
    'monto_pago'     => 0,
    'fecha_registro' => date('Y-m-d')
];

if (isset($_GET['id'])) {
    $id_ventas = intval($_GET['id']);
    $res = mysqli_query($cnn, "SELECT * FROM ventas WHERE id_ventas = $id_ventas");
    if ($res && mysqli_num_rows($res) > 0) {
        $datos = mysqli_fetch_assoc($res);
    }
}
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold m-0" style="color: #29252A;"><i class="fas fa-cash-register me-2" style="color: #F48FB1;"></i>Punto de Venta</h4>
        <a href="index.php?seccion=ventas&accion=listar" class="btn rounded-pill" style="color: #29252A; border: 1px solid #C5B4E3; background: transparent;">Volver</a>
    </div>

    <form method="POST" id="formVenta">
        <input type="hidden" name="id_ventas" value="<?= $datos['id_ventas'] ?>">
        <input type="hidden" name="id_usuario" value="<?= $datos['id_usuario'] ?>">
        <input type="hidden" name="tipo_documento" value="<?= $datos['tipo_documento'] ?>">
        <input type="hidden" name="carrito_json" id="carrito_json">
        <input type="hidden" name="pagos_json" id="pagos_json">
        <input type="hidden" name="monto_total" id="monto_total" value="0">
        <input type="hidden" name="monto_pago" id="monto_pago" value="0">
        <input type="hidden" name="monto_cambio" id="monto_cambio" value="0">
        <input type="hidden" name="id_formas_pago" id="id_formas_pago" value="1">

        <!-- ENCABEZADO -->
        <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: #FCE4EC;">
            <div class="card-body py-2">
                <div class="row align-items-end g-2">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Fecha</label>
                        <input type="date" name="fecha_registro" class="form-control form-control-sm" value="<?= substr($datos['fecha_registro'], 0, 10) ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Cliente</label>
                        <select name="id_cliente" class="form-select form-select-sm" style="border-color: #F8BBD0;">
                            <?php if ($clientes) { mysqli_data_seek($clientes, 0); while ($cl = mysqli_fetch_assoc($clientes)) { ?>
                                <option value="<?= $cl['id_cliente'] ?>" <?= ($datos['id_cliente'] == $cl['id_cliente']) ? 'selected' : '' ?>><?= htmlspecialchars($cl['cliente']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Comprobante</label>
                        <input type="text" class="form-control form-control-sm" value="Presupuesto" readonly style="background: #FFF9F5; border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Letra</label>
                        <input type="text" class="form-control form-control-sm text-center fw-bold" value="C" readonly style="background: #FFF9F5; border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Lista de Precios</label>
                        <input type="text" class="form-control form-control-sm" value="Contado" readonly style="background: #FFF9F5; border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Estado</label>
                        <input type="text" class="form-control form-control-sm" id="displayEstado" value="PENDIENTE" readonly style="background: #FFF9F5; border-color: #F8BBD0;">
                    </div>
                </div>
            </div>
        </div>

        <!-- AGREGAR ARTÍCULO -->
        <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: #FCE4EC;">
            <div class="card-header fw-bold py-2" style="background: #A9DDF5; color: #29252A;">
                <i class="fas fa-plus-circle me-2"></i>Agregar Artículo
            </div>
            <div class="card-body py-2">
                <div class="row align-items-end g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Producto</label>
                        <select id="selectProducto" class="form-select form-select-sm" style="border-color: #F8BBD0;">
                            <option value="">Seleccione un producto...</option>
                            <?php foreach ($productos_map as $id => $p) { ?>
                                <option value="<?= $id ?>" data-precio="<?= $p['precio'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Cant.</label>
                        <input type="number" id="inputCantidad" class="form-control form-control-sm" value="1" min="1" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Precio c/IVA</label>
                        <input type="number" step="0.01" id="inputPrecio" class="form-control form-control-sm" placeholder="0.00" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0" style="color: #29252A;">Descuento %</label>
                        <input type="number" step="0.01" id="inputDescuento" class="form-control form-control-sm" value="0" min="0" max="100" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-1">
                        <button type="button" id="btnAgregar" class="btn btn-sm w-100 py-2" style="background: #4CAF50; color: #fff; border: none;">
                            <i class="fas fa-plus fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE ÍTEMS -->
        <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: #FCE4EC;">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #FFF9F5;">
                        <tr>
                            <th class="ps-3" style="color: #29252A;">Artículo</th>
                            <th class="text-center" width="80" style="color: #29252A;">Cant.</th>
                            <th class="text-end" width="120" style="color: #29252A;">Precio</th>
                            <th class="text-end" width="100" style="color: #29252A;">Desc $</th>
                            <th class="text-end pe-3" width="120" style="color: #29252A;">Subtotal</th>
                            <th class="text-center" width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="carritoBody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center py-4" style="color: #4a454a;">
                                <i class="fas fa-shopping-cart me-2"></i>Sin ítems
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PANEL INFERIOR -->
        <div class="row g-3">
            <!-- IZQUIERDA: Pago + Observaciones + Guardar -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 mb-3" style="background: #FCE4EC;">
                    <div class="card-header fw-bold py-2" style="background: #FFF9F5; color: #29252A;">
                        <i class="fas fa-credit-card me-2"></i>Forma de Pago
                    </div>
                    <div class="card-body py-2">
                        <div class="row g-2">
                            <?php foreach ($formas_arr as $fp) { ?>
                                <div class="col-md-4">
                                    <label class="form-label fw-bold small mb-0" style="color: #29252A;"><?= htmlspecialchars($fp['descripcion']) ?></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text" style="background: #FFF9F5; color: #29252A; border-color: #F8BBD0;">$</span>
                                        <input type="number" step="0.01" class="form-control pago-input"
                                               data-id="<?= intval($fp['id_formas_pago']) ?>"
                                               value="0.00" min="0" style="border-color: #F8BBD0;">
                                    </div>
                                </div>
                            <?php } ?>
                        </div>
                        <hr class="my-2" style="border-color: #F8BBD0;">
                        <div class="mb-0">
                            <label class="form-label fw-bold small mb-0" style="color: #29252A;">Observaciones</label>
                            <textarea name="observaciones" class="form-control form-control-sm" rows="2" placeholder="Notas adicionales..." style="border-color: #F8BBD0;"></textarea>
                        </div>
                    </div>
                </div>

                <button type="submit" name="btnGuardar" class="btn btn-lg px-5 rounded-pill shadow"
                        style="background: #F48FB1; color: #fff; border: none;">
                    <i class="fas fa-save me-2"></i>Guardar Factura
                </button>
            </div>

            <!-- DERECHA: Total -->
            <div class="col-md-4">
                <div class="card border-0 shadow rounded-4" style="background: #29252A; color: #fff;">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase fw-bold mb-2" style="opacity: .75;">Total</h6>
                        <h1 class="display-5 fw-bold mb-0" id="displayTotal">$ 0,00</h1>
                        <div class="mt-2">
                            <small style="opacity: .5;">Pago: <span id="displayPago">$ 0,00</span></small><br>
                            <small style="opacity: .5;">Cambio: <span id="displayCambio">$ 0,00</span></small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
const productosData = <?= json_encode($productos_map) ?>;

let cart = [];

const selectProducto  = document.getElementById('selectProducto');
const inputCantidad   = document.getElementById('inputCantidad');
const inputPrecio     = document.getElementById('inputPrecio');
const inputDescuento  = document.getElementById('inputDescuento');
const btnAgregar      = document.getElementById('btnAgregar');
const carritoBody     = document.getElementById('carritoBody');
const displayTotal    = document.getElementById('displayTotal');
const displayPago     = document.getElementById('displayPago');
const displayCambio   = document.getElementById('displayCambio');
const displayEstado   = document.getElementById('displayEstado');
const montoTotalInput = document.getElementById('monto_total');
const montoPagoInput  = document.getElementById('monto_pago');
const montoCambioInput= document.getElementById('monto_cambio');
const carritoJsonInput= document.getElementById('carrito_json');
const pagosJsonInput  = document.getElementById('pagos_json');
const idFormaPagoInput= document.getElementById('id_formas_pago');

selectProducto.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const precio = opt.getAttribute('data-precio');
    if (precio) inputPrecio.value = parseFloat(precio).toFixed(2);
});

btnAgregar.addEventListener('click', function() {
    const id = selectProducto.value;
    if (!id) { alert('Seleccione un producto'); return; }

    const nombre     = selectProducto.options[selectProducto.selectedIndex].text;
    const precio     = parseFloat(inputPrecio.value) || 0;
    const cantidad   = parseInt(inputCantidad.value) || 1;
    const descPct    = parseFloat(inputDescuento.value) || 0;

    if (precio <= 0) { alert('Ingrese un precio válido'); return; }

    const subBruto   = cantidad * precio;
    const descAbs    = subBruto * (descPct / 100);
    const subtotal   = subBruto - descAbs;

    cart.push({ id: parseInt(id), nombre, cantidad, precio, descPct, descAbs, subtotal });

    selectProducto.value = '';
    inputCantidad.value  = 1;
    inputPrecio.value    = '';
    inputDescuento.value = 0;

    updateCart();
});

function removeFromCart(i) {
    cart.splice(i, 1);
    updateCart();
}

function updateCart() {
    carritoBody.innerHTML = '';

    if (cart.length === 0) {
        carritoBody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-shopping-cart me-2"></i>Sin ítems</td></tr>';
    } else {
        cart.forEach((item, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="ps-3 fw-bold">' + item.nombre + '</td>' +
                '<td class="text-center">' + item.cantidad + '</td>' +
                '<td class="text-end">$' + fmt(item.precio) + '</td>' +
                '<td class="text-end" style="color: #E57373;">-$' + fmt(item.descAbs) + '</td>' +
                '<td class="text-end pe-3 fw-bold">$' + fmt(item.subtotal) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm" style="background: transparent; color: #E57373; border: 1px solid #E57373;" onclick="removeFromCart(' + i + ')"><i class="fas fa-times"></i></button></td>';
            carritoBody.appendChild(tr);
        });
    }
    calculateTotals();
}

function calculateTotals() {
    let total = 0;
    cart.forEach(item => { total += item.subtotal; });

    let pago = 0;
    let metodoSeleccionado = 1;
    let maxPago = 0;
    let pagosArr = [];

    document.querySelectorAll('.pago-input').forEach(input => {
        const val = parseFloat(input.value) || 0;
        const fpId = parseInt(input.getAttribute('data-id')) || 1;
        if (val > 0) {
            pagosArr.push({ id_formas_pago: fpId, monto: val });
        }
        pago += val;
        if (val > maxPago) {
            maxPago = val;
            metodoSeleccionado = fpId;
        }
    });

    const cambio = pago - total;

    displayTotal.textContent  = '$ ' + fmt(total);
    displayPago.textContent   = '$ ' + fmt(pago);
    displayCambio.textContent = '$ ' + fmt(cambio);
    displayEstado.value       = (pago > 0 && pago >= total) ? 'PAGADO' : 'PENDIENTE';

    montoTotalInput.value  = total.toFixed(2);
    montoPagoInput.value   = pago.toFixed(2);
    montoCambioInput.value = cambio.toFixed(2);
    carritoJsonInput.value = JSON.stringify(cart);
    pagosJsonInput.value   = JSON.stringify(pagosArr);
    idFormaPagoInput.value = metodoSeleccionado;
}

function fmt(n) {
    return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.querySelectorAll('.pago-input').forEach(input => {
    input.addEventListener('input', calculateTotals);
});

document.getElementById('formVenta').addEventListener('submit', function() {
    calculateTotals();
});
</script>
