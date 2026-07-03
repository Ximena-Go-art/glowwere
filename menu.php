<nav class="navbar navbar-expand-lg bg-body-tertiary">
      <div class="container-fluid">
          <a class="navbar-brand" href="index.php?seccion=inicio&accion=mostrar">Mi Gestion de Ventas</a>

          <button
              class="navbar-toggler"
              type="button"
              data-bs-toggle="collapse"
              data-bs-target="#navbarSupportedContent"
              aria-controls="navbarSupportedContent"
              aria-expanded="false"
              aria-label="Toggle navigation">
              <span class="navbar-toggler-icon"></span>
          </button>
          <div class="collapse navbar-collapse" id="navbarSupportedContent">
              <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                  <li class="nav-item">
                      <a class="nav-link active" aria-current="page" href="index.php?seccion=inicio&accion=mostrar">Inicio</a>
                    </li>
                  <li class="nav-item dropdown">
                      <a
                          class="nav-link dropdown-toggle"
                          href="#"
                          role="button"
                          data-bs-toggle="dropdown"
                          aria-expanded="false">
                          Listados
                      </a>
   
                      <ul class="dropdown-menu">


                          <li><a class="dropdown-item" href="index.php?seccion=familias&accion=listar">Familias Listar</a></li>
                          <li><a class="dropdown-item" href="index.php?seccion=familias&accion=modificar">Familias Modificar</a></li>

                        
                            <li><a class="dropdown-item"href="index.php?seccion=formas_pago&accion=listar">Formas de Pago Listar</a></li>  
                            <li><a class="dropdown-item"href="index.php?seccion=formas_pago&accion=modificar">Formas de Pago Modificar</a></li>
                            
                            <li><a class="dropdown-item" href="index.php?seccion=tipos_documentos&accion=listar">Tipos de Documentos Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=tipos_documentos&accion=modificar">Tipos de Documentos Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=roles&accion=listar">Roles Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=roles&accion=modificar">Roles Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=clientes&accion=listar">Clientes Listar</a></li>
                            <li><a class="dropdown-item" href="index.php?seccion=clientes&accion=modificar">Clientes Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=proveedores&accion=listar">Proveedores Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=proveedores&accion=modificar">Proveedores Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=compras&accion=listar">Compras Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=compras&accion=modificar">Compras Modificar</a></li>

                             <li><a class="dropdown-item"href="index.php?seccion=usuarios&accion=listar">Usuarios Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=usuarios&accion=modificar">Usuarios Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=productos&accion=listar">Productos Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=productos&accion=modificar">Productos Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=compra_detalles&accion=listar">Compras Detalles Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=compra_detalles&accion=modificar">Compras Detalles Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=ventas_detalles&accion=listar">Ventas Detalles Listar</a></li>
                            <li><a class="dropdown-item"href="index.php?seccion=ventas_detalles&accion=modificar">Ventas Detalles Modificar</a></li>

                            <li><a class="dropdown-item"href="index.php?seccion=cajas&accion=listar">Cajas Listar</a></li>


                            

                      </ul>
                  </li>

                  <li class="nav-item">
                      <a class="nav-link disabled" aria-disabled="true">Usuarios</a>
                  </li>
              </ul>
              <form class="d-flex" role="search">
                  <input
                      class="form-control me-2"
                      type="search"
                      placeholder="Search"
                      aria-label="Search" />
                  <button class="btn btn-outline-success" type="submit">
                      Buscar
                  </button>

              </form>
          </div>
      </div>
  </nav>