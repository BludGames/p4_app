<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Pesanan</title>
</head>
<body class="min-h-screen bg-[#622B14] p-6 text-white">
  <div class="mx-auto max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
      <h1 class="text-3xl font-bold">Pesanan <span id="role-label" class="text-sm text-[#E4D6A9]"></span></h1>
      <a href="/home" class="text-[#E4D6A9]">&larr; Beranda</a>
    </div>

    <p id="msg" class="mb-4 hidden text-sm text-red-300"></p>

    <form id="order-form" class="mb-8 grid gap-3 rounded-3xl bg-[#251813] p-6">
      <h2 id="form-title" class="text-xl font-semibold">Buat pesanan</h2>
      <input type="hidden" id="order_id">

      <input id="user_id" type="number" placeholder="ID user pemesan (khusus admin, kosongkan = diri sendiri)"
        class="hidden rounded-full border border-white/10 bg-transparent px-4 py-2">

      <select id="menu_id" class="rounded-full border border-white/10 bg-[#251813] px-4 py-2"></select>

      <input id="jumlah" type="number" min="1" value="1" placeholder="Jumlah"
        class="rounded-full border border-white/10 bg-transparent px-4 py-2">

      <input id="catatan" type="text" placeholder="Catatan (opsional)"
        class="rounded-full border border-white/10 bg-transparent px-4 py-2">

      <select id="status" class="hidden rounded-full border border-white/10 bg-[#251813] px-4 py-2">
        <option value="pending">pending</option>
        <option value="diproses">diproses</option>
        <option value="selesai">selesai</option>
      </select>

      <div class="flex gap-3">
        <button type="submit" class="rounded-full bg-[#CC6F00] px-5 py-2">Simpan</button>
        <button type="button" id="cancel-edit" class="hidden rounded-full border border-white/20 px-5 py-2">Batal</button>
      </div>
    </form>

    <div id="list" class="grid gap-3"></div>
  </div>

  <script>
    (() => {
      const token = localStorage.getItem('token');
      const user = JSON.parse(localStorage.getItem('user') || 'null');
      if (!token || !user) {
        window.location.href = '/login';
        return;
      }

      const isAdmin = user.role === 'admin';
      const $ = (id) => document.getElementById(id);
      let orders = [];

      // Kirim token untuk API yang memerlukan login.
      async function api(url, method = 'GET', body = null) {
        const res = await fetch(url, {
          method,
          headers: {
            'Authorization': 'Bearer ' + token,
            'Accept': 'application/json',
            'Content-Type': 'application/json',
          },
          body: body ? JSON.stringify(body) : null,
        });

        if (res.status === 401) {
          localStorage.removeItem('token');
          localStorage.removeItem('user');
          window.location.href = '/login';
        }

        const data = await res.json().catch(() => ({}));
        return { ok: res.ok, data };
      }

      // Escape teks sebelum dimasukkan ke HTML.
      const esc = (s) => String(s ?? '').replace(/[&<>"']/g, (c) =>
        ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

      function showMsg(text) {
        $('msg').textContent = text;
        $('msg').classList.toggle('hidden', !text);
      }

      function errorText(data) {
        if (data.errors) return Object.values(data.errors).flat().join(' ');
        return data.message || 'Terjadi kesalahan';
      }

      async function loadMenus() {
        const res = await fetch('/api/menus', { headers: { Accept: 'application/json' } });
        if (!res.ok) throw new Error('Menu tidak dapat dimuat.');
        const menus = await res.json();
        $('menu_id').innerHTML = menus.map((m) =>
          `<option value="${esc(m.id)}">${esc(m.nama)} - Rp ${Number(m.harga).toLocaleString('id-ID')}</option>`).join('');

        // Pilih menu dari tautan kartu sebelum memuat pesanan.
        const menuId = new URLSearchParams(window.location.search).get('menu');
        if (menuId && menus.some((menu) => String(menu.id) === menuId)) {
          $('menu_id').value = menuId;
        }
      }

      async function loadOrders() {
        const { data } = await api('/api/orders');
        orders = Array.isArray(data) ? data : [];
        render();
      }

      function render() {
        if (!orders.length) {
          $('list').innerHTML = '<p class="text-white/60">Belum ada pesanan.</p>';
          return;
        }

        $('list').innerHTML = orders.map((o) => {
          const total = Number(o.menu.harga) * Number(o.jumlah);
          const canEdit = isAdmin || o.status === 'pending';
          return `
            <div class="rounded-2xl bg-[#251813] p-5">
              <div class="flex items-start justify-between gap-3">
                <div>
                  <p class="text-lg font-semibold">${esc(o.menu.nama)} &times; ${Number(o.jumlah)}</p>
                  <p class="text-sm text-white/60">${isAdmin ? 'Pemesan: ' + esc(o.user.name) + ' (ID ' + Number(o.user_id) + ') &middot; ' : ''}Total Rp ${total.toLocaleString('id-ID')}</p>
                  ${o.catatan ? `<p class="text-sm">Catatan: ${esc(o.catatan)}</p>` : ''}
                </div>
                <span class="rounded-full bg-[#CC6F00] px-3 py-1 text-xs">${esc(o.status)}</span>
              </div>
              <div class="mt-3 flex gap-4 text-sm">
                ${canEdit ? `<button data-edit="${Number(o.id)}" class="text-[#E4D6A9]">Ubah</button>` : ''}
                ${isAdmin ? `<button data-del="${Number(o.id)}" class="text-red-300">Hapus</button>` : ''}
              </div>
            </div>`;
        }).join('');
      }

      function resetForm() {
        $('order-form').reset();
        $('order_id').value = '';
        $('menu_id').disabled = false;
        $('user_id').disabled = false;
        $('form-title').textContent = 'Buat pesanan';
        $('cancel-edit').classList.add('hidden');
      }

      // Simpan pesanan baru atau perubahan.
      $('order-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        showMsg('');
        const id = $('order_id').value;
        const body = { jumlah: Number($('jumlah').value), catatan: $('catatan').value || null };

        let result;
        if (id) {
          if (isAdmin) body.status = $('status').value;
          result = await api('/api/orders/' + id, 'PUT', body);
        } else {
          body.menu_id = Number($('menu_id').value);
          if (isAdmin && $('user_id').value) body.user_id = Number($('user_id').value);
          result = await api('/api/orders', 'POST', body);
        }

        if (!result.ok) return showMsg(errorText(result.data));
        resetForm();
        loadOrders();
      });

      $('list').addEventListener('click', async (e) => {
        const editId = e.target.dataset.edit;
        const delId = e.target.dataset.del;

        if (editId) {
          const o = orders.find((item) => item.id == editId);
          if (!o) return;
          $('order_id').value = o.id;
          $('menu_id').value = o.menu_id;
          $('menu_id').disabled = true;
          $('user_id').disabled = true;
          $('jumlah').value = o.jumlah;
          $('catatan').value = o.catatan || '';
          if (isAdmin) $('status').value = o.status;
          $('form-title').textContent = 'Ubah pesanan #' + o.id;
          $('cancel-edit').classList.remove('hidden');
          window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        if (delId && confirm('Hapus pesanan ini?')) {
          const { ok, data } = await api('/api/orders/' + delId, 'DELETE');
          if (!ok) return showMsg(errorText(data));
          loadOrders();
        }
      });

      $('cancel-edit').addEventListener('click', resetForm);

      $('role-label').textContent = '(' + user.role + ')';
      if (isAdmin) {
        $('user_id').classList.remove('hidden');
        $('status').classList.remove('hidden');
      }

      async function init() {
        try {
          await loadMenus();
          await loadOrders();
        } catch (error) {
          showMsg(error.message || 'Terjadi kesalahan');
        }
      }

      init();
    })();
  </script>
</body>
</html>
