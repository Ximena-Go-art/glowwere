<?php
/* ============================
   PAGINADOR REUTILIZABLE
============================ */

function paginar_consulta($cnn, $sql, $porPagina = 10) {

    $pagina = (isset($_GET['pagina']) && is_numeric($_GET['pagina']) && intval($_GET['pagina']) > 0)
        ? intval($_GET['pagina'])
        : 1;

    $totalRegistros = 0;

    $resTotal = mysqli_query($cnn, "SELECT COUNT(*) AS total FROM ($sql) AS tabla_paginado");

    if ($resTotal) {
        $filaTotal = mysqli_fetch_assoc($resTotal);
        $totalRegistros = intval($filaTotal['total']);
    }

    $totalPaginas = max(1, ceil($totalRegistros / $porPagina));

    if ($pagina > $totalPaginas) {
        $pagina = $totalPaginas;
    }

    $offset = ($pagina - 1) * $porPagina;

    return array(
        'data'         => mysqli_query($cnn, "$sql LIMIT $porPagina OFFSET $offset"),
        'total'        => $totalRegistros,
        'pagina'       => $pagina,
        'totalPaginas' => $totalPaginas,
        'porPagina'    => $porPagina,
        'desde'        => ($totalRegistros > 0) ? $offset + 1 : 0,
        'hasta'        => min($offset + $porPagina, $totalRegistros),
    );
}

function url_pagina($numeroPagina) {
    $params = $_GET;
    $params['pagina'] = $numeroPagina;
    return "index.php?" . http_build_query($params);
}

function render_paginador($pag) {

    static $estilosIncluidos = false;

    if (!$estilosIncluidos) {
        echo "<style>
        .paginador-app .page-link {
            border: none;
            color: #6c757d;
            font-weight: 600;
            border-radius: 50rem !important;
            margin: 0 2px;
            min-width: 30px;
            text-align: center;
            transition: all .15s ease-in-out;
        }
        .paginador-app .page-link:hover {
            background-color: #fdecef;
            color: #dc3545;
        }
        .paginador-app .page-item.active .page-link {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #fff;
            box-shadow: 0 3px 8px rgba(220, 53, 69, .35);
        }
        .paginador-app .page-item.disabled .page-link {
            color: #ced4da;
            background-color: transparent;
        }
        </style>";
        $estilosIncluidos = true;
    }

    echo '<div class="card-footer bg-white border-top d-flex flex-wrap justify-content-between align-items-center py-3 px-4 gap-2">';
    echo '<small class="text-muted">Mostrando <strong>' . $pag['desde'] . '</strong>&ndash;<strong>' . $pag['hasta'] . '</strong> de <strong>' . number_format($pag['total'], 0, ',', '.') . '</strong> registros</small>';

    $actual = $pag['pagina'];
    $ultima = $pag['totalPaginas'];

    if ($ultima > 1) {

        echo '<nav aria-label="Paginación"><ul class="pagination pagination-sm mb-0 paginador-app">';

        echo '<li class="page-item' . ($actual <= 1 ? ' disabled' : '') . '">';
        echo '<a class="page-link" href="' . url_pagina(max(1, $actual - 1)) . '" title="Anterior"><i class="fas fa-angle-left"></i></a></li>';

        for ($i = 1; $i <= $ultima; $i++) {
            if ($i == 1 || $i == $ultima || abs($i - $actual) <= 2) {
                echo '<li class="page-item' . ($i == $actual ? ' active' : '') . '">';
                echo '<a class="page-link" href="' . url_pagina($i) . '">' . $i . '</a></li>';
            } elseif ($i == $actual - 3 || $i == $actual + 3) {
                echo '<li class="page-item disabled"><span class="page-link">&hellip;</span></li>';
            }
        }

        echo '<li class="page-item' . ($actual >= $ultima ? ' disabled' : '') . '">';
        echo '<a class="page-link" href="' . url_pagina(min($ultima, $actual + 1)) . '" title="Siguiente"><i class="fas fa-angle-right"></i></a></li>';

        echo '</ul></nav>';
    }

    echo '</div>';
}
