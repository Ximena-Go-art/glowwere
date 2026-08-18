<?php
include_once "conexion.php";
$cnn = conection();

// Lógica de guardado simplificada
if (isset($_POST['btnGuardar'])) {
    $id = $_POST['id_producto'];
    $codigo = $_POST['codigo'];
    $nombre = $_POST['nombre'];
    $costo = $_POST['costo']; // Nuevo campo
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $activo = isset($_POST['producto_activo']) ? 1 : 0;

    if (empty($id)) {
        $sql = "INSERT INTO productos (codigo, nombre, costo, precio, stock, producto_activo, deleted) 
                VALUES ('$codigo', '$nombre', '$costo', '$precio', '$stock', $activo, 0)";
    } else {
        $sql = "UPDATE productos SET codigo='$codigo', nombre='$nombre', costo='$costo', 
                precio='$precio', stock='$stock', producto_activo=$activo 
                WHERE id_producto=$id";
    }

    if (mysqli_query($cnn, $sql)) {
        echo "<script>
            window.location.href='index.php?seccion=productos&accion=listar';
        </script>";
    }
}

// Cargar datos si es edición
$campos = [];
if(isset($_GET['id'])){
    $idProducto = $_GET['id'];
    $result = mysqli_query($cnn, "SELECT * FROM productos WHERE id_producto='$idProducto'");
    $campos = mysqli_fetch_assoc($result);
}
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0"><?= empty($campos) ? 'Nuevo Producto' : 'Editar Producto' ?></h2>
            <p class="text-muted small">Completa los datos para <?= empty($campos) ? 'registrar' : 'actualizar' ?> el producto</p>
        </div>
        <a href="index.php?seccion=productos&accion=listar" class="btn btn-outline-secondary rounded-pill px-4">Volver</a>
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