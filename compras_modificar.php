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

$proveedores =
mysqli_query(
    $cnn,
    "SELECT id_proveedor, proveedor
     FROM proveedores
     WHERE deleted = 0"
);

$tipos =
mysqli_query(
    $cnn,
    "SELECT id_tipo_documento, descripcion
     FROM tipos_documentos
     WHERE deleted = 0"
);

/* Datos */

$datos = [
    'id_compra' => '',
    'id_usuario' => '',
    'id_proveedor' => '',
    'id_tipo_documento' => '',
    'numero_documento' => '',
    'monto_total' => '',
    'fecha_registro' => date('Y-m-d')
];

/* Guardar */

if (isset($_POST['btnGuardar'])) {

    $id_compra = intval($_POST['id_compra']);

    $id_usuario = intval($_POST['id_usuario']);
    $id_proveedor = intval($_POST['id_proveedor']);
    $id_tipo_documento = intval($_POST['id_tipo_documento']);

    $numero_documento =
    mysqli_real_escape_string(
        $cnn,
        trim($_POST['numero_documento'])
    );

    $monto_total =
    floatval($_POST['monto_total']);

    $fecha_registro =
    $_POST['fecha_registro'];

    if ($id_compra == 0) {

        $sql = "
        INSERT INTO compras
        (
            id_usuario,
            id_proveedor,
            id_tipo_documento,
            numero_documento,
            monto_total,
            fecha_registro
        )
        VALUES
        (
            $id_usuario,
            $id_proveedor,
            $id_tipo_documento,
            '$numero_documento',
            $monto_total,
            '$fecha_registro'
        )";

    } else {

        $sql = "
        UPDATE compras
        SET
            id_usuario = $id_usuario,
            id_proveedor = $id_proveedor,
            id_tipo_documento = $id_tipo_documento,
            numero_documento = '$numero_documento',
            monto_total = $monto_total,
            fecha_registro = '$fecha_registro'
        WHERE id_compra = $id_compra";
    }

    $resultado = mysqli_query($cnn, $sql);

    if ($resultado) {

        echo "
        <script>
        window.location='index.php?seccion=compras&accion=listar';
        </script>";
        exit;
    }

    echo mysqli_error($cnn);
}

/* Cargar */

if (isset($_GET['id'])) {

    $id_compra = intval($_GET['id']);

    $sql = "
    SELECT *
    FROM compras
    WHERE id_compra = $id_compra
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
            <h2 class="fw-bold m-0"><?= ($datos['id_compra'] != '') ? 'Editar Compra' : 'Nueva Compra' ?></h2>
            <p class="text-muted small">Registra una compra realizada a un proveedor</p>
        </div>
        <a href="index.php?seccion=compras&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_compra" value="<?= $datos['id_compra'] ?>">

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Usuario</label>
                                <select name="id_usuario" class="form-select form-select-lg rounded-3">
                                    <option value="">Seleccione</option>
                                    <?php mysqli_data_seek($usuarios, 0); while($u = mysqli_fetch_assoc($usuarios)){ ?>
                                    <option value="<?= $u['id_usuario'] ?>" <?= ($datos['id_usuario']==$u['id_usuario']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($u['usuario']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Proveedor</label>
                                <select name="id_proveedor" class="form-select form-select-lg rounded-3">
                                    <option value="">Seleccione</option>
                                    <?php mysqli_data_seek($proveedores, 0); while($p = mysqli_fetch_assoc($proveedores)){ ?>
                                    <option value="<?= $p['id_proveedor'] ?>" <?= ($datos['id_proveedor']==$p['id_proveedor']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($p['proveedor']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Tipo Documento</label>
                                <select name="id_tipo_documento" class="form-select form-select-lg rounded-3">
                                    <option value="">Seleccione</option>
                                    <?php mysqli_data_seek($tipos, 0); while($t = mysqli_fetch_assoc($tipos)){ ?>
                                    <option value="<?= $t['id_tipo_documento'] ?>" <?= ($datos['id_tipo_documento']==$t['id_tipo_documento']) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($t['descripcion']) ?>
                                    </option>
                                    <?php } ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">N° Documento</label>
                                <input type="text" name="numero_documento" class="form-control form-control-lg rounded-3" 
                                       value="<?= htmlspecialchars($datos['numero_documento']) ?>" placeholder="Número de factura">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Monto Total</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="monto_total" class="form-control form-control-lg rounded-3" 
                                           value="<?= $datos['monto_total'] ?>" placeholder="0.00" style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Fecha</label>
                                <input type="date" name="fecha_registro" class="form-control form-control-lg rounded-3" 
                                       value="<?= substr($datos['fecha_registro'],0,10) ?>">
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