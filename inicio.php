<div class="container-fluid py-4">

    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h2 class="fw-bold m-0">Hola, <?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado')?> 👋</h2>
            <p class="text-muted small m-0">Aquí tienes el resumen de tu negocio de cosmética de hoy.</p>
        </div>
        <div class="d-flex gap-2">
    <a href="login.php?logout=1" 
       class="btn btn-outline-danger btn-sm rounded-pill px-4 shadow-sm">
       <i class="fas fa-sign-out-alt me-2"></i> Cerrar sesión
    </a>
</div>
    </div>

    <div class="row g-3 mb-4">
        
        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted fw-bold text-uppercase m-0 small">Ventas Hoy</h6>
                        <div class="bg-success-subtle text-success rounded p-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-coins"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">$ 45,200</h3>
                    <p class="text-success small m-0"><i class="fas fa-arrow-up"></i> +12% vs ayer</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted fw-bold text-uppercase m-0 small">Órdenes Mes</h6>
                        <div class="bg-primary-subtle text-primary rounded p-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-shopping-bag"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">128</h3>
                    <p class="text-muted small m-0">Ventas completadas</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="text-muted fw-bold text-uppercase m-0 small">Clientes</h6>
                        <div class="bg-info-subtle text-info rounded p-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">342</h3>
                    <p class="text-muted small m-0">Activos en el sistema</p>
                </div>
            </div>
        </div>

        <div class="col-md-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-danger text-white">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-uppercase m-0 small text-white-50">Stock Bajo</h6>
                        <div class="bg-white text-danger rounded p-2 d-flex align-items-center justify-content-center">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                    </div>
                    <h3 class="fw-bold mb-1">5</h3>
                    <p class="text-white-50 small m-0">Productos por agotarse</p>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold">Últimas Ventas Realizadas</h5>
                </div>
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>ID</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Monto</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#0015</td>
                                    <td>María González</td>
                                    <td>Hoy, 10:30 AM</td>
                                    <td class="fw-bold">$ 3,500</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Pagado</span></td>
                                </tr>
                                <tr>
                                    <td>#0014</td>
                                    <td>Luciana Pérez</td>
                                    <td>Hoy, 09:15 AM</td>
                                    <td class="fw-bold">$ 8,200</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Pagado</span></td>
                                </tr>
                                <tr>
                                    <td>#0013</td>
                                    <td>Cliente Mostrador</td>
                                    <td>Ayer</td>
                                    <td class="fw-bold">$ 1,200</td>
                                    <td><span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">Pagado</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="text-end mt-2">
                        <a href="index.php?seccion=ventas&accion=listar" class="text-danger text-decoration-none small fw-bold">Ver todas las ventas <i class="fas fa-arrow-right"></i></a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold">Necesitan Reposición</h5>
                </div>
                <div class="card-body px-4">
                    
                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded p-2 me-3">💄</div>
                            <div>
                                <h6 class="mb-0 fw-bold small">Labial Mate Rojo Pasión</h6>
                                <p class="text-muted small m-0">Familia: Labios</p>
                            </div>
                        </div>
                        <span class="badge bg-danger rounded-pill">2 un.</span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-bottom py-3">
                        <div class="d-flex align-items-center">
                            <div class="bg-light rounded p-2 me-3">🧴</div>
                            <div>
                                <h6 class="mb-0 fw-bold small">Base Líquida Tono Medio</h6>
                                <p class="text-muted small m-0">Familia: Rostro</p>
                            </div>
                        </div>
                        <span class="badge bg-warning text-dark rounded-pill">4 un.</span>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php?seccion=productos&accion=listar" class="btn btn-outline-secondary rounded-pill btn-sm w-100">Gestionar Inventario</a>
                    </div>

                </div>
            </div>
        </div>

    </div>
</div>