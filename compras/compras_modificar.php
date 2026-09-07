<?php
include_once "conexion.php";
$cnn = conection();

// --- LÓGICA DE GUARDADO ---
if (isset($_POST['btnGuardar'])) {
    $id_compra         = intval($_POST['id_compra']);
    $id_usuario        = intval($_POST['id_usuario']);
    $id_proveedor      = intval($_POST['id_proveedor']);
    $id_tipo_documento = intval($_POST['id_tipo_documento']);
    $numero_documento  = mysqli_real_escape_string($cnn, trim($_POST['numero_documento']));
    $monto_total       = floatval($_POST['monto_total']);
    $fecha_registro    = $_POST['fecha_registro'];

    $carrito     = json_decode($_POST['carrito_json'] ?? '[]', true);

    if ($id_compra == 0) {
        $sql = "INSERT INTO compras (id_usuario, id_proveedor, id_tipo_documento, numero_documento, monto_total, fecha_registro)
                VALUES ($id_usuario, $id_proveedor, $id_tipo_documento, '$numero_documento', $monto_total, '$fecha_registro')";
    } else {
        $sql = "UPDATE compras SET id_usuario=$id_usuario, id_proveedor=$id_proveedor, id_tipo_documento=$id_tipo_documento,
                numero_documento='$numero_documento', monto_total=$monto_total, fecha_registro='$fecha_registro'
                WHERE id_compra=$id_compra";
    }

    if (mysqli_query($cnn, $sql)) {
        $compra_id = $id_compra == 0 ? mysqli_insert_id($cnn) : $id_compra;

        // Si es edición, revertir stock y cascade delete de detalles anteriores
        if ($id_compra != 0) {
            $detallesActuales = mysqli_query($cnn, "SELECT id_producto, cantidad FROM compra_detalles WHERE id_compra = $compra_id AND deleted = 0");
            if ($detallesActuales) {
                while ($d = mysqli_fetch_assoc($detallesActuales)) {
                    mysqli_query($cnn, "UPDATE productos SET stock = stock - " . intval($d['cantidad']) . " WHERE id_producto = " . intval($d['id_producto']));
                }
            }
            mysqli_query($cnn, "UPDATE compra_detalles SET deleted = 1 WHERE id_compra = $compra_id");
        }

        // Guardar ítems del carrito en compra_detalles y sumar stock
        if (!empty($carrito)) {
            foreach ($carrito as $item) {
                $id_producto  = intval($item['id']);
                $cantidad     = intval($item['cantidad']);
                $precio_unit  = floatval($item['precio']);
                $precio_total = floatval($item['subtotal']);
                mysqli_query($cnn, "INSERT INTO compra_detalles (id_compra, id_producto, cantidad, precio_unitario, precio_total, deleted)
                                    VALUES ($compra_id, $id_producto, $cantidad, $precio_unit, $precio_total, 0)");
                mysqli_query($cnn, "UPDATE productos SET stock = stock + $cantidad WHERE id_producto = $id_producto");
            }
        }

        if ($id_compra == 0) {
            registrar_accion($cnn, "Compras", "Registró la compra #$compra_id del proveedor ID $id_proveedor por $" . number_format($monto_total, 2, ',', '.'));
        } else {
            registrar_accion($cnn, "Compras", "Modificó la compra #$id_compra");
        }
        echo "<script>window.location='index.php?seccion=compras&accion=listar';</script>";
        exit;
    } else {
        die("<div class='alert alert-danger m-3'><strong>Error:</strong> " . mysqli_error($cnn) . "</div>");
    }
}

// --- CONSULTAS ---
$usuarios    = mysqli_query($cnn, "SELECT id_usuario, usuario FROM usuarios WHERE deleted = 0");
$proveedores = mysqli_query($cnn, "SELECT id_proveedor, proveedor FROM proveedores WHERE deleted = 0");
$productos   = mysqli_query($cnn, "SELECT id_producto, nombre, precio FROM productos WHERE deleted = 0");
$tipos       = mysqli_query($cnn, "SELECT id_tipo_documento, descripcion FROM tipos_documentos WHERE deleted = 0");

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
    'id_compra'         => '',
    'id_usuario'        => $_SESSION['id_usuario'] ?? 1,
    'id_proveedor'      => '',
    'id_tipo_documento' => '',
    'numero_documento'  => '',
    'monto_total'       => 0,
    'fecha_registro'    => date('Y-m-d')
];

