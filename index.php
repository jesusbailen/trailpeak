<?php require_once __DIR__ . '/includes/header.php'; ?>
<?php
if (!empty($_SESSION['flash_success'])): ?>
  <div class="alert alert-success text-center m-0 rounded-0">
    <?= htmlspecialchars($_SESSION['flash_success']) ?>
  </div>
<?php
  unset($_SESSION['flash_success']);
endif;
?>

<?php
if (!empty($_SESSION['flash_warning'])): ?>
  <div class="alert alert-warning text-center m-0 rounded-0">
    <?= htmlspecialchars($_SESSION['flash_warning']) ?>
  </div>
<?php
  unset($_SESSION['flash_warning']);
endif;
?>



<?php require_once __DIR__ . '/config/db.php'; ?>



<?php

// Debug según entorno
if (defined('ENV') && ENV === 'local') {
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
    error_reporting(E_ALL);
} else {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ALL);
    ini_set('log_errors', 1);
}


/**
 * Genera un slug a partir de un texto (Ropa técnica -> ropa-tecnica)
 */
function slugify(string $text): string {
  $text = trim($text);
  $text = mb_strtolower($text, 'UTF-8');

  // quitar acentos
  $map = [
    'á'=>'a','à'=>'a','ä'=>'a','â'=>'a',
    'é'=>'e','è'=>'e','ë'=>'e','ê'=>'e',
    'í'=>'i','ì'=>'i','ï'=>'i','î'=>'i',
    'ó'=>'o','ò'=>'o','ö'=>'o','ô'=>'o',
    'ú'=>'u','ù'=>'u','ü'=>'u','û'=>'u',
    'ñ'=>'n','ç'=>'c'
  ];
  $text = strtr($text, $map);

  // dejar solo letras/números/espacios/guiones
  $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
  $text = preg_replace('/[\s-]+/', '-', $text);
  return trim($text, '-');
}

