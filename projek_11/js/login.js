// System Initialization & Theme Sync
document.addEventListener('DOMContentLoaded', () => {
  const savedTheme = localStorage.getItem('pb_theme');
  if (savedTheme === 'dark') {
    setDarkMode(true);
  }
});

// Theme Toggle Functionality
function toggleTheme() {
  const isDark = document.body.classList.toggle('dark-mode');
  localStorage.setItem('pb_theme', isDark ? 'dark' : 'light');
  updateThemeUI(isDark);
  showToast(isDark ? 'Mode Gelap diaktifkan 🌙' : 'Mode Terang diaktifkan ☀️');
}

function setDarkMode(isDark) {
  if (isDark) {
    document.body.classList.add('dark-mode');
  } else {
    document.body.classList.remove('dark-mode');
  }
  updateThemeUI(isDark);
}

function updateThemeUI(isDark) {
  const themeIcon = document.getElementById('themeIcon');
  const themeText = document.getElementById('themeText');
  if (themeIcon && themeText) {
    themeIcon.textContent = isDark ? '☀️' : '🌙';
    themeText.textContent = isDark ? 'Mode Terang' : 'Mode Gelap';
  }
}

// Switch between Login and Register Tabs
function switchTab(tab) {
  const loginForm = document.getElementById('loginForm');
  const registerForm = document.getElementById('registerForm');
  const tabLoginBtn = document.getElementById('tabLoginBtn');
  const tabRegisterBtn = document.getElementById('tabRegisterBtn');

  if (tab === 'login') {
    loginForm.style.display = 'block';
    registerForm.style.display = 'none';
    tabLoginBtn.classList.add('active');
    tabRegisterBtn.classList.remove('active');
  } else {
    loginForm.style.display = 'none';
    registerForm.style.display = 'block';
    tabLoginBtn.classList.remove('active');
    tabRegisterBtn.classList.add('active');
  }
}

// Toggle Password Visibility
function togglePasswordVisibility(inputId, btn) {
  const input = document.getElementById(inputId);
  if (input.type === 'password') {
    input.type = 'text';
    btn.textContent = '🙈';
  } else {
    input.type = 'password';
    btn.textContent = '👁️';
  }
}

// Fill Preset Demo Data
function fillPreset(role) {
  switchTab('login');
  if (role === 'admin') {
    document.getElementById('loginEmail').value = 'admin@sistemkeuangan.id';
    document.getElementById('loginPassword').value = 'admin123';
    document.getElementById('loginRole').value = 'Administrator';
    showToast('Demo data Administrator dimuat 👑');
  } else if (role === 'staff') {
    document.getElementById('loginEmail').value = 'staff@sistemkeuangan.id';
    document.getElementById('loginPassword').value = 'staff123';
    document.getElementById('loginRole').value = 'Staff Booth';
    showToast('Demo data Staff Event dimuat 📸');
  }
}

// Handle Login Form Submit
function handleLoginSubmit(e) {
  e.preventDefault();
  const email = document.getElementById('loginEmail').value.trim();
  const pass = document.getElementById('loginPassword').value;
  const btn = document.getElementById('loginSubmitBtn');

  if (!email || !pass) {
    showToast('Mohon isi email dan password!', 'error');
    return;
  }

  btn.disabled = true;
  btn.innerHTML = '<span>Memproses Masuk...</span> ⏳';

  const formData = new URLSearchParams();
  formData.append('email', email);
  formData.append('password', pass);

  fetch('api/login.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(`Selamat datang, ${data.user.name}! Mengalihkan ke Dashboard...`);
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 1200);
    } else {
      showToast(data.error || 'Login gagal', 'error');
      btn.disabled = false;
      btn.innerHTML = '<span>Masuk ke Dashboard</span> <span>➔</span>';
    }
  })
  .catch(err => {
    showToast('Terjadi kesalahan server', 'error');
    btn.disabled = false;
    btn.innerHTML = '<span>Masuk ke Dashboard</span> <span>➔</span>';
  });
}

// Handle Register Form Submit
function handleRegisterSubmit(e) {
  e.preventDefault();
  const name = document.getElementById('regName').value.trim();
  const boothName = document.getElementById('regBoothName').value.trim();
  const email = document.getElementById('regEmail').value.trim();
  const password = document.getElementById('regPassword').value;

  if (!name || !boothName || !email || !password) {
    showToast('Lengkapi seluruh data pendaftaran!');
    return;
  }

  const formData = new URLSearchParams();
  formData.append('name', name);
  formData.append('business_name', boothName);
  formData.append('email', email);
  formData.append('password', password);

  fetch('api/register.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      showToast(`Pendaftaran ${boothName} Berhasil! Mengalihkan...`);
      setTimeout(() => {
        window.location.href = 'index.php';
      }, 1200);
    } else {
      showToast(data.error || 'Pendaftaran gagal', 'error');
    }
  })
  .catch(err => {
    showToast('Terjadi kesalahan server', 'error');
  });
}

// Reset Password Modal Functions
function openResetModal() {
  document.getElementById('resetModal').classList.add('active');
}

function closeResetModal() {
  document.getElementById('resetModal').classList.remove('active');
}

function handleResetSubmit(e) {
  e.preventDefault();
  const email = document.getElementById('resetEmail').value.trim();
  closeResetModal();
  showToast(`Instruksi reset password dikirim ke ${email} 📩`);
}

// Toast Notification System
function showToast(message, type = 'success') {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = 'toast';
  
  const icon = type === 'error' ? '⚠️' : '✅';
  toast.innerHTML = `<span>${icon}</span> <span>${message}</span>`;
  
  container.appendChild(toast);

  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transform = 'translateX(100%)';
    toast.style.transition = 'all 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 3000);
}
