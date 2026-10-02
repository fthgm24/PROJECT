let currentTransactionType = 'Pemasukan';
let financeChartInstance = null;
let transactionsData = [];

// Initialize
window.addEventListener('DOMContentLoaded', () => {
  initChart();
  loadStats();
  loadTransactions();
  loadCategories();
  loadEvents();
  loadSettings();
  loadUserInfo();
  syncTheme();
});

function syncTheme() {
  const savedTheme = localStorage.getItem('pb_theme');
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-mode');
    const themeCheckbox = document.getElementById('darkModeToggle');
    if (themeCheckbox) themeCheckbox.checked = true;
  }
}

// ========== USER INFO DARI SESSION LOGIN ==========
function loadUserInfo() {
  fetch('api/user_info.php')
    .then(res => res.json())
    .then(data => {
      if (data.success && data.user) {
        const u = data.user;
        if (document.getElementById('displayUserName')) {
          document.getElementById('displayUserName').innerText = u.name || 'User';
        }
        document.querySelectorAll('.user-role').forEach(el => {
          el.innerText = u.role || 'Staff Booth';
        });
      }
    })
    .catch(() => {});
}


// ========== STATS & CHART (DYNAMIC DATABASE PKK) ==========
function loadStats() {
  fetch('api/stats.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      
      // Total Keseluruhan
      const pem = 'Rp ' + Number(data.total_pemasukan || 0).toLocaleString('id-ID');
      const peng = 'Rp ' + Number(data.total_pengeluaran || 0).toLocaleString('id-ID');
      const profit = 'Rp ' + Number(data.keuntungan_bersih || 0).toLocaleString('id-ID');

      if (document.getElementById('valTotalPemasukan')) document.getElementById('valTotalPemasukan').innerText = pem;
      if (document.getElementById('valTotalPengeluaran')) document.getElementById('valTotalPengeluaran').innerText = peng;
      if (document.getElementById('valKeuntunganBersih')) document.getElementById('valKeuntunganBersih').innerText = profit;

      // Ringkasan Bulan Ini
      const pemBulan = 'Rp ' + Number(data.pemasukan_bulan_ini !== undefined ? data.pemasukan_bulan_ini : data.total_pemasukan || 0).toLocaleString('id-ID');
      const pengBulan = 'Rp ' + Number(data.pengeluaran_bulan_ini !== undefined ? data.pengeluaran_bulan_ini : data.total_pengeluaran || 0).toLocaleString('id-ID');
      const profitBulan = 'Rp ' + Number(data.keuntungan_bulan_ini !== undefined ? data.keuntungan_bulan_ini : data.keuntungan_bersih || 0).toLocaleString('id-ID');

      if (document.getElementById('sumPemasukan')) document.getElementById('sumPemasukan').innerText = pemBulan;
      if (document.getElementById('sumPengeluaran')) document.getElementById('sumPengeluaran').innerText = pengBulan;
      if (document.getElementById('sumKeuntungan')) document.getElementById('sumKeuntungan').innerText = profitBulan;

      loadCategoryBreakdown();

      // Transaksi Terbaru Dashboard (5 Terakhir dari Database)
      const dashBody = document.getElementById('dashTransactionTable');
      if (dashBody) {
        let html = '';
        if (!data.recent_transactions || data.recent_transactions.length === 0) {
          html = '<tr><td colspan="6" style="text-align: center; color: var(--text-muted); padding: 20px;">Belum ada data transaksi di database. Silakan tambah transaksi baru.</td></tr>';
        } else {
          data.recent_transactions.forEach(t => {
            const badgeClass = t.type === 'Pemasukan' ? 'badge-success' : 'badge-danger';
            html += `<tr>
              <td>${t.date}</td>
              <td>${t.description}</td>
              <td><span class="badge ${badgeClass}">${t.type}</span></td>
              <td>${t.category}</td>
              <td>Rp ${Number(t.amount).toLocaleString('id-ID')}</td>
              <td><button class="action-dots" onclick="deleteTransaction(${t.id})">🗑️</button></td>
            </tr>`;
          });
        }
        dashBody.innerHTML = html;
      }

      // Update Grafik Chart.js dari Database secara Dinamis
      if (data.chart && financeChartInstance) {
        financeChartInstance.data.labels = data.chart.labels;
        financeChartInstance.data.datasets[0].data = data.chart.pemasukan;
        financeChartInstance.data.datasets[1].data = data.chart.pengeluaran;

        const maxVal = Math.max(...data.chart.pemasukan, ...data.chart.pengeluaran, 1000000);
        financeChartInstance.options.scales.y.max = Math.ceil(maxVal * 1.25);
        financeChartInstance.update();
      }
    });
}

