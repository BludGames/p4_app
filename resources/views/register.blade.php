<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Register</title>
</head>
<body class="bg-[#622B14] text-white flex min-h-screen items-center justify-center">
  <form id="register-form" class="w-full max-w-sm rounded-3xl bg-[#251813] p-10">
    <h1 class="mb-6 text-3xl font-bold">Daftar</h1>

    <!-- tempat pesan error -->
    <div id="error-msg" class="mb-4 hidden text-sm text-red-400"></div>

    <input id="name" type="text" placeholder="Nama" required
      class="mb-4 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">
    <input id="email" type="email" placeholder="Email" required
      class="mb-4 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">
    <input id="password" type="password" placeholder="Password (min. 6 karakter)" required
      class="mb-4 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">
    <input id="password_confirmation" type="password" placeholder="Ulangi password" required
      class="mb-6 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">

    <button type="submit" class="w-full rounded-full bg-[#CC6F00] px-4 py-2">Daftar</button>

    <p class="mt-4 text-center text-sm">
      Sudah punya akun? <a href="/login" class="text-[#E4D6A9]">Login</a>
    </p>
  </form>

  <script>
    const form = document.getElementById('register-form');
    const errorMsg = document.getElementById('error-msg');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorMsg.classList.add('hidden');

      const res = await fetch('/api/register', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          name: document.getElementById('name').value,
          email: document.getElementById('email').value,
          password: document.getElementById('password').value,
          password_confirmation: document.getElementById('password_confirmation').value,
        }),
      });

      const data = await res.json();

      if (res.ok) {
        // Simpan token dan user setelah daftar.
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = '/pesanan';
      } else if (res.status === 422) {
        // gagal validasi: gabungkan semua pesan error
        const pesan = Object.values(data.errors).flat();
        errorMsg.textContent = pesan.join(' ');
        errorMsg.classList.remove('hidden');
      } else {
        errorMsg.textContent = data.message || 'Terjadi kesalahan';
        errorMsg.classList.remove('hidden');
      }
    });
  </script>
</body>
</html>
