<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm border-0 rounded-4 mb-5">
      <div class="card-body p-4 p-lg-5">
        <div class="row align-items-center g-4">
          <div class="col-lg-6">
            <h1 class="fw-bold mb-3">Quiénes somos</h1>
            <p class="text-muted mb-3">
              TrailPeak nace en la montaña para quienes buscan rendimiento real. Seleccionamos
              equipamiento técnico con criterio de uso: ligereza, durabilidad y comodidad cuando
              el terreno exige más.
            </p>
            <p class="text-muted mb-4">
              Probamos, comparamos y filtramos para que elijas con confianza. Menos ruido,
              más calidad y asesoramiento claro.
            </p>
            <div class="d-flex flex-wrap gap-2">
              <a class="btn btn-dark" href="<?= BASE_URL ?>index.php#ropa">Ver productos</a>
              <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>index.php#contacto">Contacto</a>
            </div>
          </div>
          <div class="col-lg-6">
            <img
              src="<?= BASE_URL ?>img/ui/equipo.png"
              class="img-fluid rounded-4 shadow-sm"
              alt="Equipo TrailPeak"
            >
          </div>
        </div>
      </div>
    </div>

    <div class="row g-4 mb-5">
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 rounded-4">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-activity fs-4 text-dark"></i>
              <h2 class="h6 fw-bold mb-0">Selección técnica</h2>
            </div>
            <p class="text-muted small mb-0">
              Producto probado en condiciones reales para asegurar fiabilidad y rendimiento.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 rounded-4">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-compass fs-4 text-dark"></i>
              <h2 class="h6 fw-bold mb-0">Enfoque trail</h2>
            </div>
            <p class="text-muted small mb-0">
              Solo material de trail running: ropa técnica, calzado y accesorios clave.
            </p>
          </div>
        </div>
      </div>
      <div class="col-md-4">
        <div class="card h-100 shadow-sm border-0 rounded-4">
          <div class="card-body">
            <div class="d-flex align-items-center gap-2 mb-2">
              <i class="bi bi-people fs-4 text-dark"></i>
              <h2 class="h6 fw-bold mb-0">Equipo multidisciplinar</h2>
            </div>
            <p class="text-muted small mb-0">
              Diseño, tecnología y deporte unidos para una experiencia clara y cercana.
            </p>
          </div>
        </div>
      </div>
    </div>

    <div class="card shadow-sm border-0 rounded-4">
      <div class="card-body p-4 p-lg-5">
        <div class="row g-4">
          <div class="col-lg-8">
            <h2 class="h5 fw-bold mb-3">Nuestra misión</h2>
            <p class="text-muted mb-0">
              Acompañar a cada corredor con equipamiento fiable para entrenar y competir con
              seguridad. En TrailPeak creemos en la simplicidad: menos ruido, más valor real.
            </p>
          </div>
          <div class="col-lg-4">
            <div class="bg-dark text-white rounded-4 p-4 h-100 d-flex flex-column justify-content-center">
              <div class="fw-bold">+8 años</div>
              <div class="small text-white-50">Seleccionando material trail</div>
              <hr class="border-secondary my-3">
              <div class="fw-bold">100% enfoque</div>
              <div class="small text-white-50">Trail running</div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
