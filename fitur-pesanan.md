# Fitur Pesanan (Laravel + Sanctum)

**Aturan akses**
- User: buat pesanan, lihat pesanan sendiri, ubah pesanan sendiri (selama status `pending`)
- Admin: lihat semua pesanan, tambah pesanan (untuk user tertentu), ubah, hapus

---

## 1. Perintah artisan (urut, Menu dulu baru Order)

```bash
php artisan make:model Menu -m
php artisan make:model Order -m
php artisan make:migration add_role_to_users_table --table=users
php artisan make:controller OrderController
php artisan make:middleware AdminOnly
php artisan make:seeder MenuSeeder
```

---

## 2. Migration

**`database/migrations/..._create_menus_table.php`** (isi di dalam `up()`)

```php
Schema::create('menus', function (Blueprint $table) {
    $table->id();
    $table->string('nama');
    $table->text('deskripsi')->nullable();
    $table->unsignedInteger('harga');
    $table->boolean('tersedia')->default(true);
    $table->timestamps();
});
```

**`database/migrations/..._create_orders_table.php`**

```php
Schema::create('orders', function (Blueprint $table) {
    $table->id();
    $table->foreignId('user_id')->constrained()->cascadeOnDelete();
    $table->foreignId('menu_id')->constrained()->cascadeOnDelete();
    $table->unsignedInteger('jumlah')->default(1);
    $table->string('catatan')->nullable();
    $table->string('status')->default('pending'); // pending, diproses, selesai
    $table->timestamps();
});
```

**`database/migrations/..._add_role_to_users_table.php`**

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('role')->default('user')->after('email');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('role');
    });
}
```

---

## 3. Model

**`app/Models/Menu.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Menu extends Model
{
    protected $fillable = ['nama', 'deskripsi', 'harga', 'tersedia'];
}
```

**`app/Models/Order.php`**

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = ['user_id', 'menu_id', 'jumlah', 'catatan', 'status'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function menu()
    {
        return $this->belongsTo(Menu::class);
    }
}
```

`User.php` **jangan** tambah `role` ke Fillable (supaya orang tidak bisa daftar sebagai admin lewat register).

---

## 4. Middleware admin

**`app/Http/Middleware/AdminOnly.php`**

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class AdminOnly
{
    public function handle(Request $request, Closure $next)
    {
        if ($request->user()?->role !== 'admin') {
            return response()->json(['message' => 'Khusus admin'], 403);
        }
        return $next($request);
    }
}
```

**Daftarkan di `bootstrap/app.php`** (di dalam `withMiddleware`):

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->alias([
        'admin' => \App\Http\Middleware\AdminOnly::class,
    ]);
})
```

---

## 5. OrderController

**`app/Http/Controllers/OrderController.php`**

```php
<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    // Admin lihat semua, user cuma lihat miliknya
    public function index(Request $request)
    {
        $query = Order::with(['user:id,name', 'menu:id,nama,harga'])->latest();

        if ($request->user()->role !== 'admin') {
            $query->where('user_id', $request->user()->id);
        }

        return response()->json($query->get());
    }

    // User pesan untuk dirinya. Admin boleh isi user_id untuk menambah pesanan orang lain
    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_id' => 'required|exists:menus,id',
            'jumlah'  => 'required|integer|min:1|max:100',
            'catatan' => 'nullable|string|max:255',
            'user_id' => 'nullable|exists:users,id',
        ]);

        $isAdmin = $request->user()->role === 'admin';
        $data['user_id'] = ($isAdmin && !empty($data['user_id']))
            ? $data['user_id']
            : $request->user()->id;

        $order = Order::create($data);

        return response()->json($order->load(['user:id,name', 'menu:id,nama,harga']), 201);
    }

    // User cuma boleh ubah miliknya & selama masih pending. Admin bebas
    public function update(Request $request, Order $order)
    {
        $isAdmin = $request->user()->role === 'admin';

        if (!$isAdmin) {
            if ($order->user_id !== $request->user()->id) {
                return response()->json(['message' => 'Bukan pesananmu'], 403);
            }
            if ($order->status !== 'pending') {
                return response()->json(['message' => 'Pesanan sudah diproses, tidak bisa diubah'], 422);
            }
        }

        $rules = [
            'jumlah'  => 'required|integer|min:1|max:100',
            'catatan' => 'nullable|string|max:255',
        ];
        if ($isAdmin) {
            $rules['status'] = 'sometimes|in:pending,diproses,selesai';
        }

        $order->update($request->validate($rules));

        return response()->json($order->load(['user:id,name', 'menu:id,nama,harga']));
    }

    // Route ini sudah dibungkus middleware 'admin'
    public function destroy(Order $order)
    {
        $order->delete();
        return response()->json(['message' => 'Pesanan dihapus']);
    }
}
```

---

## 6. Route

**`routes/api.php`** (tambahkan, jangan hapus route auth yang sudah ada)

```php
use App\Http\Controllers\OrderController;
use App\Models\Menu;

// publik: daftar menu
Route::get('/menus', fn () => Menu::where('tersedia', true)->get());

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::put('/orders/{order}', [OrderController::class, 'update']);
    Route::delete('/orders/{order}', [OrderController::class, 'destroy'])->middleware('admin');
});
```

**`routes/web.php`** (tambahkan)

```php
Route::get('/pesanan', function () {
    return view('pesanan');
})->name('pesanan');
```

---

## 7. Seeder (menu + akun admin + akun user)

**`database/seeders/MenuSeeder.php`**

