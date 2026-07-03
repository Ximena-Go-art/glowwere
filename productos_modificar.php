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
        <h3 class="fw-bold text-danger"><i class="fas fa-edit"></i> <?= empty($campos) ? 'Nuevo Producto' : 'Editar Producto' ?></h3>
        <a href="index.php?seccion=productos&accion=listar" class="btn btn-outline-secondary rounded-pill">
            <i class="fas fa-arrow-left"></i> Volver
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <form method="POST">
                <input type="hidden" name="id_producto" value="<?= $campos['id_producto'] ?? '' ?>">
                
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Código</label>
                        <input type="text" name="codigo" class="form-control" value="<?= $campos['codigo'] ?? '' ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nombre</label>
                        <input type="text" name="nombre" class="form-control" value="<?= $campos['nombre'] ?? '' ?>" required>
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Costo</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="costo" class="form-control" value="<?= $campos['costo'] ?? '' ?>">
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Precio Venta</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" step="0.01" name="precio" class="form-control" value="<?= $campos['precio'] ?? '' ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label fw-bold">Stock</label>
                        <input type="number" step="0.01" name="stock" class="form-control" value="<?= $campos['stock'] ?? '' ?>">
                    </div>
                </div>

                <div class="mb-4 form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="producto_activo" value="1" id="activo" 
                        <?= (isset($campos['producto_activo']) && $campos['producto_activo'] == 1) ? 'checked' : '' ?>>
                    <label class="form-check-label" for="activo">Producto Activo</label>
                </div>

                <button type="submit" name="btnGuardar" class="btn btn-danger btn-lg px-5 rounded-pill">
                    <i class="fas fa-save me-2"></i> Guardar Cambios
                </button>
            </form>
        </div>
    </div>
</div>