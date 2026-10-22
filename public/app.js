const menuBtn = document.querySelector('#menu-btn');
const navLinks = document.querySelector('#nav-links');

if (menuBtn && navLinks) {
  menuBtn.addEventListener('click', () => {
    navLinks.classList.toggle('hidden');
  });
}

async function logout() {
  try {
    await fetch('/api/logout', {
      method: 'POST',
      headers: {
        'Authorization': 'Bearer ' + localStorage.getItem('token'),
        'Accept': 'application/json',
      },
    });
  } catch (e) {
    // abaikan error jaringan, tetap hapus token di browser
  }

  localStorage.removeItem('token');
  localStorage.removeItem('user');
  window.location.href = '/login';
}

document.addEventListener('DOMContentLoaded', () => {
  const btn = document.getElementById('logout-btn');
  if (btn && localStorage.getItem('token')) {
    btn.classList.remove('hidden');
  }

  const grid = document.getElementById('menu-grid');
  if (grid) loadMenuCards(grid);
});

// Ambil menu dari API dan tampilkan sebagai kartu.
async function loadMenuCards(grid) {
  const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (char) =>
    ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));

  try {
    const response = await fetch('/api/menus', { headers: { Accept: 'application/json' } });
    if (!response.ok) throw new Error('Menu tidak dapat dimuat.');

    const menus = await response.json();
    grid.innerHTML = menus.map((menu) => `
      <article class="mx-auto w-full max-w-sm overflow-hidden rounded-2xl border border-white/5 bg-[#622B14] transition duration-500 hover:translate-y-2">
        <img src="/${esc(menu.gambar || '')}" alt="${esc(menu.nama)}" class="h-64 w-full object-cover">
        <div class="p-5">
          <h3 class="text-2xl">${esc(menu.nama)}</h3>
          <p class="mt-2 text-red-800">${esc(menu.deskripsi || '')}</p>
          <div class="mt-4 flex items-center justify-between">
            <span class="text-xl font-bold">Rp ${Number(menu.harga).toLocaleString('id-ID')}</span>
            <a href="/pesanan?menu=${encodeURIComponent(menu.id)}" class="rounded-full bg-[#CC6F00] px-4 py-2 text-white">Pesan</a>
          </div>
        </div>
      </article>`).join('');
  } catch (error) {
    grid.textContent = 'Menu belum dapat dimuat.';
  }
}