// 1) CATEGORÍAS (tu tabla: categoria)
$categoriasRaw = $pdo->query("
  SELECT id_categoria, nombre, id_padre
  FROM categoria
  WHERE activo = 1
  ORDER BY id_padre, nombre
")->fetchAll(PDO::FETCH_ASSOC);

// añadimos slug calculado
$categorias = [];
$categoriasPrincipales = [];
$categoriasPrincipalesNoOfertas = [];
$subcategorias = [];
foreach ($categoriasRaw as $c) {
  $c['slug'] = slugify($c['nombre']);
  $categorias[] = $c;
  if (!empty($c['id_padre'])) {
    $subcategorias[(int)$c['id_padre']][] = $c;
  } else {
    $categoriasPrincipales[] = $c;
    if ($c['slug'] !== 'ofertas') {
      $categoriasPrincipalesNoOfertas[] = $c;
    }
  }
}

// Filtros de búsqueda (GET)
$q = trim($_GET['q'] ?? '');
$categoriaId = (int)($_GET['categoria'] ?? 0);
$order = $_GET['order'] ?? '';
$precioMin = $_GET['precio_min'] ?? '';
$precioMax = $_GET['precio_max'] ?? '';
$page = max(1, (int)($_GET['page'] ?? 1));
$hasFilters = ($q !== '' || $categoriaId > 0 || $precioMin !== '' || $precioMax !== '');

// Paginacion por categorias (mostrar 1 categoria por pagina)
$useCategoryPaging = ($categoriaId === 0 && !$hasFilters);
$totalPages = max(1, count($categoriasPrincipalesNoOfertas));
if ($page > $totalPages) {
  $page = $totalPages;
}
$categoriaIdQuery = $categoriaId;
if ($useCategoryPaging && $categoriasPrincipalesNoOfertas) {
  $categoriaIdQuery = (int)$categoriasPrincipalesNoOfertas[$page - 1]['id_categoria'];
}

// 2) PRODUCTOS (tu tabla: producto)
$where = ['p.activo = 1', 'c.activo = 1'];
$params = [];

if ($q !== '') {
  $where[] = '(p.nombre LIKE ? OR p.descripcion LIKE ? OR p.sku LIKE ?)';
  $like = '%' . $q . '%';
  $params[] = $like;
  $params[] = $like;
  $params[] = $like;
}

if ($categoriaIdQuery > 0) {
  $where[] = 'p.id_categoria = ?';
  $params[] = $categoriaIdQuery;
}
if ($precioMin !== '' && is_numeric($precioMin)) {
  $where[] = 'p.precio >= ?';
  $params[] = (float)$precioMin;
}
if ($precioMax !== '' && is_numeric($precioMax)) {
  $where[] = 'p.precio <= ?';
  $params[] = (float)$precioMax;
}

$orderSql = 'c.nombre, p.nombre';
if ($order === 'nombre_desc') {
  $orderSql = 'p.nombre DESC';
} elseif ($order === 'precio_asc') {
  $orderSql = 'p.precio ASC';
} elseif ($order === 'precio_desc') {
  $orderSql = 'p.precio DESC';
} elseif ($order === 'nombre_asc') {
  $orderSql = 'p.nombre ASC';
}

$sqlCount = "
  SELECT COUNT(*)
  FROM producto p
  JOIN categoria c ON c.id_categoria = p.id_categoria
  WHERE " . implode(' AND ', $where) . "
";
$stmt = $pdo->prepare($sqlCount);
$stmt->execute($params);
$totalProductos = (int)$stmt->fetchColumn();

$sql = "
  SELECT
    p.id_producto AS id,
    p.sku,
    p.nombre,
    p.descripcion,
    p.precio,
    p.stock,
    p.id_categoria,
    p.imagen_path,
    c.nombre AS categoria_nombre
  FROM producto p
  JOIN categoria c ON c.id_categoria = p.id_categoria
  WHERE " . implode(' AND ', $where) . "
  ORDER BY $orderSql
";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// agrupamos por slug de categoría
$productosPorCategoria = [];
foreach ($productos as $p) {
  $slugCat = slugify($p['categoria_nombre']);
  $productosPorCategoria[$slugCat][] = $p;
}

$ofertasNombres = ['Gorra', 'Cortavientos', 'Guantes'];
$ofertasSlugs = array_map('slugify', $ofertasNombres);
$ofertasProductos = [];
if ($ofertasNombres) {
  $placeholders = implode(',', array_fill(0, count($ofertasNombres), '?'));
  $stmtOfertas = $pdo->prepare("
    SELECT id_producto AS id, nombre, precio, stock, imagen_path
    FROM producto
    WHERE activo = 1 AND nombre IN ($placeholders)
  ");
  $stmtOfertas->execute($ofertasNombres);
  $rows = $stmtOfertas->fetchAll(PDO::FETCH_ASSOC);
  foreach ($rows as $row) {
    $ofertasProductos[slugify($row['nombre'])] = $row;
  }
}
$ofertasRenderizadas = false;

$imagenesPorSlug = [
  'mochila-5l' => 'img/productos/mochila5L.png',
  'bolsa-hidratacion' => 'img/productos/bolsahidratacion.png',
  'bolsa-de-hidratacion' => 'img/productos/bolsahidratacion.png',
  'chaleco' => 'img/productos/chaleco.png',
  'short-tecnico' => 'img/productos/short.png',
  'camiseta-trail' => 'img/productos/camiseta.png',
  'zapatilla-mountain-pro' => 'img/productos/zapatillas.png',
  'zapatillas-speed-trail-pro' => 'img/productos/zapatillas2.png',
];
?>



    <!-- HERO -->
    <section class="hero-section hero-clean text-white d-flex align-items-center">
      <div class="hero-overlay"></div>
      <div class="container text-center hero-content">
        <div class="mx-auto" style="max-width: 760px;">
          <p class="text-uppercase small opacity-75 mb-2">TrailPeak Store</p>
          <h1 class="fw-bold display-6 mb-3">Equipamiento diseñado para rendir en montaña</h1>
          <p class="lead mb-4">Ropa y material técnico para trail running. Rendimiento, ligereza y fiabilidad.</p>
          <a href="#catalogo" class="btn btn-light btn-lg">Ver catálogo</a>
        </div>
      </div>
    </section>

    <!-- FILTER BAR -->
    <section class="container filter-bar">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body">
          <form class="row g-3 align-items-end" method="get" action="index.php">
            <div class="col-12 col-lg-2">
              <label class="form-label small">Búsqueda</label>
              <input
                type="text"
                name="q"
                class="form-control"
                placeholder="Nombre, descripción o SKU"
                value="<?= htmlspecialchars($q) ?>"
              />
            </div>
            <div class="col-12 col-lg-2">
              <label class="form-label small">Categoría</label>
              <select name="categoria" class="form-select">
                <option value="">Todas</option>
                <?php foreach ($categorias as $c): ?>
                  <?php $label = !empty($c['id_padre']) ? '— ' . $c['nombre'] : $c['nombre']; ?>
                  <option value="<?= (int)$c['id_categoria'] ?>" <?= $categoriaId === (int)$c['id_categoria'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars($label) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </div>
            <div class="col-6 col-lg-2">
              <label class="form-label small">Precio min</label>
              <input
                type="number"
                name="precio_min"
                class="form-control"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars((string)$precioMin) ?>"
              />
            </div>
            <div class="col-6 col-lg-2">
              <label class="form-label small">Precio max</label>
              <input
                type="number"
                name="precio_max"
                class="form-control"
                min="0"
                step="0.01"
                value="<?= htmlspecialchars((string)$precioMax) ?>"
              />
            </div>
            <div class="col-12 col-lg-2">
              <label class="form-label small">Orden</label>
              <select name="order" class="form-select">
                <option value="nombre_asc" <?= ($order === '' || $order === 'nombre_asc') ? 'selected' : '' ?>>Nombre (A-Z)</option>
                <option value="nombre_desc" <?= $order === 'nombre_desc' ? 'selected' : '' ?>>Nombre (Z-A)</option>
                <option value="precio_asc" <?= $order === 'precio_asc' ? 'selected' : '' ?>>Precio (menor)</option>
                <option value="precio_desc" <?= $order === 'precio_desc' ? 'selected' : '' ?>>Precio (mayor)</option>
              </select>
            </div>
            <div class="col-12 col-lg-2 d-flex gap-2">
              <button class="btn btn-dark w-100" type="submit">Filtrar</button>
              <a class="btn btn-outline-secondary w-100" href="index.php">Limpiar</a>
            </div>
          </form>
        </div>
      </div>
    </section>

    <div class="container my-4">
      <hr class="tp-divider">
    </div>

    <!-- PRODUCT GRID -->
    <section id="catalogo" class="py-5">
      <div class="container">
        <div class="card shadow-sm border-0 rounded-4 mb-4 catalogo-banner">
          <div class="card-body d-flex justify-content-between align-items-center">
            <h2 class="h5 fw-bold mb-0">Catálogo</h2>
            <span class="text-muted small">Mostrando <?= count($productos) ?> de <?= $totalProductos ?> productos</span>
          </div>
        </div>
        <div class="row g-4">
          <!-- MENÚ LATERAL IZQUIERDO (Categorías/Subcategorías) -->
          <aside class="col-lg-3">
            <div class="card shadow-sm sticky-lg-top sidebar-sticky border-0 rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Categorías</h5>
                <div class="d-flex flex-wrap gap-2 mb-3">
                  <a class="btn btn-outline-dark btn-sm category-chip" href="index.php">Todas</a>
                  <?php foreach ($categoriasPrincipales as $c): ?>
                    <a class="btn btn-outline-dark btn-sm category-chip" href="index.php?categoria=<?= (int)$c['id_categoria'] ?>">
                      <?= htmlspecialchars($c['nombre']) ?>
                    </a>
                  <?php endforeach; ?>
                </div>

                <h6 class="text-muted mb-2">Secciones</h6>
                <div class="d-grid gap-2">
                  <a href="#ofertas" class="btn btn-outline-dark btn-sm">Ver ofertas</a>
                  <a href="#catalogo" class="btn btn-outline-dark btn-sm">Ver catálogo</a>
                </div>
              </div>
            </div>
          </aside>

          <main class="col-lg-6">
            <div class="card shadow-sm mb-4 border-0 rounded-4">
              <div class="card-body">
                <div class="row text-center g-4">
                  <div class="col-md-4">
                    <h6 class="fw-bold mb-1">Diseño técnico</h6>
                    <p class="text-muted small mb-0">Productos pensados para el esfuerzo real en montaña.</p>
                  </div>
                  <div class="col-md-4">
                    <h6 class="fw-bold mb-1">Materiales de calidad</h6>
                    <p class="text-muted small mb-0">Tejidos duraderos, ligeros y probados.</p>
                  </div>
                  <div class="col-md-4">
                    <h6 class="fw-bold mb-1">Especialistas</h6>
                    <p class="text-muted small mb-0">No vendemos de todo. Solo trail.</p>
                  </div>
                </div>
              </div>
            </div>

            <div class="my-4">
              <hr class="tp-divider">
            </div>

           <?php if (!$ofertasRenderizadas && !$hasFilters): ?>
              <!-- OFERTAS -->
              <section id="ofertas" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h2 class="h4 mb-0">Ofertas</h2>
                  <span class="badge text-bg-light"><?= count($ofertasNombres) ?> productos</span>
                </div>

                <div class="row g-4">
                  <?php foreach ($ofertasNombres as $nombreOferta): ?>
                    <?php
                      $imgOferta = '';
                      if (slugify($nombreOferta) === 'gorra') $imgOferta = 'img/ofertas/gorra.png';
                      if (slugify($nombreOferta) === 'cortavientos') $imgOferta = 'img/ofertas/cortavientos.png';
                      if (slugify($nombreOferta) === 'guantes') $imgOferta = 'img/ofertas/guantes.png';

                      $prodOferta = $ofertasProductos[slugify($nombreOferta)] ?? null;
                      if ($prodOferta && !empty($prodOferta['imagen_path'])) {
                        $imgOferta = $prodOferta['imagen_path'];
                      }
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                      <div class="card h-100 shadow-sm product-card border-0 rounded-4 card-gradient">
                        <?php if ($imgOferta !== ''): ?>
                          <img
                            src="<?= BASE_URL . $imgOferta ?>"
                            class="card-img-top product-img"
                            alt="<?= htmlspecialchars($nombreOferta) ?>"
                            width="400"
                            height="400"
                          >
                        <?php else: ?>
                          <div class="product-placeholder">
                            <i class="bi bi-image fs-1"></i>
                          </div>
                        <?php endif; ?>
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($nombreOferta) ?></h5>
                          </div>
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                              <?php
                                $precioActual = $prodOferta ? (float)$prodOferta['precio'] : null;
                                $mostrarAnterior = in_array($nombreOferta, ['Cortavientos', 'Guantes'], true);
                                $precioAnterior = $precioActual !== null ? $precioActual + 15 : null;
                              ?>
                              <?php if ($precioActual !== null && $mostrarAnterior): ?>
                                <span class="text-muted small text-decoration-line-through me-2">
                                  <?= number_format($precioAnterior, 2, ',', '.') ?> &euro;
                                </span>
                              <?php endif; ?>
                              <?php if ($precioActual !== null): ?>
                                <span class="fw-bold fs-5"><?= number_format($precioActual, 2, ',', '.') ?> &euro;</span>
                              <?php else: ?>
                                <span class="fw-bold fs-5 text-muted">No disponible</span>
                              <?php endif; ?>
                            </div>
                            <span class="text-muted small">Stock: <?= $prodOferta ? (int)$prodOferta['stock'] : 0 ?></span>
                          </div>
                          <?php if ($prodOferta): ?>
                            <div class="d-flex gap-2 mt-3">
                              <a class="btn btn-outline-dark w-100 btn-center" href="<?= BASE_URL ?>producto.php?id=<?= (int)$prodOferta['id'] ?>">Ver m&aacute;s</a>
                              <form method="post" action="add_to_cart.php">
                                <input type="hidden" name="product_id" value="<?= (int)$prodOferta['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-icon" title="Anadir al carrito" aria-label="Anadir al carrito">
                                  <i class="bi bi-cart3"></i>
                                </button>
                              </form>
                            </div>
                          <?php else: ?>
                            <div class="d-flex gap-2 mt-3">
                              <button class="btn btn-outline-secondary w-100" disabled>No disponible</button>
                              <button class="btn btn-secondary btn-icon" disabled>
                                <i class="bi bi-cart3"></i>
                              </button>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </section>

              <div class="my-4">
                <hr class="tp-divider">
              </div>
              <?php $ofertasRenderizadas = true; ?>
            <?php endif; ?>

           <?php foreach ($categorias as $c): ?>
            <?php if ($c['slug'] === 'ofertas') continue; ?>
            <?php if ($useCategoryPaging && (int)$c['id_categoria'] !== (int)$categoriaIdQuery) continue; ?>
            <?php if (false && !$ofertasRenderizadas && $c['slug'] === 'accesorios'): ?>
              <!-- OFERTAS (como categoría) -->
              <section id="ofertas" class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3">
                  <h2 class="h4 mb-0">Ofertas</h2>
                  <span class="badge text-bg-light"><?= count($ofertasNombres) ?> productos</span>
                </div>

                <div class="row g-4">
                  <?php foreach ($ofertasNombres as $nombreOferta): ?>
                    <?php
                      $imgOferta = '';
                      if (slugify($nombreOferta) === 'gorra') $imgOferta = 'img/ofertas/gorra.png';
                      if (slugify($nombreOferta) === 'cortavientos') $imgOferta = 'img/ofertas/cortavientos.png';
                      if (slugify($nombreOferta) === 'guantes') $imgOferta = 'img/ofertas/guantes.png';

                      $prodOferta = $ofertasProductos[slugify($nombreOferta)] ?? null;
                      if ($prodOferta && !empty($prodOferta['imagen_path'])) {
                        $imgOferta = $prodOferta['imagen_path'];
                      }
                    ?>
                    <div class="col-12 col-md-6 col-lg-4">
                      <div class="card h-100 shadow-sm product-card border-0 rounded-4 card-gradient">
                        <?php if ($imgOferta !== ''): ?>
                          <img
                            src="<?= BASE_URL . $imgOferta ?>"
                            class="card-img-top product-img"
                            alt="<?= htmlspecialchars($nombreOferta) ?>"
                            width="400"
                            height="400"
                          >
                        <?php else: ?>
                          <div class="product-placeholder">
                            <i class="bi bi-image fs-1"></i>
                          </div>
                        <?php endif; ?>
                        <div class="card-body">
                          <div class="d-flex justify-content-between align-items-start mb-2">
                            <h5 class="card-title mb-0"><?= htmlspecialchars($nombreOferta) ?></h5>
                            <span class="badge text-bg-light">Ofertas</span>
                          </div>
                          <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="d-flex align-items-baseline gap-2 flex-wrap">
                              <?php
                                $precioActual = $prodOferta ? (float)$prodOferta['precio'] : null;
                                $mostrarAnterior = in_array($nombreOferta, ['Cortavientos', 'Guantes'], true);
                                $precioAnterior = $precioActual !== null ? $precioActual + 15 : null;
                              ?>
                              <?php if ($precioActual !== null && $mostrarAnterior): ?>
                                <span class="text-muted small text-decoration-line-through me-2">
                                  <?= number_format($precioAnterior, 2, ',', '.') ?> €
                                </span>
                              <?php endif; ?>
                              <?php if ($precioActual !== null): ?>
                                <span class="fw-bold fs-5"><?= number_format($precioActual, 2, ',', '.') ?> €</span>
                              <?php else: ?>
                                <span class="fw-bold fs-5 text-muted">No disponible</span>
                              <?php endif; ?>
                            </div>
                            <span class="text-muted small">Stock: <?= $prodOferta ? (int)$prodOferta['stock'] : 0 ?></span>
                          </div>
                          <?php if ($prodOferta): ?>
                            <div class="d-flex gap-2 mt-3">
                              <a class="btn btn-outline-dark w-100 btn-center" href="<?= BASE_URL ?>producto.php?id=<?= (int)$prodOferta['id'] ?>">Ver m&aacute;s</a>
                              <form method="post" action="add_to_cart.php">
                                <input type="hidden" name="product_id" value="<?= (int)$prodOferta['id'] ?>">
                                <button type="submit" class="btn btn-primary btn-icon" title="Anadir al carrito" aria-label="Anadir al carrito">
                                  <i class="bi bi-cart3"></i>
                                </button>
                              </form>
                            </div>
                          <?php else: ?>
                            <div class="d-flex gap-2 mt-3">
                              <button class="btn btn-outline-secondary w-100" disabled>No disponible</button>
                              <button class="btn btn-secondary btn-icon" disabled>
                                <i class="bi bi-cart3"></i>
                              </button>
                            </div>
                          <?php endif; ?>
                        </div>
                      </div>
                    </div>
                  <?php endforeach; ?>
                </div>
              </section>

              <div class="my-4">
                <hr class="tp-divider">
              </div>
              <?php $ofertasRenderizadas = true; ?>
            <?php endif; ?>

  <?php
    $productosCategoria = $productosPorCategoria[$c['slug']] ?? [];
    if ($c['slug'] === 'accesorios') {
      $productosCategoria = array_values(array_filter($productosCategoria, function ($prod) use ($ofertasSlugs) {
        return !in_array(slugify($prod['nombre']), $ofertasSlugs, true);
      }));
    }
    if (empty($productosCategoria)) {
      continue;
    }
  ?>
  <section id="<?= htmlspecialchars($c['slug']) ?>" class="mb-5">
    <div class="d-flex justify-content-between align-items-center mb-3">
      <h2 class="h4 mb-0"><?= htmlspecialchars($c['nombre']) ?></h2>
      <span class="badge text-bg-light"><?= count($productosCategoria) ?> productos</span>
    </div>

    <div class="row g-4">
      <?php if (!empty($productosCategoria)): ?>
        <?php foreach ($productosCategoria as $p): ?>
          <div class="col-12 col-md-6 col-lg-4">
            <div class="card h-100 shadow-sm product-card border-0 rounded-4 card-gradient">
              <?php
                $slugProd = slugify($p['nombre']);
                $imgProd = !empty($p['imagen_path']) ? $p['imagen_path'] : ($imagenesPorSlug[$slugProd] ?? null);
                if (!$imgProd) {
                  $nombreLower = mb_strtolower($p['nombre'] ?? '', 'UTF-8');
                  if (strpos($slugProd, 'mochila') !== false || strpos($nombreLower, 'mochila') !== false) {
                    $imgProd = 'img/productos/mochila5L.png';
                  } elseif (strpos($slugProd, 'bolsa-hidratacion') !== false || strpos($slugProd, 'bolsa-de-hidratacion') !== false || strpos($nombreLower, 'bolsa hidratacion') !== false || strpos($nombreLower, 'bolsa de hidratacion') !== false || strpos($nombreLower, 'bolsa de hidratación') !== false) {
                    $imgProd = 'img/productos/bolsahidratacion.png';
                  } elseif (strpos($slugProd, 'chaleco') !== false || strpos($nombreLower, 'chaleco') !== false) {
                    $imgProd = 'img/productos/chaleco.png';
                  } elseif (strpos($slugProd, 'short') !== false || strpos($nombreLower, 'short') !== false) {
                    $imgProd = 'img/productos/short.png';
                  } elseif (strpos($slugProd, 'camiseta') !== false || strpos($nombreLower, 'camiseta') !== false) {
                    $imgProd = 'img/productos/camiseta.png';
                  } elseif (strpos($slugProd, 'speed-trail-pro') !== false || strpos($nombreLower, 'speed trail pro') !== false) {
                    $imgProd = 'img/productos/zapatillas2.png';
                  } elseif (strpos($slugProd, 'zapatilla') !== false || strpos($nombreLower, 'zapatilla') !== false) {
                    $imgProd = 'img/productos/zapatillas.png';
                  }
                }
              ?>
              <?php if ($imgProd): ?>
                <img
                  src="<?= BASE_URL . $imgProd ?>"
                  class="card-img-top product-img"
                  alt="<?= htmlspecialchars($p['nombre']) ?>"
                  width="400"
                  height="400"
                >
              <?php else: ?>
                <div class="product-placeholder">
                  <i class="bi bi-image fs-1"></i>
                </div>
              <?php endif; ?>
              <div class="card-body">
                <div class="d-flex justify-content-between align-items-start mb-2">
                  <h5 class="card-title mb-0"><?= htmlspecialchars($p['nombre']) ?></h5>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                  <span class="fw-bold fs-5"><?= number_format((float)$p['precio'], 2, ',', '.') ?> €</span>
                  <span class="text-muted small">Stock: <?= (int)$p['stock'] ?></span>
                </div>
                <div class="d-flex gap-2 mt-3">
                  <a class="btn btn-outline-dark w-100 btn-center" href="<?= BASE_URL ?>producto.php?id=<?= (int)$p['id'] ?>">Ver m&aacute;s</a>
                  <form method="post" action="add_to_cart.php">
                    <input type="hidden" name="product_id" value="<?= (int)$p['id'] ?>">
                    <button type="submit" class="btn btn-primary btn-icon" title="Anadir al carrito" aria-label="Anadir al carrito">
                      <i class="bi bi-cart3"></i>
                    </button>
                  </form>
                </div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      <?php else: ?>
        <div class="col-12">
          <div class="alert alert-info border-0">No se encontraron productos en esta categoría.</div>
        </div>
      <?php endif; ?>
    </div>
  </section>
<?php endforeach; ?>

<?php if ($useCategoryPaging && $totalPages > 1): ?>
  <?php
    $baseParams = $_GET;
    unset($baseParams['page']);
  ?>
  <nav class="mt-4" aria-label="Paginacion">
    <ul class="pagination justify-content-center">
      <?php
        $prev = max(1, $page - 1);
        $next = min($totalPages, $page + 1);
        $prevParams = array_merge($baseParams, ['page' => $prev]);
        $nextParams = array_merge($baseParams, ['page' => $next]);
      ?>
      <li class="page-item <?= $page <= 1 ? 'disabled' : '' ?>">
        <a class="page-link" href="index.php?<?= htmlspecialchars(http_build_query($prevParams)) ?>">Anterior</a>
      </li>
      <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <?php $pageParams = array_merge($baseParams, ['page' => $i]); ?>
        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
          <a class="page-link" href="index.php?<?= htmlspecialchars(http_build_query($pageParams)) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>
      <li class="page-item <?= $page >= $totalPages ? 'disabled' : '' ?>">
        <a class="page-link" href="index.php?<?= htmlspecialchars(http_build_query($nextParams)) ?>">Siguiente</a>
      </li>
    </ul>
  </nav>
<?php endif; ?>


          </main>

          <!-- ZONA DERECHA (LOGIN/REGISTRO) -->
          <aside class="col-lg-3">
            <div id="login" class="card shadow-sm sticky-lg-top sidebar-sticky border-0 rounded-4">
              <div class="card-body">
                <h5 class="card-title mb-3">Acceso clientes</h5>

                
<?php if (!empty($_SESSION['user'])): ?>
  <?php $rol = $_SESSION['user']['rol'] ?? 'cliente'; ?>
  <div class="mb-3">
    <span class="text-muted small">Sesión iniciada como</span><br>
    <strong><?= htmlspecialchars($_SESSION['user']['nombre'] ?? $_SESSION['user']['email'] ?? 'Usuario') ?></strong>
    <div class="text-muted small">Rol: <?= htmlspecialchars($rol) ?></div>
  </div>

  <div class="d-grid gap-2">
    <?php if ($rol === 'cliente'): ?>
      <a class="btn btn-outline-primary btn-sm" href="<?= BASE_URL ?>mis_pedidos.php">Mis pedidos</a>
      <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>mis_datos.php">Mis datos</a>
    <?php else: ?>
      <a class="btn btn-outline-dark btn-sm" href="<?= BASE_URL ?>admin/index.php">Panel</a>
    <?php endif; ?>
    <a class="btn btn-outline-danger btn-sm" href="<?= BASE_URL ?>logout.php">Salir</a>
  </div>

<?php else: ?>

  <form class="mb-3" method="post" action="<?= BASE_URL ?>login.php">
    <div class="mb-2">
      <label class="form-label small">Email</label>
      <input
        type="email"
        name="email"
        class="form-control"
        placeholder="usuario@email.com"
        required
      />
    </div>

    <div class="mb-3">
      <label class="form-label small">Contraseña</label>
      <input
        type="password"
        name="password"
        class="form-control"
        placeholder="••••••••"
        required
      />
    </div>

    <!-- Para volver a esta zona tras login -->
    <input type="hidden" name="redirect" value="<?= BASE_URL ?>index.php#login">

    <button type="submit" class="btn btn-dark w-100 btn-sm">
      Entrar
    </button>
  </form>

  <div class="d-grid gap-2">
    <a class="btn btn-outline-dark w-100 btn-sm" href="<?= BASE_URL ?>register.php">
      Crear cuenta
    </a>
  </div>

<?php endif; ?>

              </div>
            </div>
          </aside>
        </div>
      </div>
    </section>


<?php require_once __DIR__ . '/includes/footer.php'; ?>