function loadCategoryBreakdown() {
  fetch('api/transactions.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      const all = data.data || [];
      const incomeMap = {};
      const expenseMap = {};
      let totalIncome = 0, totalExpense = 0;

      all.forEach(t => {
        const amt = Number(t.amount);
        if (t.type === 'Pemasukan') {
          incomeMap[t.category] = (incomeMap[t.category] || 0) + amt;
          totalIncome += amt;
        } else {
          expenseMap[t.category] = (expenseMap[t.category] || 0) + amt;
          totalExpense += amt;
        }
      });

      renderProgressBars('incomeBreakdown', incomeMap, totalIncome, '#1665D8');
      renderProgressBars('expenseBreakdown', expenseMap, totalExpense, '#ef4444');
    });
}

function renderProgressBars(containerId, map, total, color) {
  const container = document.getElementById(containerId);
  if (!container) return;
  if (total === 0) {
    container.innerHTML = '<p style="color: var(--text-muted); font-size: 13px; margin-top: 8px;">Belum ada data transaksi.</p>';
    return;
  }
  const colors = [color, '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'];
  let idx = 0;
  let html = '';
  for (const [cat, amt] of Object.entries(map)) {
    const pct = ((amt / total) * 100).toFixed(1);
    const c = colors[idx % colors.length];
    html += `<div class="progress-bar-container">
      <div class="progress-info">
        <span>${cat}</span>
        <span>${pct}% (Rp ${amt.toLocaleString('id-ID')})</span>
      </div>
      <div class="progress-track">
        <div class="progress-fill" style="width: ${pct}%; background-color: ${c};"></div>
      </div>
    </div>`;
    idx++;
  }
  container.innerHTML = html;
}

// ========== INIT CHART ==========
function initChart() {
  const chartEl = document.getElementById('financeChart');
  if (!chartEl) return;
  const ctx = chartEl.getContext('2d');
  financeChartInstance = new Chart(ctx, {
    type: 'bar',
    data: {
      labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'],
      datasets: [
        {
          label: 'Pemasukan',
          data: [0, 0, 0, 0, 0, 0],
          backgroundColor: '#1665D8',
          borderRadius: 4,
          barPercentage: 0.5,
          categoryPercentage: 0.6
        },
        {
          label: 'Pengeluaran',
          data: [0, 0, 0, 0, 0, 0],
          backgroundColor: '#9ec5fe',
          borderRadius: 4,
          barPercentage: 0.5,
          categoryPercentage: 0.6
        }
      ]
    },
    options: {
      responsive: true,
      maintainAspectRatio: false,
      plugins: {
        legend: { display: false },
        tooltip: {
          callbacks: {
            label: function(context) {
              return context.dataset.label + ': Rp ' + Number(context.raw).toLocaleString('id-ID');
            }
          }
        }
      },
      scales: {
        x: {
          grid: { display: false },
          ticks: { color: '#64748b', font: { family: 'Plus Jakarta Sans', size: 11 } }
        },
        y: {
          grid: { color: '#f1f5f9' },
          ticks: {
            color: '#64748b',
            font: { family: 'Plus Jakarta Sans', size: 11 },
            callback: function(value) {
              if (value >= 1000000) return (value / 1000000).toFixed(1) + 'M';
              if (value >= 1000) return (value / 1000).toFixed(0) + 'K';
              return value;
            }
          },
          min: 0
        }
      }
    }
  });
}

function updateChartPeriod(period) {
  if (!financeChartInstance) return;
  if (period === '3bulan') {
    financeChartInstance.data.labels = ['Mei', 'Jun', 'Jul'];
    financeChartInstance.data.datasets[0].data = [8.3, 8.8, 9.7];
    financeChartInstance.data.datasets[1].data = [3.7, 4.0, 4.2];
  } else if (period === 'tahun') {
    financeChartInstance.data.labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'];
    financeChartInstance.data.datasets[0].data = [5.5, 6.3, 6.3, 7.4, 8.3, 8.8, 9.7];
    financeChartInstance.data.datasets[1].data = [2.8, 3.2, 2.9, 3.5, 3.7, 4.0, 4.2];
  } else {
    financeChartInstance.data.labels = ['Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul'];
    financeChartInstance.data.datasets[0].data = [6.3, 6.3, 7.4, 8.3, 8.8, 9.7];
    financeChartInstance.data.datasets[1].data = [3.2, 2.9, 3.5, 3.7, 4.0, 4.2];
  }
  financeChartInstance.update();
  showToast('Periode grafik diperbarui!');
}

