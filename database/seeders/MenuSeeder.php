<?php

namespace Database\Seeders;

use App\Models\Menu;
use Illuminate\Database\Seeder;

class MenuSeeder extends Seeder
{
    public function run(): void
    {
        $menus = [
            ['nama' => 'Espresso', 'deskripsi' => 'Kopi hitam pekat dan kuat', 'harga' => 18000, 'gambar' => 'asset/KopiNgeblur.jpg'],
            ['nama' => 'Cappuccino', 'deskripsi' => 'Espresso dengan susu dan busa lembut', 'harga' => 25000, 'gambar' => 'asset/coffee_cup2.jpg'],
            ['nama' => 'Kopi Tubruk', 'deskripsi' => 'Kopi tradisional pekat', 'harga' => 15000, 'gambar' => 'asset/kopitubruk.jpg'],
            ['nama' => 'Cafe Latte', 'deskripsi' => 'Espresso dan susu steamed', 'harga' => 25000, 'gambar' => 'asset/Caffe_Latte_at_Pulse_Cafe.jpg'],
        ];

        foreach ($menus as $menu) {
            Menu::create($menu);
        }
    }
}
