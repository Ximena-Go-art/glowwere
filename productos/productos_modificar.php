<?php
include_once "conexion.php";
$cnn = conection();

// Lógica de guardado
if (isset($_POST['btnGuardar'])) {
    $id = $_POST['id_producto'];
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $id_familia = !empty($_POST['id_familia']) ? intval($_POST['id_familia']) : 'NULL';
    $costo = $_POST['costo'];
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $activo = isset($_POST['producto_activo']) ? 1 : 0;

    if (empty($id)) {
        $sql = "INSERT INTO productos (codigo, nombre, id_familia, costo, precio, stock, producto_activo, deleted) 
                VALUES ('$codigo', '$nombre', $id_familia, '$costo', '$precio', '$stock', $activo, 0)";
    } else {
        $sql = "UPDATE productos SET codigo='$codigo', nombre='$nombre', id_familia=$id_familia, costo='$costo', 
                precio='$precio', stock='$stock', producto_activo=$activo 
                WHERE id_producto=$id";
    }

    if (mysqli_query($cnn, $sql)) {
        $nuevo_id = mysqli_insert_id($cnn);
        if (empty($id)) {
            registrar_accion($cnn, "Productos", "Registró el producto '$nombre'");
        } else {
            registrar_accion($cnn, "Productos", "Modificó el producto #$id ($nombre)");
        }
        echo "<script>
            window.location.href='index.php?seccion=productos&accion=listar';
        </script>";
    }
}

// Cargar datos si es edición
$campos = [];
if(isset($_GET['id'])){
    $idProducto = $_GET['id'];
    $result = mysqli_query($cnn, "SELECT p.*, f.familia AS nombre_familia FROM productos p LEFT JOIN familias f ON p.id_familia = f.id_familia WHERE p.id_producto='$idProducto'");
    $campos = mysqli_fetch_assoc($result);
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= empty($campos) ? 'Nuevo Producto' : 'Editar Producto' ?></h2>
            <p class="text-muted small">Completa los datos para <?= empty($campos) ? 'registrar' : 'actualizar' ?> el producto</p>
        </div>
        <a href="index.php?seccion=productos&accion=listar" class="btn rounded-pill px-4" style="border-color:#C5B4E3;color:#C5B4E3">Volver</a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form method="POST">
                        <input type="hidden" name="id_producto" value="<?= $campos['id_producto'] ?? '' ?>">
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Código</label>
                                <input type="text" name="codigo" class="form-control form-control-lg rounded-3" value="<?= $campos['codigo'] ?? '' ?>" required placeholder="Código del producto">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-bold small text-muted">Nombre</label>
                                <input type="text" name="nombre" class="form-control form-control-lg rounded-3" value="<?= $campos['nombre'] ?? '' ?>" required placeholder="Nombre del producto">
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3 position-relative">
                                <label class="form-label fw-bold small text-muted">Familia</label>
                                <input type="hidden" name="id_familia" id="id_familia" value="<?= $campos['id_familia'] ?? '' ?>">
                                <input type="text" id="familia_input" class="form-control form-control-lg rounded-3" 
                                       autocomplete="off" placeholder="Escriba para buscar familia..."
                                       value="<?= $campos['nombre_familia'] ?? ($campos['familia'] ?? '') ?>">
                                <div id="familia_sugerencias" class="list-group position-absolute w-100" 
                                     style="z-index:1050; display:none; top:100%; max-height:200px; overflow-y:auto;"></div>
                                <small id="familia_estado" class="text-muted">
                                    <?= !empty($campos['id_familia']) ? 'Familia: ' . htmlspecialchars($campos['nombre_familia'] ?? '') : 'Sin familia asignada' ?>
                                </small>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Costo</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="costo" class="form-control form-control-lg rounded-3" value="<?= $campos['costo'] ?? '' ?>" placeholder="0.00" style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Precio Venta</label>
                                <div class="input-group">
                                    <span class="input-group-text">$</span>
                                    <input type="number" step="0.01" name="precio" class="form-control form-control-lg rounded-3" value="<?= $campos['precio'] ?? '' ?>" required placeholder="0.00" style="border-top-left-radius:0;border-bottom-left-radius:0">
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-bold small text-muted">Stock</label>
                                <input type="number" step="0.01" name="stock" class="form-control form-control-lg rounded-3" value="<?= $campos['stock'] ?? '' ?>" placeholder="0">
                            </div>
                        </div>

                        <div class="mb-4 form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="producto_activo" value="1" id="activo" 
                                <?= (isset($campos['producto_activo']) && $campos['producto_activo'] == 1) ? 'checked' : '' ?>>
                            <label class="form-check-label" for="activo">Producto Activo</label>
                        </div>

                        <div class="d-grid">
                            <button type="submit" name="btnGuardar" class="btn btn-lg rounded-pill shadow-sm" style="background-color:#F48FB1;border-color:#F48FB1;color:#fff">
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
(function() {
    const input = document.getElementById('familia_input');
    const hidden = document.getElementById('id_familia');
    const sugerencias = document.getElementById('familia_sugerencias');
    const estado = document.getElementById('familia_estado');
    let timeout = null;

    input.addEventListener('input', function() {
        clearTimeout(timeout);
        const valor = this.value.trim();

        if (valor.length < 1) {
            sugerencias.style.display = 'none';
            sugerencias.innerHTML = '';
            hidden.value = '';
            estado.textContent = 'Sin familia asignada';
            return;
        }

        timeout = setTimeout(function() {
            fetch('familias/familias_buscar.php?q=' + encodeURIComponent(valor))
                .then(function(resp) { return resp.json(); })
                .then(function(data) {
                    sugerencias.innerHTML = '';
                    if (data.length === 0) {
                        sugerencias.style.display = 'none';
                        return;
                    }
                    data.forEach(function(item) {
                        const btn = document.createElement('button');
                        btn.type = 'button';
                        btn.className = 'list-group-item list-group-item-action';
                        btn.textContent = item.familia;
                        btn.addEventListener('click', function() {
                            input.value = item.familia;
                            hidden.value = item.id_familia;
                            sugerencias.style.display = 'none';
                            sugerencias.innerHTML = '';
                            estado.textContent = 'Familia: ' + item.familia;
                        });
                        sugerencias.appendChild(btn);
                    });
                    sugerencias.style.display = 'block';
                });
        }, 300);
    });

    input.addEventListener('blur', function() {
        setTimeout(function() { sugerencias.style.display = 'none'; }, 200);
    });

    input.addEventListener('focus', function() {
        if (sugerencias.children.length > 0 && input.value.trim().length >= 1) {
            sugerencias.style.display = 'block';
        }
    });
})();
</script>