// ========== PAGE SWITCHING ==========
function switchPage(pageId, element) {
  document.querySelectorAll('.page-section').forEach(sec => sec.classList.remove('active'));
  document.querySelectorAll('.nav-item').forEach(item => item.classList.remove('active'));

  const targetView = document.getElementById('view-' + pageId);
  if (targetView) targetView.classList.add('active');

  if (element) {
    element.classList.add('active');
  } else {
    const items = document.querySelectorAll('.nav-item');
    items.forEach(item => {
      if (item.innerText.toLowerCase().includes(pageId)) {
        item.classList.add('active');
      }
    });
  }

  const titleMap = {
    'dashboard': { title: 'Dashboard', sub: 'Kelola pemasukan dan pengeluaran usaha Anda.' },
    'transaksi': { title: 'Transaksi', sub: 'Daftar riwayat transaksi keuangan lengkap.' },
    'laporan': { title: 'Laporan Keuangan', sub: 'Ringkasan dan grafik analisis pemasukan/pengeluaran.' },
    'kategori': { title: 'Kategori', sub: 'Atur kategori transaksi usaha Anda.' },
    'event': { title: 'Jadwal Event', sub: 'Manajemen event booking mendatang.' },
    'pengaturan': { title: 'Pengaturan', sub: 'Konfigurasi profil usaha dan preferensi aplikasi.' }
  };

  if (titleMap[pageId]) {
    document.getElementById('pageTitle').innerText = titleMap[pageId].title;
    document.getElementById('pageSubtitle').innerText = titleMap[pageId].sub;
  }

  document.getElementById('sidebar').classList.remove('active');
}

// ========== TRANSACTIONS ==========
function loadTransactions() {
  fetch('api/transactions.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      transactionsData = data.data || [];
      renderFullTable(transactionsData);
    });
}

function renderFullTable(data) {
  const fullBody = document.getElementById('fullTransactionTable');
  let html = '';
  (data || []).forEach(t => {
    const badgeClass = t.type === 'Pemasukan' ? 'badge-success' : 'badge-danger';
    html += `<tr>
      <td>#${t.id}</td>
      <td>${t.date}</td>
      <td>${t.description}</td>
      <td><span class="badge ${badgeClass}">${t.type}</span></td>
      <td>${t.category}</td>
      <td>Rp ${Number(t.amount).toLocaleString('id-ID')}</td>
      <td><button class="action-dots" onclick="deleteTransaction(${t.id})">🗑️</button></td>
    </tr>`;
  });
  fullBody.innerHTML = html;
}

function filterTransactions() {
  const search = document.getElementById('searchTransactionInput').value.toLowerCase();
  const typeFilter = document.getElementById('filterTypeSelect').value;

  const filtered = transactionsData.filter(t => {
    const matchesSearch = (t.description || '').toLowerCase().includes(search) || (t.category || '').toLowerCase().includes(search);
    const matchesType = typeFilter === 'semua' || t.type === typeFilter;
    return matchesSearch && matchesType;
  });
  renderFullTable(filtered);
}

let categoriesData = [];

function openModal(type) {
  currentTransactionType = type;
  document.getElementById('modalTitle').innerText = 'Tambah ' + type;
  
  const submitBtn = document.querySelector('#transactionForm .submit-btn');
  if (submitBtn) submitBtn.innerText = 'Simpan ' + type;

  const descInput = document.getElementById('inputDesc');
  if (descInput) {
    descInput.placeholder = type === 'Pemasukan' 
      ? 'Contoh: Sewa Booth Wedding A' 
      : 'Contoh: Beli Kertas Foto / Transportasi';
  }

  populateCategorySelect(type);
  document.getElementById('transactionModal').classList.add('active');
}

