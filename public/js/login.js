document.addEventListener('DOMContentLoaded', () => {
  // Función reutilizable para alternar visibilidad
  function setupToggle(toggleId, inputId) {
    const toggle = document.getElementById(toggleId);
    const input = document.getElementById(inputId);
    if (!toggle || !input) return;

    toggle.addEventListener('click', function () {
      const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
      input.setAttribute('type', type);
      this.innerHTML = type === 'password'
        ? '<i class="bi bi-eye"></i>'
        : '<i class="bi bi-eye-slash"></i>';
    });
  }

  // Para el campo principal de contraseña
  setupToggle('togglePassword', 'password');

  // Para el campo de confirmación
  setupToggle('toggleConfirmPassword', 'confirmPassword');
});