```php
<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['nama' => 'Espresso',    'deskripsi' => 'Kopi hitam pekat dan kuat', 'harga' => 18000],
            ['nama' => 'Cappuccino',  'deskripsi' => 'Espresso dengan susu dan busa lembut', 'harga' => 25000],
            ['nama' => 'Kopi Tubruk', 'deskripsi' => 'Kopi tradisional pekat', 'harga' => 15000],
            ['nama' => 'Cafe Latte',  'deskripsi' => 'Espresso dan susu steamed', 'harga' => 25000],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
```

**`database/seeders/DatabaseSeeder.php`** (isi `run()`)

```php
use App\Models\User;

public function run(): void
{
    // forceCreate dipakai karena 'role' sengaja tidak masuk Fillable
    User::forceCreate([
        'name' => 'Admin',
        'email' => 'admin@email.com',
        'password' => '12345678', // otomatis di-hash oleh cast
        'role' => 'admin',
    ]);

    User::forceCreate([
        'name' => 'Budi',
        'email' => 'budi@email.com',
        'password' => '12345678',
        'role' => 'user',
    ]);

    $this->call(MenuSeeder::class);
}
```

Jalankan:

```bash
php artisan migrate
php artisan db:seed
```

(Kalau tabel `users` sudah berisi dan email bentrok, pakai `php artisan migrate:fresh --seed`. Ini menghapus semua data.)

---

## 8. Simpan data user saat login

Di `login.blade.php`, bagian sukses, tambah satu baris `user`:

```js
if (res.ok) {
  localStorage.setItem('token', data.token);
  localStorage.setItem('user', JSON.stringify(data.user)); // <-- tambahan
  window.location.href = '/pesanan';
}
```

Lakukan hal yang sama di `register.blade.php`. Saat logout, hapus juga `localStorage.removeItem('user')`.

---

## 9. Halaman `resources/views/pesanan.blade.php`

```html
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
    const token = localStorage.getItem('token');
    const user = JSON.parse(localStorage.getItem('user') || 'null');
    if (!token || !user) window.location.href = '/login';

    const isAdmin = user && user.role === 'admin';
    const $ = (id) => document.getElementById(id);
    let orders = [];

    // helper fetch ke API
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
        localStorage.clear();
        window.location.href = '/login';
      }
      const data = await res.json().catch(() => ({}));
      return { ok: res.ok, data };
    }

    // cegah XSS dari catatan/nama
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
      const menus = await res.json();
      $('menu_id').innerHTML = menus.map((m) =>
        `<option value="${m.id}">${esc(m.nama)} - Rp ${m.harga.toLocaleString('id-ID')}</option>`).join('');
    }

    async function loadOrders() {
      const { data } = await api('/api/orders');
      orders = data;
      render();
    }

    function render() {
      if (!orders.length) {
        $('list').innerHTML = '<p class="text-white/60">Belum ada pesanan.</p>';
        return;
      }
      $('list').innerHTML = orders.map((o) => {
        const total = o.menu.harga * o.jumlah;
        const canEdit = isAdmin || o.status === 'pending';
        return `
          <div class="rounded-2xl bg-[#251813] p-5">
            <div class="flex items-start justify-between gap-3">
              <div>
                <p class="text-lg font-semibold">${esc(o.menu.nama)} &times; ${o.jumlah}</p>
                <p class="text-sm text-white/60">${isAdmin ? 'Pemesan: ' + esc(o.user.name) + ' (ID ' + o.user_id + ') &middot; ' : ''}Total Rp ${total.toLocaleString('id-ID')}</p>
                ${o.catatan ? `<p class="text-sm">Catatan: ${esc(o.catatan)}</p>` : ''}
              </div>
              <span class="rounded-full bg-[#CC6F00] px-3 py-1 text-xs">${esc(o.status)}</span>
            </div>
            <div class="mt-3 flex gap-4 text-sm">
              ${canEdit ? `<button data-edit="${o.id}" class="text-[#E4D6A9]">Ubah</button>` : ''}
              ${isAdmin ? `<button data-del="${o.id}" class="text-red-300">Hapus</button>` : ''}
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

    // simpan (buat baru / update)
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

    // tombol Ubah / Hapus
    $('list').addEventListener('click', async (e) => {
      const editId = e.target.dataset.edit;
      const delId = e.target.dataset.del;

      if (editId) {
        const o = orders.find((x) => x.id == editId);
        $('order_id').value = o.id;
        $('menu_id').value = o.menu_id;
        $('menu_id').disabled = true;     // menu tidak bisa diganti saat ubah
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

    // tampilan khusus admin
    $('role-label').textContent = '(' + user.role + ')';
    if (isAdmin) {
      $('user_id').classList.remove('hidden');
      $('status').classList.remove('hidden');
    }

    loadMenus();
    loadOrders();
  </script>
</body>
</html>
```

---

## 10. Cara tes

Akun dari seeder: `admin@email.com` / `12345678` dan `budi@email.com` / `12345678`.

1. Login sebagai **budi** di `/login`, otomatis ke `/pesanan`. Buat pesanan, lalu coba **Ubah**. Tombol Hapus tidak muncul.
2. Logout, login sebagai **admin**. Semua pesanan muncul, ada tombol **Hapus**, dan bisa menambah pesanan dengan mengisi **ID user** (budi = ID 2).
3. Tes keamanan di Postman: pakai token budi, `DELETE /api/orders/1` harus `403 Khusus admin`.

## Troubleshooting cepat

- `Unauthenticated` / halaman balik ke login: token atau `user` di localStorage belum tersimpan (cek langkah 8).
- `Target class [admin] does not exist`: middleware alias belum didaftarkan di `bootstrap/app.php`.
- Error foreign key saat migrate: urutan migration salah. Pastikan `menus` terbuat sebelum `orders`, atau jalankan `migrate:fresh`.
- Kolom `role` tidak ada: migration `add_role_to_users_table` belum dijalankan.