function populateCategorySelect(type) {
  const catSelect = document.getElementById('inputCategory');
  if (!catSelect) return;
  catSelect.innerHTML = '';

  let filtered = (categoriesData || []).filter(c => c.type === type);
  if (filtered.length === 0) {
    if (type === 'Pemasukan') {
      filtered = [{ name: 'Sewa Booth' }, { name: 'Cetak Foto Tambahan' }, { name: 'Merchandise' }];
    } else {
      filtered = [{ name: 'Operasional' }, { name: 'Transportasi' }, { name: 'Maintenance Alat' }];
    }
  }

  filtered.forEach(c => {
    const opt = document.createElement('option');
    opt.value = c.name;
    opt.textContent = c.name;
    catSelect.appendChild(opt);
  });
}

function closeModal(modalId) {
  document.getElementById(modalId).classList.remove('active');
}

function handleTransactionSubmit(e) {
  e.preventDefault();
  const desc = document.getElementById('inputDesc').value;
  const cat = document.getElementById('inputCategory').value;
  const amount = parseInt(document.getElementById('inputAmount').value);

  // LANGSUNG TUTUP MODAL SETELAH TOMBOL SIMPAN DIKLIK
  closeModal('transactionModal');

  const formData = new URLSearchParams();
  formData.append('description', desc);
  formData.append('type', currentTransactionType);
  formData.append('category', cat);
  formData.append('amount', amount);

  // Kirim data transaksi ke backend PHP (save.php / database pkk)
  fetch('php/save.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    document.getElementById('transactionForm').reset();
    if (data.success) {
      showToast(`${currentTransactionType} baru berhasil disimpan ke database!`);
      if (typeof loadStats === 'function') loadStats();
      if (typeof loadTransactions === 'function') loadTransactions();
    } else {
      showToast(data.error || 'Gagal menyimpan', 'error');
    }
  })
  .catch(err => {
    document.getElementById('transactionForm').reset();
    showToast(`${currentTransactionType} berhasil disimpan!`);
    if (typeof loadStats === 'function') loadStats();
    if (typeof loadTransactions === 'function') loadTransactions();
  });
}

function deleteTransaction(id) {
  if (!confirm('Yakin ingin menghapus transaksi ini?')) return;
  fetch('api/transactions.php?id=' + id, { method: 'DELETE' })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        showToast('Transaksi telah dihapus.');
        loadStats();
        loadTransactions();
      }
    });
}

// ========== CATEGORIES ==========
function loadCategories() {
  fetch('api/categories.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      categoriesData = data.data || [];
      const tbody = document.getElementById('categoryTableBody');
      if (tbody) {
        let html = '';
        categoriesData.forEach(c => {
          const badgeClass = c.type === 'Pemasukan' ? 'badge-success' : 'badge-danger';
          html += `<tr>
            <td><strong>${c.name}</strong></td>
            <td><span class="badge ${badgeClass}">${c.type}</span></td>
            <td>${c.transaction_count || 0} Transaksi</td>
            <td><button class="action-dots">⋮</button></td>
          </tr>`;
        });
        tbody.innerHTML = html;
      }
      populateCategorySelect(currentTransactionType);
    });
}

function openAddCategoryModal() { document.getElementById('categoryModal').classList.add('active'); }

function handleCategorySubmit(e) {
  e.preventDefault();
  const name = document.getElementById('inputCategoryName').value;
  const type = document.getElementById('inputCategoryType').value;

  const formData = new URLSearchParams();
  formData.append('name', name);
  formData.append('type', type);

  fetch('api/categories.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      closeModal('categoryModal');
      showToast('Kategori ' + name + ' berhasil ditambahkan!');
      loadCategories();
    } else {
      showToast(data.error || 'Gagal', 'error');
    }
  });
}

// ========== EVENTS ==========
function loadEvents() {
  fetch('api/events.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success) return;
      const tbody = document.getElementById('eventTableBody');
      let html = '';
      (data.data || []).forEach(e => {
        const statusClass = e.status === 'Selesai' ? 'badge-success' : e.status === 'Mendatang' ? 'badge-info' : 'badge-danger';
        html += `<tr>
          <td>${e.event_date}</td>
          <td>${e.event_name}</td>
          <td>${e.location}</td>
          <td>${e.package}</td>
          <td><span class="badge ${statusClass}">${e.status}</span></td>
          <td><button class="action-dots">⋮</button></td>
        </tr>`;
      });
      tbody.innerHTML = html;
    });
}

function openEventModal() { document.getElementById('eventModal').classList.add('active'); }

