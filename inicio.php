<?php
include_once "conexion.php";
$cnn = conection();

/* ═══════════════════════════════════════
   CONSULTAS DEL DASHBOARD
   ═══════════════════════════════════════ */

// --- KPIs ---

// Ventas hoy
$resVenHoy = mysqli_query($cnn, "SELECT IFNULL(SUM(monto_total),0) AS total, COUNT(*) AS cantidad
    FROM ventas WHERE deleted=0 AND DATE(fecha_registro) = CURDATE()");
$venHoy = mysqli_fetch_assoc($resVenHoy);

// Ventas ayer
$resVenAyer = mysqli_query($cnn, "SELECT IFNULL(SUM(monto_total),0) AS total
    FROM ventas WHERE deleted=0 AND DATE(fecha_registro) = DATE_SUB(CURDATE(), INTERVAL 1 DAY)");
$venAyer = mysqli_fetch_assoc($resVenAyer);

// Ventas mes actual
$primerDiaMes = date('Y-m-01');
$resVenMes = mysqli_query($cnn, "SELECT IFNULL(SUM(monto_total),0) AS total, COUNT(*) AS cantidad
    FROM ventas WHERE deleted=0 AND fecha_registro >= '$primerDiaMes 00:00:00'");
$venMes = mysqli_fetch_assoc($resVenMes);

// Ventas mes anterior
$primerDiaMesAnt = date('Y-m-01', strtotime('-1 month'));
$ultimoDiaMesAnt = date('Y-m-t', strtotime('-1 month'));
$resVenMesAnt = mysqli_query($cnn, "SELECT IFNULL(SUM(monto_total),0) AS total
    FROM ventas WHERE deleted=0
    AND fecha_registro >= '$primerDiaMesAnt 00:00:00'
    AND fecha_registro <= '$ultimoDiaMesAnt 23:59:59'");
$venMesAnt = mysqli_fetch_assoc($resVenMesAnt);

// Ticket promedio hoy
$resTicket = mysqli_query($cnn, "SELECT IFNULL(AVG(monto_total),0) AS promedio
    FROM ventas WHERE deleted=0 AND DATE(fecha_registro) = CURDATE()");
$ticket = mysqli_fetch_assoc($resTicket);

// Unidades vendidas hoy
$resUnidades = mysqli_query($cnn, "SELECT IFNULL(SUM(vd.cantidad_productos),0) AS total
    FROM ventas_detalles vd INNER JOIN ventas v ON vd.id_ventas = v.id_ventas
    WHERE vd.deleted=0 AND v.deleted=0 AND DATE(v.fecha_registro) = CURDATE()");
$unidades = mysqli_fetch_assoc($resUnidades);

// --- ALERTAS / INVENTARIO ---

// Stock bajo (<=5)
$resStockBajo = mysqli_query($cnn, "SELECT p.nombre, p.stock, f.familia
    FROM productos p LEFT JOIN familias f ON p.id_familia = f.id_familia
    WHERE p.deleted=0 AND p.stock <= 5 ORDER BY p.stock ASC LIMIT 10");

// Top 5 más vendidos
$resTop5 = mysqli_query($cnn, "SELECT p.nombre, SUM(vd.cantidad_productos) AS total_vendidos
    FROM ventas_detalles vd
    INNER JOIN productos p ON vd.id_producto = p.id_producto
    WHERE vd.deleted=0
    GROUP BY p.nombre
    ORDER BY total_vendidos DESC LIMIT 5");

// --- ANÁLISIS ---

// Clientes frecuentes (top 5 por monto)
$resClientes = mysqli_query($cnn, "SELECT c.cliente, COUNT(v.id_ventas) AS compras, SUM(v.monto_total) AS total_gastado
    FROM ventas v
    INNER JOIN clientes c ON v.id_cliente = c.id_cliente
    WHERE v.deleted=0 AND c.deleted=0
    GROUP BY c.cliente
    ORDER BY total_gastado DESC LIMIT 5");

// Ventas por categoría
$resCategorias = mysqli_query($cnn, "SELECT f.familia, COUNT(vd.id_ventas_detalles) AS items_vendidos, SUM(vd.monto_total) AS total
    FROM ventas_detalles vd
    INNER JOIN productos p ON vd.id_producto = p.id_producto
    INNER JOIN familias f ON p.id_familia = f.id_familia
    WHERE vd.deleted=0
    GROUP BY f.familia
    ORDER BY total DESC");

// --- Helpers ---
function comparativa($actual, $anterior, $fmt = 'moneda') {
    if ($anterior == 0) return '<span class="small" style="color: #4a454a;">Sin datos previos</span>';
    $pct = (($actual - $anterior) / $anterior) * 100;
    $icon = $pct >= 0 ? 'fa-arrow-up' : 'fa-arrow-down';
    $color = $pct >= 0 ? '#4CAF50' : '#E57373';
    $signo = $pct >= 0 ? '+' : '';
    return "<p class=\"small m-0\" style=\"color: {$color};\"><i class=\"fas {$icon}\"></i> {$signo}" . number_format($pct, 1) . "% vs anterior</p>";
}

function fmt_money($v) { return '$ ' . number_format($v, 0, ',', '.'); }
function fmt_num($v) { return number_format($v, 0, ',', '.'); }
?>

<div class="container-fluid py-4">

    <!-- ENCABEZADO -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-3 pt-4 px-4 rounded-4" style="background: linear-gradient(135deg, #F8BBD0, #C5B4E3);">
        <div>
            <h2 class="fw-bold m-0" style="color: #29252A;">Hola, <?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado') ?> 👋</h2>
            <p class="small m-0" style="color: #4a454a;">Aquí tenés el resumen de tu negocio de cosmética de hoy.</p>
        </div>
        <div class="text-end">
            <span class="small fw-bold" style="color: #29252A;"><?= date('d/m/Y') ?></span>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECCIÓN 1: ACCESOS RÁPIDOS
         ═══════════════════════════════════════ -->
    <div class="row g-3 mb-4">
        <div class="col-md-6 col-xl-3">
            <a href="index.php?seccion=ventas&accion=modificar" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 card-hover" style="background: #F48FB1;">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="color: #F48FB1;">
                            <i class="fas fa-plus fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #fff;">Nueva Venta</h6>
                            <small style="color: rgba(255,255,255,.75);">Registrar cobro</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="index.php?seccion=cajas&accion=listar" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 card-hover" style="background: #A9DDF5;">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="color: #5BAFD4;">
                            <i class="fas fa-wallet fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #29252A;">Registrar Caja</h6>
                            <small style="color: #4a454a;">Ingreso de dinero</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="index.php?seccion=compras&accion=modificar" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 card-hover" style="background: #C5B4E3;">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="color: #9575CD;">
                            <i class="fas fa-truck fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #fff;">Cargar Pedido</h6>
                            <small style="color: rgba(255,255,255,.75);">Pedido a proveedor</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        <div class="col-md-6 col-xl-3">
            <a href="index.php?seccion=productos&accion=modificar" class="text-decoration-none">
                <div class="card border-0 shadow-sm rounded-4 h-100 card-hover" style="background: #FFE8A3;">
                    <div class="card-body d-flex align-items-center p-4">
                        <div class="bg-white rounded-circle p-3 me-3 d-flex align-items-center justify-content-center" style="color: #E6C24D;">
                            <i class="fas fa-cube fa-lg"></i>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0" style="color: #29252A;">Agregar Producto</h6>
                            <small style="color: #4a454a;">Nuevo al catálogo</small>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECCIÓN 2: KPIs PRINCIPALES
         ═══════════════════════════════════════ -->
    <div class="row g-3 mb-4">

        <!-- Ventas Hoy -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-uppercase m-0 small" style="color: #29252A;">Ventas Hoy</h6>
                        <div class="rounded p-2 d-flex align-items-center justify-content-center" style="background: #E8F5E9; color: #4CAF50;">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" style="color: #29252A;"><?= fmt_money($venHoy['total']) ?></h3>
                    <?= comparativa($venHoy['total'], $venAyer['total']) ?>
                </div>
            </div>
        </div>

        <!-- Ventas Mes -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-uppercase m-0 small" style="color: #29252A;">Ventas Mes</h6>
                        <div class="rounded p-2 d-flex align-items-center justify-content-center" style="background: #F3E5F5; color: #9575CD;">
                            <i class="fas fa-calendar-check"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" style="color: #29252A;"><?= fmt_money($venMes['total']) ?></h3>
                    <?= comparativa($venMes['total'], $venMesAnt['total']) ?>
                </div>
            </div>
        </div>

        <!-- Ticket Promedio -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-uppercase m-0 small" style="color: #29252A;">Ticket Promedio</h6>
                        <div class="rounded p-2 d-flex align-items-center justify-content-center" style="background: #FFF8E1; color: #E6C24D;">
                            <i class="fas fa-receipt"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" style="color: #29252A;"><?= fmt_money($ticket['promedio']) ?></h3>
                    <p class="small m-0" style="color: #4a454a;">Promedio por venta hoy</p>
                </div>
            </div>
        </div>

        <!-- Unidades Vendidas -->
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-uppercase m-0 small" style="color: #29252A;">Unidades Vendidas</h6>
                        <div class="rounded p-2 d-flex align-items-center justify-content-center" style="background: #E3F2FD; color: #5BAFD4;">
                            <i class="fas fa-box-open"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1" style="color: #29252A;"><?= fmt_num($unidades['total']) ?></h3>
                    <p class="small m-0" style="color: #4a454a;">Productos comercializados hoy</p>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECCIÓN 3: ALERTAS + INVENTARIO
         ═══════════════════════════════════════ -->
    <div class="row g-4 mb-4">

        <!-- Stock Bajo -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-header border-0 pt-4 pb-0 px-4" style="background: transparent;">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="fw-bold" style="color: #29252A;"><i class="fas fa-exclamation-triangle me-2" style="color: #E57373;"></i>Stock Bajo</h5>
                        <a href="index.php?seccion=productos&accion=listar" class="btn btn-sm rounded-pill" style="background: #F48FB1; color: #fff; border: none;">Ver todo</a>
                    </div>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (mysqli_num_rows($resStockBajo) == 0): ?>
                        <div class="text-center py-4" style="color: #4a454a;">
                            <i class="fas fa-check-circle fa-2x mb-2" style="color: #4CAF50;"></i>
                            <p class="m-0">Todos los productos tienen stock suficiente</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background: #FFF9F5;">
                                        <th style="color: #29252A;">Producto</th>
                                        <th style="color: #29252A;">Familia</th>
                                        <th class="text-center" style="color: #29252A;">Stock</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($fila = mysqli_fetch_assoc($resStockBajo)): ?>
                                    <tr>
                                        <td class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['nombre']) ?></td>
                                        <td style="color: #4a454a;"><?= htmlspecialchars($fila['familia'] ?? '-') ?></td>
                                        <td class="text-center">
                                            <?php
                                            $stock = intval($fila['stock']);
                                            if ($stock <= 2) {
                                                echo '<span class="badge rounded-pill" style="background: #E57373; color: #fff;">' . $stock . ' un.</span>';
                                            } else {
                                                echo '<span class="badge rounded-pill" style="background: #FFE8A3; color: #29252A;">' . $stock . ' un.</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Top 5 Más Vendidos -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-header border-0 pt-4 pb-0 px-4" style="background: transparent;">
                    <h5 class="fw-bold" style="color: #29252A;"><i class="fas fa-trophy me-2" style="color: #E6C24D;"></i>Top 5 Más Vendidos</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (mysqli_num_rows($resTop5) == 0): ?>
                        <div class="text-center py-4" style="color: #4a454a;">
                            <i class="fas fa-inbox fa-2x mb-2"></i>
                            <p class="m-0">Sin ventas registradas aún</p>
                        </div>
                    <?php else: ?>
                        <?php $pos = 1; while ($fila = mysqli_fetch_assoc($resTop5)): ?>
                        <div class="d-flex justify-content-between align-items-center py-3" style="<?= ($pos < mysqli_num_rows($resTop5)) ? 'border-bottom: 1px solid #F8BBD0;' : '' ?>">
                            <div class="d-flex align-items-center">
                                <span class="badge rounded-circle me-3" style="min-width:28px; background: #29252A; color: #fff;"><?= $pos ?></span>
                                <span class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['nombre']) ?></span>
                            </div>
                            <span style="color: #4a454a;"><?= fmt_num($fila['total_vendidos']) ?> u.</span>
                        </div>
                        <?php $pos++; endwhile; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════
         SECCIÓN 4: ANÁLISIS
         ═══════════════════════════════════════ -->
    <div class="row g-4 mb-4">

        <!-- Clientes Frecuentes -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-header border-0 pt-4 pb-0 px-4" style="background: transparent;">
                    <h5 class="fw-bold" style="color: #29252A;"><i class="fas fa-heart me-2" style="color: #F48FB1;"></i>Clientes Frecuentes</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (mysqli_num_rows($resClientes) == 0): ?>
                        <div class="text-center py-4" style="color: #4a454a;">
                            <i class="fas fa-users fa-2x mb-2"></i>
                            <p class="m-0">Sin ventas registradas aún</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead>
                                    <tr style="background: #FFF9F5;">
                                        <th style="color: #29252A;">#</th>
                                        <th style="color: #29252A;">Cliente</th>
                                        <th class="text-center" style="color: #29252A;">Compras</th>
                                        <th class="text-end" style="color: #29252A;">Total Gastado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php $pos = 1; while ($fila = mysqli_fetch_assoc($resClientes)): ?>
                                    <tr>
                                        <td><span class="badge rounded-circle" style="background: #29252A; color: #fff;"><?= $pos ?></span></td>
                                        <td class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['cliente']) ?></td>
                                        <td class="text-center" style="color: #4a454a;"><?= $fila['compras'] ?></td>
                                        <td class="text-end fw-bold" style="color: #4CAF50;"><?= fmt_money($fila['total_gastado']) ?></td>
                                    </tr>
                                    <?php $pos++; endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Ventas por Categoría -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100" style="background: #FCE4EC;">
                <div class="card-header border-0 pt-4 pb-0 px-4" style="background: transparent;">
                    <h5 class="fw-bold" style="color: #29252A;"><i class="fas fa-tags me-2" style="color: #C5B4E3;"></i>Ventas por Categoría</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <?php if (mysqli_num_rows($resCategorias) == 0): ?>
                        <div class="text-center text-muted py-4">
                            <i class="fas fa-layer-group fa-2x mb-2"></i>
                            <p class="m-0">Sin ventas registradas aún</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Categoría</th>
                                        <th class="text-center">Ítems Vendidos</th>
                                        <th class="text-end">Facturación</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php
                                    $totalGeneral = 0;
                                    while ($fila = mysqli_fetch_assoc($resCategorias)) {
                                        $totalGeneral += $fila['total'];
                                    }
                                    $resCategorias2 = mysqli_query($cnn, "SELECT f.familia, COUNT(vd.id_ventas_detalles) AS items_vendidos, SUM(vd.monto_total) AS total
                                        FROM ventas_detalles vd
                                        INNER JOIN productos p ON vd.id_producto = p.id_producto
                                        INNER JOIN familias f ON p.id_familia = f.id_familia
                                        WHERE vd.deleted=0
                                        GROUP BY f.familia
                                        ORDER BY total DESC");
                                    while ($fila = mysqli_fetch_assoc($resCategorias2)):
                                        $pct = $totalGeneral > 0 ? ($fila['total'] / $totalGeneral) * 100 : 0;
                                    ?>
                                    <tr>
                                        <td class="fw-bold" style="color: #29252A;"><?= htmlspecialchars($fila['familia']) ?></td>
                                        <td class="text-center" style="color: #4a454a;"><?= $fila['items_vendidos'] ?></td>
                                        <td class="text-end">
                                            <div class="fw-bold" style="color: #29252A;"><?= fmt_money($fila['total']) ?></div>
                                            <div class="progress mt-1" style="height: 4px; background: #F8BBD0;">
                                                <div class="progress-bar" style="width: <?= number_format($pct, 0) ?>%; background: #F48FB1;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

</div>

<style>
.card-hover { transition: transform .15s ease-in-out, box-shadow .15s ease-in-out; }
.card-hover:hover { transform: translateY(-3px); box-shadow: 0 8px 20px #C5B4E3 !important; }
</style>
