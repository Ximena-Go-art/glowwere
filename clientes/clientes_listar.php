<?php
include "conexion.php";
include_once "paginador.php";
$cnn = conection();

/* Eliminación lógica */
if (isset($_GET['eliminar']) && is_numeric($_GET['eliminar'])) {
    $id_cliente = intval($_GET['eliminar']);
    $sql_eliminar = "UPDATE clientes SET deleted = 1 WHERE id_cliente = $id_cliente";
    
    if (mysqli_query($cnn, $sql_eliminar)) {
        registrar_accion($cnn, "Clientes", "Eliminó el cliente #$id_cliente");
        echo "<script>window.location='index.php?seccion=clientes&accion=listar';</script>";
    } else {
        echo "<div class='alert alert-danger m-3'>Error al eliminar: " . mysqli_error($cnn) . "</div>";
    }
}

/* Filtros */
$filtro_buscar = isset($_GET['f_buscar']) ? trim($_GET['f_buscar']) : '';
$condiciones = array("deleted = 0");
if ($filtro_buscar != '') {
    $buscarSeguro = mysqli_real_escape_string($cnn, $filtro_buscar);
    $condiciones[] = "(cliente LIKE '%$buscarSeguro%' OR documento LIKE '%$buscarSeguro%')";
}
$where = implode(" AND ", $condiciones);

/* Ordenamiento */
$orden = isset($_GET['orden']) ? $_GET['orden'] : 'id_cliente';
$direccion = (isset($_GET['dir']) && $_GET['dir'] === 'DESC') ? 'DESC' : 'ASC';

/* Consulta */
$sql = "SELECT id_cliente, cliente, documento FROM clientes WHERE $where ORDER BY $orden $direccion";
$pag = paginar_consulta($cnn, $sql, 10);
$resultado = $pag['data'];
$hayFiltros = ($filtro_buscar != '');
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold m-0" style="color: #29252A;">Clientes</h2>
            <p class="small" style="color: #4a454a;">Gestión y listado de clientes registrados</p>
        </div>
        <a href="index.php?seccion=clientes&accion=modificar" class="btn rounded-pill px-4" style="background: #F48FB1; color: #fff; border: none;">
            + Nuevo Cliente
        </a>
    </div>

    <!-- FILTROS -->
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #FCE4EC;">
        <div class="card-body py-3">
            <form method="GET" action="index.php">
                <input type="hidden" name="seccion" value="clientes">
                <input type="hidden" name="accion" value="listar">
                <div class="row g-3 align-items-end">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold" style="color: #29252A;">Buscar</label>
                        <input type="text" name="f_buscar" class="form-control" placeholder="Nombre o documento..."
                               value="<?= htmlspecialchars($filtro_buscar) ?>" style="border-color: #F8BBD0;">
                    </div>
                    <div class="col-md-6 d-flex align-items-end gap-2">
                        <button type="submit" class="btn rounded-pill px-4" style="background: #F48FB1; color: #fff; border: none;"><i class="fas fa-filter me-1"></i> Filtrar</button>
                        <?php if($hayFiltros){ ?>
                            <a href="index.php?seccion=clientes&accion=listar" class="btn rounded-pill px-4" style="color: #29252A; border: 1px solid #C5B4E3; background: transparent;">Limpiar</a>
                        <?php } ?>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4" style="background: #FCE4EC;">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead style="background: #FFF9F5;">
                        <tr>
                            <th class="ps-4" style="color: #29252A;">
                                <?php $nuevaDir = ($direccion === 'ASC') ? 'DESC' : 'ASC'; $sp = $_GET; $sp['orden'] = 'id_cliente'; $sp['dir'] = $nuevaDir; unset($sp['pagina']); ?>
                                <a href="index.php?<?= http_build_query($sp) ?>" style="text-decoration:none; color:inherit;">ID <?= $direccion === 'ASC' ? '<i class="fas fa-sort-up"></i>' : '<i class="fas fa-sort-down"></i>' ?></a>
                            </th>
                            <th style="color: #29252A;">Nombre del Cliente</th>
                            <th style="color: #29252A;">Documento</th>
                            <th class="text-end pe-4" style="color: #29252A;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($fila = mysqli_fetch_assoc($resultado)){ ?>
                        <tr>
                            <td class="ps-4 fw-bold" style="color: #4a454a;"><?= $fila['id_cliente'] ?></td>
                            <td>
                                <div class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['cliente']) ?></div>
                            </td>
                            <td style="color: #29252A;"><?= htmlspecialchars($fila['documento']) ?></td>
                            <td class="text-end pe-4">
                                <a href="index.php?seccion=clientes&accion=modificar&id=<?= $fila['id_cliente'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Editar"
                                   style="background: transparent; color: #E6C24D; border: 1px solid #E6C24D;">
                                   <i class="fas fa-pen"></i>
                                </a>
                                <a href="index.php?seccion=clientes&accion=listar&eliminar=<?= $fila['id_cliente'] ?>" 
                                   class="btn btn-sm rounded-pill px-3" title="Eliminar"
                                   style="background: transparent; color: #E57373; border: 1px solid #E57373;"
                                   onclick="return confirm('¿Está seguro de eliminar este cliente?');">
                                   <i class="fas fa-trash"></i>
                                </a>
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>
        <?php render_paginador($pag); ?>
    </div>
</div>