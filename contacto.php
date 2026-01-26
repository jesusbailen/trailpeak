<?php require_once __DIR__ . '/includes/header.php'; ?>

<section class="py-5 bg-light">
  <div class="container">
    <div class="card shadow-sm border-0 rounded-4 mb-4">
      <div class="card-body p-4 p-lg-5">
        <h1 class="fw-bold mb-3">Contacto</h1>
        <p class="text-muted mb-0">
          ¿Tienes dudas sobre tallas, envíos o productos? Escríbenos y te responderemos lo antes posible.
        </p>
      </div>
    </div>

    <div class="row g-4">
      <div class="col-lg-7">
        <div class="card shadow-sm border-0 rounded-4">
          <div class="card-body p-4">
            <form>
              <div class="mb-3">
                <label class="form-label">Nombre</label>
                <input type="text" class="form-control" placeholder="Tu nombre" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Email</label>
                <input type="email" class="form-control" placeholder="tu@email.com" required>
              </div>
              <div class="mb-3">
                <label class="form-label">Mensaje</label>
                <textarea class="form-control" rows="5" placeholder="Cuéntanos en qué podemos ayudarte..." required></textarea>
              </div>
              <button class="btn btn-dark">Enviar</button>
            </form>
          </div>
        </div>
      </div>
      <div class="col-lg-5">
        <div class="card shadow-sm border-0 rounded-4 h-100">
          <div class="card-body p-4">
            <h2 class="h6 fw-bold mb-2">Atención al cliente</h2>
            <p class="text-muted small mb-3">
              Lunes a viernes de 9:00 a 18:00.
            </p>
            <div class="text-muted small">
              <div><strong>Email:</strong> soporte@trailpeak.com</div>
              <div><strong>Teléfono:</strong> +34 900 123 456</div>
              <div><strong>Ubicación:</strong> Elche, España</div>
            </div>
            <hr class="my-3">
            <p class="text-muted small mb-0">
              Si tu consulta es sobre un pedido, indica el número de pedido para agilizar la respuesta.
            </p>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