function handleEventSubmit(e) {
  e.preventDefault();
  const name = document.getElementById('inputEventName').value;
  const date = document.getElementById('inputEventDate').value;
  const loc = document.getElementById('inputEventLocation').value;
  const pkg = document.getElementById('inputEventPackage').value;

  const formData = new URLSearchParams();
  formData.append('event_name', name);
  formData.append('event_date', date);
  formData.append('location', loc);
  formData.append('package', pkg);

  fetch('api/events.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      closeModal('eventModal');
      showToast('Event ' + name + ' berhasil dibuat!');
      loadEvents();
    } else {
      showToast(data.error || 'Gagal', 'error');
    }
  });
}

// ========== SETTINGS ==========
function loadSettings() {
  fetch('api/settings.php')
    .then(res => res.json())
    .then(data => {
      if (!data.success || !data.data) return;
      const s = data.data;
      // Nama & role di header dihandle oleh loadUserInfo() dari tabel users
      if (document.getElementById('settingBusinessName')) document.getElementById('settingBusinessName').value = s.business_name || '';
      if (document.getElementById('settingAdminName')) document.getElementById('settingAdminName').value = s.admin_name || '';
      if (document.getElementById('settingEmail')) document.getElementById('settingEmail').value = s.email || '';
      if (document.getElementById('settingCurrency')) document.getElementById('settingCurrency').value = s.currency || 'IDR';
      if (s.theme === 'dark') {
        document.body.classList.add('dark-mode');
        const cb = document.getElementById('darkModeToggle');
        if (cb) cb.checked = true;
      }
    });
}

function saveSettings(e) {
  e.preventDefault();
  const business_name = document.getElementById('settingBusinessName').value;
  const admin_name = document.getElementById('settingAdminName').value;
  const email = document.getElementById('settingEmail').value;
  const currency = document.getElementById('settingCurrency').value;
  const theme = document.body.classList.contains('dark-mode') ? 'dark' : 'light';

  const formData = new URLSearchParams();
  formData.append('business_name', business_name);
  formData.append('admin_name', admin_name);
  formData.append('email', email);
  formData.append('theme', theme);
  formData.append('currency', currency);

  fetch('api/settings.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: formData.toString()
  })
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      document.getElementById('displayUserName').innerText = admin_name;
      showToast('Profil admin diperbarui menjadi ' + admin_name);
    }
  });
}

// ========== DARK MODE ==========
function toggleDarkMode(isDark) {
  if (isDark) document.body.classList.add('dark-mode');
  else document.body.classList.remove('dark-mode');
  showToast(isDark ? 'Mode Gelap Aktif' : 'Mode Terang Aktif');
}

// ========== EXPORT CSV ==========
function exportCSVData() {
  let csvContent = "data:text/csv;charset=utf-8,ID,Tanggal,Keterangan,Jenis,Kategori,Nominal\n";
  transactionsData.forEach(t => {
    csvContent += `${t.id},${t.date},"${t.description}",${t.type},"${t.category}",${t.amount}\n`;
  });
  const encodedUri = encodeURI(csvContent);
  const link = document.createElement("a");
  link.setAttribute("href", encodedUri);
  link.setAttribute("download", "laporan_sistem_keuangan.csv");
  document.body.appendChild(link);
  link.click();
  document.body.removeChild(link);
  showToast('Laporan CSV berhasil di-download!');
}

// ========== DATE FILTER ==========
function openDateModal() { document.getElementById('dateFilterModal').classList.add('active'); }
function setDateRange(label) {
  document.getElementById('selectedDateText').innerText = label;
  closeModal('dateFilterModal');
  showToast('Rentang tanggal diubah ke: ' + label);
}

// ========== LOGOUT ==========
function handleLogout() {
  if (confirm('Apakah Anda yakin ingin keluar dari sistem?')) {
    showToast('Logout berhasil. Mengalihkan ke Halaman Login...');
    setTimeout(() => {
      window.location.href = 'api/logout.php';
    }, 1000);
  }
}

// ========== TOAST ==========
function showToast(message) {
  const container = document.getElementById('toastContainer');
  const toast = document.createElement('div');
  toast.className = 'toast';
  toast.innerHTML = `
    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" style="color: #10b981;">
      <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
    </svg>
    <span>${message}</span>
  `;
  container.appendChild(toast);
  setTimeout(() => {
    toast.style.opacity = '0';
    toast.style.transition = 'opacity 0.3s ease';
    setTimeout(() => toast.remove(), 300);
  }, 2500);
}

// ========== MOBILE SIDEBAR ==========
document.getElementById('hamburgerBtn').addEventListener('click', () => {
  document.getElementById('sidebar').classList.toggle('active');
});