if (isset($_GET['id'])) {
    $id_compra = intval($_GET['id']);
    $res = mysqli_query($cnn, "SELECT * FROM compras WHERE id_compra = $id_compra");
    if ($res && mysqli_num_rows($res) > 0) {
        $datos = mysqli_fetch_assoc($res);
    }
}
?>

<div class="container-fluid py-3">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h4 class="fw-bold m-0" style="color:#E57373"><i class="fas fa-truck me-2"></i>Punto de Compra</h4>
        <a href="index.php?seccion=compras&accion=listar" class="btn rounded-pill" style="border-color:#C5B4E3;color:#C5B4E3">Volver</a>
    </div>

    <form method="POST" id="formCompra">
        <input type="hidden" name="id_compra" value="<?= $datos['id_compra'] ?>">
        <input type="hidden" name="id_usuario" value="<?= $datos['id_usuario'] ?>">
        <input type="hidden" name="carrito_json" id="carrito_json">
        <input type="hidden" name="monto_total" id="monto_total" value="0">

        <!-- ENCABEZADO -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body py-2">
                <div class="row align-items-end g-2">
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted mb-0">Fecha</label>
                        <input type="date" name="fecha_registro" class="form-control form-control-sm" value="<?= substr($datos['fecha_registro'], 0, 10) ?>">
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted mb-0">Proveedor</label>
                        <select name="id_proveedor" class="form-select form-select-sm" required>
                            <option value="">Seleccione...</option>
                            <?php if ($proveedores) { mysqli_data_seek($proveedores, 0); while ($pr = mysqli_fetch_assoc($proveedores)) { ?>
                                <option value="<?= $pr['id_proveedor'] ?>" <?= ($datos['id_proveedor'] == $pr['id_proveedor']) ? 'selected' : '' ?>><?= htmlspecialchars($pr['proveedor']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold small text-muted mb-0">Tipo Documento</label>
                        <select name="id_tipo_documento" class="form-select form-select-sm" required>
                            <option value="">Seleccione...</option>
                            <?php if ($tipos) { mysqli_data_seek($tipos, 0); while ($t = mysqli_fetch_assoc($tipos)) { ?>
                                <option value="<?= $t['id_tipo_documento'] ?>" <?= ($datos['id_tipo_documento'] == $t['id_tipo_documento']) ? 'selected' : '' ?>><?= htmlspecialchars($t['descripcion']) ?></option>
                            <?php } } ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted mb-0">N° Documento</label>
                        <input type="text" name="numero_documento" class="form-control form-control-sm" value="<?= htmlspecialchars($datos['numero_documento']) ?>" placeholder="N° de factura">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small text-muted mb-0">Estado</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="REGISTRADA" readonly>
                    </div>
                </div>
            </div>
        </div>

        <!-- AGREGAR ARTÍCULO -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-header bg-success text-white fw-bold py-2">
                <i class="fas fa-plus-circle me-2"></i>Agregar Artículo
            </div>
            <div class="card-body py-2">
                <div class="row align-items-end g-2">
                    <div class="col-md-4">
                        <label class="form-label fw-bold small mb-0">Producto</label>
                        <select id="selectProducto" class="form-select form-select-sm">
                            <option value="">Seleccione un producto...</option>
                            <?php foreach ($productos_map as $id => $p) { ?>
                                <option value="<?= $id ?>" data-precio="<?= $p['precio'] ?>"><?= htmlspecialchars($p['nombre']) ?></option>
                            <?php } ?>
                        </select>
                    </div>
                    <div class="col-md-1">
                        <label class="form-label fw-bold small mb-0">Cant.</label>
                        <input type="number" id="inputCantidad" class="form-control form-control-sm" value="1" min="1">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0">Precio Unit.</label>
                        <input type="number" step="0.01" id="inputPrecio" class="form-control form-control-sm" placeholder="0.00">
                    </div>
                    <div class="col-md-2">
                        <label class="form-label fw-bold small mb-0">Descuento %</label>
                        <input type="number" step="0.01" id="inputDescuento" class="form-control form-control-sm" value="0" min="0" max="100">
                    </div>
                    <div class="col-md-1">
                        <button type="button" id="btnAgregar" class="btn btn-success btn-sm w-100 py-2">
                            <i class="fas fa-plus fa-lg"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- TABLA DE ÍTEMS -->
        <div class="card border-0 shadow-sm rounded-4 mb-3">
            <div class="card-body p-0">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background-color:#FFF9F5">
                        <tr>
                            <th class="ps-3">Artículo</th>
                            <th class="text-center" width="80">Cant.</th>
                            <th class="text-end" width="120">Precio</th>
                            <th class="text-end" width="100">Desc $</th>
                            <th class="text-end pe-3" width="120">Subtotal</th>
                            <th class="text-center" width="50"></th>
                        </tr>
                    </thead>
                    <tbody id="carritoBody">
                        <tr id="emptyRow">
                            <td colspan="6" class="text-center text-muted py-4">
                                <i class="fas fa-box-open me-2"></i>Sin ítems
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- PANEL INFERIOR -->
        <div class="row g-3">
            <!-- IZQUIERDA: Observaciones + Guardar -->
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 mb-3">
                    <div class="card-header bg-light fw-bold py-2">
                        <i class="fas fa-sticky-note me-2"></i>Observaciones
                    </div>
                    <div class="card-body py-2">
                        <textarea name="observaciones" class="form-control form-control-sm" rows="3" placeholder="Notas adicionales sobre la compra..."></textarea>
                    </div>
                </div>

                <button type="submit" name="btnGuardar" class="btn btn-success btn-lg px-5 rounded-pill shadow">
                    <i class="fas fa-save me-2"></i>Guardar Compra
                </button>
            </div>

            <!-- DERECHA: Total -->
            <div class="col-md-4">
                <div class="card border-0 shadow rounded-4 bg-dark text-white">
                    <div class="card-body text-center py-4">
                        <h6 class="text-uppercase fw-bold mb-2 opacity-75">Total</h6>
                        <h1 class="display-5 fw-bold mb-0" id="displayTotal">$ 0,00</h1>
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
const montoTotalInput = document.getElementById('monto_total');
const carritoJsonInput= document.getElementById('carrito_json');

selectProducto.addEventListener('change', function() {
    const opt = this.options[this.selectedIndex];
    const precio = opt.getAttribute('data-precio');
    if (precio) inputPrecio.value = parseFloat(precio).toFixed(2);
});

btnAgregar.addEventListener('click', function() {
    const id = selectProducto.value;
    if (!id) { alert('Seleccione un producto'); return; }

    const nombre   = selectProducto.options[selectProducto.selectedIndex].text;
    const precio   = parseFloat(inputPrecio.value) || 0;
    const cantidad = parseInt(inputCantidad.value) || 1;
    const descPct  = parseFloat(inputDescuento.value) || 0;

    if (precio <= 0) { alert('Ingrese un precio válido'); return; }

    const subBruto = cantidad * precio;
    const descAbs  = subBruto * (descPct / 100);
    const subtotal = subBruto - descAbs;

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
        carritoBody.innerHTML = '<tr id="emptyRow"><td colspan="6" class="text-center text-muted py-4"><i class="fas fa-box-open me-2"></i>Sin ítems</td></tr>';
    } else {
        cart.forEach((item, i) => {
            const tr = document.createElement('tr');
            tr.innerHTML =
                '<td class="ps-3 fw-bold">' + item.nombre + '</td>' +
                '<td class="text-center">' + item.cantidad + '</td>' +
                '<td class="text-end">$' + fmt(item.precio) + '</td>' +
                '<td class="text-end text-danger">-$' + fmt(item.descAbs) + '</td>' +
                '<td class="text-end pe-3 fw-bold">$' + fmt(item.subtotal) + '</td>' +
                '<td class="text-center"><button type="button" class="btn btn-sm btn-outline-danger" onclick="removeFromCart(' + i + ')"><i class="fas fa-times"></i></button></td>';
            carritoBody.appendChild(tr);
        });
    }
    calculateTotals();
}

function calculateTotals() {
    let total = 0;
    cart.forEach(item => { total += item.subtotal; });

    displayTotal.textContent = '$ ' + fmt(total);
    montoTotalInput.value    = total.toFixed(2);
    carritoJsonInput.value   = JSON.stringify(cart);
}

function fmt(n) {
    return n.toLocaleString('es-AR', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
}

document.getElementById('formCompra').addEventListener('submit', function() {
    calculateTotals();
});
</script>
