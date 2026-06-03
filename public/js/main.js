// ── Password strength meter ─────────────────────────────────────────────────

function getStrength(pw) {
  const checks = [
    pw.length >= 8,
    /[A-Z]/.test(pw),
    /[a-z]/.test(pw),
    /[0-9]/.test(pw),
    /[^A-Za-z0-9]/.test(pw),
  ];
  const score = checks.filter(Boolean).length;
  const map = ['', 'weak', 'fair', 'good', 'strong', 'strong'];
  const labels = ['', 'Weak', 'Fair', 'Good', 'Strong', 'Strong'];
  return { score, cls: map[score], label: labels[score] };
}

const pwInput      = document.getElementById('password');
const strengthFill = document.getElementById('strength-fill');
const strengthText = document.getElementById('strength-text');

if (pwInput && strengthFill && strengthText) {
  pwInput.addEventListener('input', function () {
    const { cls, label } = getStrength(this.value);
    strengthFill.className = 'strength-fill' + (this.value ? ' ' + cls : '');
    strengthText.textContent = this.value ? label : '';
    strengthText.className = 'strength-text' + (this.value ? ' ' + cls : '');
  });
}

// ── Toggle password visibility ──────────────────────────────────────────────

document.querySelectorAll('.toggle-password').forEach(btn => {
  btn.addEventListener('click', function () {
    const input  = this.closest('.input-wrapper').querySelector('.form-input');
    const open   = this.querySelector('.eye-open');
    const closed = this.querySelector('.eye-closed');
    const isHidden = input.type === 'password';
    input.type = isHidden ? 'text' : 'password';
    if (open)   open.style.display   = isHidden ? 'none'  : '';
    if (closed) closed.style.display = isHidden ? ''      : 'none';
  });
});

// ── Auto-dismiss flash alerts ───────────────────────────────────────────────

document.querySelectorAll('.alert').forEach(alert => {
  setTimeout(() => {
    alert.style.transition = 'opacity .5s ease, transform .5s ease';
    alert.style.opacity    = '0';
    alert.style.transform  = 'translateY(-4px)';
    setTimeout(() => alert.remove(), 500);
  }, 5000);
});

// ── Submit button loading state ─────────────────────────────────────────────

document.querySelectorAll('form').forEach(form => {
  form.addEventListener('submit', function () {
    const btn = this.querySelector('button[type="submit"]');
    if (!btn) return;
    btn.disabled = true;
    const spinner = `<svg class="spin" xmlns="http://www.w3.org/2000/svg" width="16" height="16"
        viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
        <path d="M21 12a9 9 0 1 1-6.219-8.56"/></svg>`;
    btn.innerHTML = spinner + ' Please wait…';
    setTimeout(() => { btn.disabled = false; }, 12000);
  });
});
