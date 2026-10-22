<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <script src="https://cdn.tailwindcss.com"></script>
  <title>Login</title>
</head>
<body class="bg-[#622B14] text-white flex min-h-screen items-center justify-center">
  <form id="login-form" class="w-full max-w-sm rounded-3xl bg-[#251813] p-10">
    <h1 class="mb-6 text-3xl font-bold">Login</h1>

    <p id="error-msg" class="mb-4 hidden text-sm text-red-400"></p>

    <input id="email" type="email" placeholder="Email" required
      class="mb-4 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">
    <input id="password" type="password" placeholder="Password" required
      class="mb-6 w-full rounded-full border border-white/10 bg-transparent px-4 py-2">

    <button type="submit" class="w-full rounded-full bg-[#CC6F00] px-4 py-2">Masuk</button>

    <p class="mt-4 text-center text-sm">
      Belum punya akun? <a href="/register" class="text-[#E4D6A9]">Daftar</a>
    </p>
  </form>

  <script>
    const form = document.getElementById('login-form');
    const errorMsg = document.getElementById('error-msg');

    form.addEventListener('submit', async (e) => {
      e.preventDefault();
      errorMsg.classList.add('hidden');

      const res = await fetch('/api/login', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
        body: JSON.stringify({
          email: document.getElementById('email').value,
          password: document.getElementById('password').value,
        }),
      });

      const data = await res.json();

      if (res.ok) {
        localStorage.setItem('token', data.token);
        localStorage.setItem('user', JSON.stringify(data.user));
        window.location.href = '/home';
      } else {
        errorMsg.textContent = data.message || 'Login gagal';
        errorMsg.classList.remove('hidden');
      }
    });
  </script>
</body>
</html>
