<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <!-- cdn tailwind -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- MY CSS -->
    <link rel="stylesheet" href="{{ asset('style.css') }}" />
    <script src="{{ asset('app.js') }}" defer></script>
    <title>P4TKJ-PRL</title>
  </head>
  <body class="bg-[#622B14] text-white">

  <!-- navbar -->
  <nav class="fixed top-0 z-50 w-full border-b border-[#2A1A0E] bg-[#6E3511] backdrop-blur-lg">
    <div class="mx-auto justify-between flex items-center gap-8 max-w-7xl px-6 py-5">
      <a href="#home" class="text-2xl font-bold ">Coffee <span class="text-[#2A1A0E]">Break</span></a>
      <div class="hidden md:flex gap-8" id="nav-links">
        <a href="#home" class="transition duration-300 hover:text-[#CC6F00]">Home</a>
        <a href="#jelajah" class="transition duration-300 hover:text-[#CC6F00]">Jelajah</a>
        <a href="#menu" class="transition duration-300 hover:text-[#CC6F00]">Menu</a>
        <a href="#contact" class="transition duration-300 hover:text-[#CC6F00]">Kontak</a>
        <button id="logout-btn" onclick="logout()" class="hidden rounded-full bg-[#CC6F00] px-4 py-1">Logout</button>
      </div>
      <!--tombol pesan-->
      <button id="menu-btn" class="md:hidden text-2xl" type="button">☰</button>
    </div>
  </nav> <!-- NAV CLOSE  -->

  <!-- Hero Section -->
    <section id="home" class="flex min-h-screen flex-col items-center gap-10 px-6 py-18 md:flex-row md:px-20">
      <div class="fade-up .mx-[5px] w-full max-w-2xl md:w-1/2">
        <!-- Konten Kiri -->
        
          <p class="mb-5 text-sm font-bold uppercase">Selamat Datang di Coffe<span class="text-[#E4D6A9]">Break</span></p>
          <h1 class="text-5xl font-bold">Nikmati Setiap Slurpp Dengan <span class="text-[#E4D6A9]">Kualitas Terbaik</span></h1>
          <p class=".leading-[2] mt-6 max-w-lg">Lorem, ipsum dolor sit amet consectetur adipisicing elit. Temporibus odit doloremque illo nobis amet aliquid veniam facilis laboriosam, animi adipisci omnis, vel ullam accusantium dolore. Velit obcaecati minima possimus ullam?</p>
          
            <!-- tombol jelajah -->
        <div class="mt-6 flex flex-wrap gap-6">
          <a href="#menu" class="rounded-full bg-[#995f2f] px-4 py-2 border-2 border-transparent transition-all duration-500 hover:border-[3px] hover:border-[#E4D6A9]  hover:scale-110">Jelajah</a>
          <a href="#menu" class="rounded-full bg-[#995f2f] px-4 py-2 border border-transparent transition-all duration-500 hover:border-[3px] hover:border-[#E4D6A9] hover:scale-110">Cerita Kami</a>
        </div>
        
        
      </div>
        <div class="img-hero flex w-full items-center justify-center gap-4 md:w-1/2">
          <img src="{{ asset('asset/KopiNgeblur (2).jpg') }}" alt="Kopi" class="mt-4 w-1/2 max-w-lg rounded-t-full rounded-b-xl object-cover">
          <img src="{{ asset('asset/Biji.jpg') }}" alt="Biji kopi" class="mt-4 w-1/2 max-w-xl rounded-t-full rounded-b-xl object-cover">
          
        </div> 
      
      
   </section>
   <!-- Hero close -->
    <section id="menu" class="bg-[#995F2F] px-6 py-24">
    <div class="mx-auto max-w-7xl">
      <div class="mb-4 text-center">
        <p class="text-sm tracking-[0.5em] text-[#622B14] font-bold">AKU CINTA KOPI</p>
        <h2 class="text-4xl font-bold">TEMUKAN RASA CINTA DI <span class="text-[#622B14]">KOPI KAMI</span></h2>
      </div>
    </div>

    <div id="menu-grid" class="mx-auto grid max-w-7xl grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-4"></div>
  </section>

  <section id="jelajah" class="px-6 py-24">
    <div class="mx-auto grid max-w-7xl items-center gap-10 md:grid-cols-2">
        <!--content kiri-->
        <img src="{{ asset('asset/KopiNgeblur.jpg') }}" alt="Secangkir kopi dengan uap"
            class="w-full rounded-s-full object-cover" >
        <!--conent kanan-->
        <div>
            <p class="text-sm tracking-[0.5em] text-[#CC6F00]">Temukan Kopinya</p>
            <h2 class="text-4xl font-bold">Yang <span class="text-[#CC6F00]">Dibuat Dengan Hati</span></h2>
            <p class="mt-6 max-w-lg leading-8 text-orange-600">Nikmati Kopinya dari seruputan pertama hingga tetes terakhir, dibuat dengan biji kopi terbaik dan diracik oleh barista professional</p>
            <p class="mt-6 max-w-lg leading-8 text-orange-600">Nikmati Kopinya dari seruputan pertama hingga tetes terakhir, dibuat dengan biji kopi terbaik dan diracik oleh barista professional</p>
            <a href="#menu" class="mt-6 inline-block rounded-full bg-[#F2F7A0] px-4 py-1 text-orange-600 transition hover:border-2 hover:border-[#4D2A00] hover:text-[#4D2A00]">Kenali kami</a>
        </div>
    </div>
  </section>

  <section id="news" class="bg-[#211711] py-6 px-24">
    <div class="mx-auto max-w-7xl">
        <div class="mx-auto text-center">
            <p class="text-sm tracking-[0.5em] text-[#CC6F00]">Dari Biji Ke Cangkir</p>
            <h2 class="mb-14 text-4xl font-semibold md:text-5xl">Dibuat Dengan Perhatian</h2>
            <p class="mt-2 leading-7 text-orange-600">Memilih Biji Dengan Aroma Yang Kuat dan Segar</p>
        </div>
    </div>
  </section>

  <section id="contact" class="bg-[#211711] py-6 px-24">
    <div class="ms:auto grid max-w-7xl items-center gap-10 md:grid-cols-2
    ">
    <!--Kiri-->
    <div>
        <p class="text-sm tracking-[0.5em] text-[#CC6F00]">Selamat Datang</p>
        <h2 class="mb-14 text-4xl font-semibold md:text-5xl">Mari Ngopi Bareng</h2>
        <p class="mt-2 leading-7 text-orange-600">Jalan. P4TK Jakarta Timur, Duren Sawit</p>
    </div>

    <!--Kanan-->
    <div>
        <form action="" class="rounded-3xl bg-[#251813] p-14">
            <div class="grid gap-14 mt-6">
                <input type="text" class="w-full rounded-full border border-white/10 bg-transparent" placeholder="Masukan Nama Anda" required>
            </div>
            <div class="grid gap-14 mt-6">
                <input type="text" class="w-full rounded-full border border-white/10 bg-transparent" placeholder="Masukan No Pesanan" required>
            </div>
            <div class="grid gap-14 mt-6">
                <input type="text" class="w-full rounded-full border border-white/10 bg-transparent" placeholder="Masukan Pesan">
            </div>
            <div class="flex justify-between mt-6">
                <button type="submit" href="#" class="rounded-full bg-[#CC6F00] px-4 py-2">Kirim Pesanan</button>
            </div>
        </form>
    </div>
    
    </div>
  </section>
   <footer class="px-6 py-24">
    <div class="mx-auto flex justify-between items-center max-w-7xl gap-6">
      <a href="home" class="font-semibold">Coffee<span class="text-[#2A1A0E]">Break</span></a>
      <p class="text-sm">&copy; Hak cipta 2024 - Coffee Break</p>
    </div>
   </footer>
   </body>
</html>
<!-- 
60% warna utama
20% warna kedua
20% warna ketiga 
-->
