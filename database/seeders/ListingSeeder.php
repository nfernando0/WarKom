<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Community;
use App\Models\Listing;
use App\Models\ListingImage;
use App\Models\User;
use Illuminate\Database\Seeder;

class ListingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Pastikan kategori default tersedia
        $categoriesData = [
            'Elektronik' => ['icon' => 'device-phone-mobile'],
            'Pakaian & Mode' => ['icon' => 'tag'],
            'Makanan & Minuman' => ['icon' => 'cake'],
            'Rumah Tangga' => ['icon' => 'home'],
            'Hobi & Hiburan' => ['icon' => 'sparkles'],
            'Kendaraan & Otomotif' => ['icon' => 'truck'],
            'Jasa & Lainnya' => ['icon' => 'briefcase'],
        ];

        $categories = [];
        foreach ($categoriesData as $name => $data) {
            $categories[$name] = Category::firstOrCreate(['name' => $name], [
                'name' => $name,
                'icon' => $data['icon'],
            ]);
        }

        // 2. Pastikan Komunitas Default tersedia
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@warkom.test'],
            [
                'name' => 'Admin WarKom',
                'password' => bcrypt('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'address' => 'Kantor Pengelola WarKom Blok A1',
            ]
        );

        $community = Community::firstOrCreate(
            ['invite_code' => 'WARKOM-KOMP-01'],
            [
                'name' => 'Warga RT 05 / RW 02 Pondok Indah',
                'description' => 'Komunitas jual beli dan berbagi antar tetangga RT 05 RW 02.',
                'location' => 'Jakarta Selatan',
                'created_by' => $adminUser->id,
            ]
        );

        // Update admin community jika belum
        if (! $adminUser->community_id) {
            $adminUser->update(['community_id' => $community->id]);
        }

        // 3. User Anggota Komunitas
        $usersData = [
            [
                'name' => 'Budi Santoso',
                'email' => 'budi@warkom.test',
                'phone' => '081234567891',
                'address' => 'Jl. Mawar No. 12, RT 05',
            ],
            [
                'name' => 'Siti Rahmawati',
                'email' => 'siti@warkom.test',
                'phone' => '081234567892',
                'address' => 'Jl. Melati No. 04, RT 05',
            ],
            [
                'name' => 'Andi Pratama',
                'email' => 'andi@warkom.test',
                'phone' => '081234567893',
                'address' => 'Jl. Anggrek No. 18, RT 05',
            ],
            [
                'name' => 'Dewi Lestari',
                'email' => 'dewi@warkom.test',
                'phone' => '081234567894',
                'address' => 'Jl. Kenanga No. 07, RT 05',
            ],
            [
                'name' => 'Rian Hidayat',
                'email' => 'rian@warkom.test',
                'phone' => '081234567895',
                'address' => 'Jl. Cempaka No. 21, RT 05',
            ],
            [
                'name' => 'Maya Safitri',
                'email' => 'maya@warkom.test',
                'phone' => '081234567896',
                'address' => 'Jl. Dahlia No. 09, RT 05',
            ],
        ];

        $users = [];
        foreach ($usersData as $u) {
            $users[] = User::firstOrCreate(
                ['email' => $u['email']],
                [
                    'name' => $u['name'],
                    'password' => bcrypt('password'),
                    'role' => 'user',
                    'phone' => $u['phone'],
                    'address' => $u['address'],
                    'community_id' => $community->id,
                ]
            );
        }

        // 4. Daftar Produk / Listings Realistis dengan Foto Berkualitas
        $products = [
            // ELEKTRONIK
            [
                'category' => 'Elektronik',
                'user_index' => 0,
                'title' => 'MacBook Air M1 2020 Space Grey (8GB / 256GB)',
                'description' => "Dijual MacBook Air M1 warna Space Grey kondisi sangat terawat 95% pemakaian pribadi.\n\n- Battery Health 91% (Cycle count rendah)\n- Kelengkapan Fullset original (Box, MagSafe charger, kabel)\n- Layar mulus tanpa dead pixel / stag\n- Keyboard dan trackpad berfungsi 100%\n- Alasan jual: sudah upgrade ke laptop kantor.",
                'price' => 8650000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1517336714731-489689fd1ca8?auto=format&fit=crop&w=800&q=80',
                    'https://images.unsplash.com/photo-1611186871348-b1ce696e52c9?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Elektronik',
                'user_index' => 1,
                'title' => 'Monitor LG 24 Inch IPS 75Hz Full HD (24MK600M)',
                'description' => "Monitor LG 24 inch panel IPS jernih dan bezel tipis (Borderless design).\n\n- Refresh rate 75Hz support AMD FreeSync\n- Port: 2x HDMI, 1x D-Sub, Headphone out\n- Tidak ada garis / dead pixel sama sekali\n- Kelengkapan: Unit monitor, stand, adaptor power, kabel HDMI\n- Siap COD cek sepuasnya di rumah.",
                'price' => 1150000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1527443224154-c4a3942d3acf?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Elektronik',
                'user_index' => 2,
                'title' => 'Keychron K2 Wireless Mechanical Keyboard RGB',
                'description' => "Mechanical keyboard compact 75% Keychron K2 Version 2.\n\n- Switch Gateron Brown (enak buat ngetik & gaming santai)\n- RGB Backlight banyak mode\n- Support Bluetooth up to 3 devices + kabel Type-C\n- Kompatibel Windows, Mac, iOS, Android\n- Keycaps bawaan mulus no shining.",
                'price' => 950000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1587829741301-dc798b83add3?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Elektronik',
                'user_index' => 3,
                'title' => 'Sony WH-1000XM4 Wireless Noise Cancelling Headphones',
                'description' => "Headphone wireless flagship Sony WH-1000XM4 warna Hitam.\n\n- Fitur Active Noise Cancelling (ANC) terbaik di kelasnya\n- Baterai awet tahan hingga 30 jam\n- Earpad masih empuk dan bersih\n- Lengkap hardcase, kabel audio jack, dan kabel charger Type-C.",
                'price' => 2650000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // RUMAH TANGGA
            [
                'category' => 'Rumah Tangga',
                'user_index' => 4,
                'title' => 'Air Fryer Philips HD9252 Digital Essential 4.1L',
                'description' => "Air fryer digital Philips kapasitas 4.1 Liter. Barang BARU hadiah pernikahan, belum pernah dipakai sama sekali (hanya buka kardus cek kelengkapan).\n\n- Layar sentuh digital dengan 7 preset masak\n- Teknologi Rapid Air untuk hasil renyah bebas minyak\n- Buku resep dan buku panduan lengkap.",
                'price' => 890000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1584269600464-37b1b58a9fe7?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Rumah Tangga',
                'user_index' => 5,
                'title' => 'Meja Kerja Minimalis Kayu Jati Belanda 120x60cm',
                'description' => "Meja kerja / meja belajar kokoh bahan kayu solid Jati Belanda dengan rangka besi hollow hitam antikarat.\n\n- Ukuran panjang 120cm, lebar 60cm, tinggi 75cm\n- Finishing vernis natural halus tahan gores & air\n- Kondisi mulus bebas rayap dan tidak goyang.",
                'price' => 450000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1518455027359-f3f8164ba6bd?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Rumah Tangga',
                'user_index' => 0,
                'title' => 'Dispenser Galon Bawah Modena DD 67 S Silver',
                'description' => "Dispenser air galon bawah Modena warna elegan Silver Metallic.\n\n- 3 kran: Panas, Dingin, Normal\n- Tangki stainless steel food grade anti karat\n- Child lock pengaman air panas aman untuk anak kecil\n- Pemakaian sekitar 1 tahun, kondisi normal dan bersih terawat.",
                'price' => 1350000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1544816155-12df9643f363?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // MAKANAN & MINUMAN
            [
                'category' => 'Makanan & Minuman',
                'user_index' => 1,
                'title' => 'Sambal Cumi Asin Pedas Gurih Rumahan (Toples 200gr)',
                'description' => "Sambal cumi asin khas rumahan buatan sendiri. Dibuat fresh setiap hari tanpa bahan pengawet kimia.\n\n- Potongan cumi asin melimpah dan tidak alot\n- Rasa pedas nampol dipadu rempah bawang segar\n- Daya tahan 1 bulan di dalam kulkas\n- Cocok untuk santapan sehari-hari dengan nasi hangat.",
                'price' => 35000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1563379091339-03b21ab4a4f8?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Makanan & Minuman',
                'user_index' => 2,
                'title' => 'Kopi Robusta Lampung Petik Merah Asli 250gr (Bubuk / Biji)',
                'description' => "Kopi asli perkebunan Lampung Barat proses natural petik merah.\n\n- Roast profile: Medium to Dark (Body tebal, aroma cokelat & kacang)\n- Tersedia dalam bentuk Biji matang atau Bubuk Halus / Sedang\n- Kemasan alufoil zipper valve kedap udara.",
                'price' => 45000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1559056199-641a0ac8b55e?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Makanan & Minuman',
                'user_index' => 3,
                'title' => 'Fudgy Brownies Shiny Crust Homemade Topping Mix (20x10cm)',
                'description' => "Brownies panggang premium dengan tekstur fudgy lumer di dalam dan shiny crust renyah di atas.\n\n- Topping: Almond slice, Chocochips, dan Keju parut\n- Menggunakan cokelat batang kualitas impor & butter premium\n- Freshly baked made by order.",
                'price' => 60000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1606313564200-e75d5e30476c?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // PAKAIAN & MODE
            [
                'category' => 'Pakaian & Mode',
                'user_index' => 4,
                'title' => 'Jaket Parka Uniqlo Original Blocktech Warna Navy Size L',
                'description' => "Jaket parka Uniqlo teknologi Blocktech tahan angin dan percikan air ringan (Water Repellent).\n\n- Warna Navy (Biru Gelap)\n- Size L (Lebar dada 58cm, Panjang 70cm)\n- Resleting dan velcro masih rekat kuat\n- Kondisi 92% jarang dipakai.",
                'price' => 290000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1551028719-00167b16eac5?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Pakaian & Mode',
                'user_index' => 5,
                'title' => 'Sneakers Ventela Public Low Black Natural Size 42',
                'description' => "Sepatu lokal hits Ventela Public Low warna Black Natural.\n\n- Size 42 (Insole 27.1 cm)\n- Insole ultralite foam empuk tidak bikin pegal\n- Sol tapak masih tebal tidak licin\n- Lengkap dengan box original bawaan.",
                'price' => 175000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1525966222134-fcfa99b8ae77?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Pakaian & Mode',
                'user_index' => 0,
                'title' => 'Tas Ransel Eiger Diario 25L Laptop Compartment',
                'description' => "Backpack Eiger original kapasitas 25 Liter muat laptop hingga 15.6 inch.\n\n- Bahan Cordura kuat dan jahitan rapi\n- Terdapat rain cover pelindung hujan di bagian saku bawah\n- Busa punggung empuk nyaman dipakai harian.",
                'price' => 310000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1553062407-98eeb64c6a62?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // HOBI & HIBURAN
            [
                'category' => 'Hobi & Hiburan',
                'user_index' => 1,
                'title' => 'Gitar Akustik Yamaha F310 Natural Original',
                'description' => "Gitar akustik legendaris Yamaha F310 Original suara nyaring dan resonansi hangat.\n\n- Kayu Spruce top, back & side Meranti\n- Action senar ceper nyaman dimainkan tidak sakit di jari\n- Sudah dipasang senar baru D'Addario 0.10\n- Bonus: Softcase gigbag busa tebal & pick gitar.",
                'price' => 880000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1510915361894-db8b60106cb1?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Hobi & Hiburan',
                'user_index' => 2,
                'title' => 'Tanaman Hias Monstera Deliciosa Daun Pecah Rimbun',
                'description' => "Tanaman hias indoor/outdoor Monstera Deliciosa sehat dan subur dengan 5 daun sudah pecah sempurna.\n\n- Sudah ditanam di pot terakota estetik ukuran diameter 25cm\n- Media tanam porous siap pajang\n- Sangat cantik untuk dekorasi ruang tamu atau teras rumah.",
                'price' => 125000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1614594975525-e45190c55d0b?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // KENDARAAN & OTOMOTIF
            [
                'category' => 'Kendaraan & Otomotif',
                'user_index' => 3,
                'title' => 'Helm KYT TT Course Dalla Porta Replica Size L',
                'description' => "Helm Full Face KYT TT Course motif pembalap Dalla Porta.\n\n- Size L (Busa masih padat, wangi bebas bau apek)\n- Dilengkapi Visor Flat Clear original + Dark Smoke aftermarket\n- Sistem pengunci tali Double D-Ring standar balap\n- Kelengkapan: Helm, Sarung kain KYT, Dus.",
                'price' => 950000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1558981806-ec527fa84c39?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Kendaraan & Otomotif',
                'user_index' => 4,
                'title' => 'Pompa Ban Elektrik Portable Xiaomi Mi Air Pump 1S',
                'description' => "Kompresor angin elektrik portable Xiaomi 1S praktis untuk motor, mobil, sepeda, hingga bola.\n\n- Tekanan otomatis berhenti sesuai settingan PSI/Bar\n- Layar digital LED terang & lampu senter darurat\n- Baterai rechargeable via port USB Type-C\n- Fungsi normal 100% lengkap nozzle adapter.",
                'price' => 320000,
                'condition' => 'bekas',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1578844251758-2f71da64c96f?auto=format&fit=crop&w=800&q=80',
                ],
            ],

            // JASA & LAINNYA
            [
                'category' => 'Jasa & Lainnya',
                'user_index' => 5,
                'title' => 'Jasa Cuci & Service AC Split Rumah (0.5 PK - 2 PK)',
                'description' => "Layanan cuci AC profesional untuk area komplek & sekitar.\n\n- Cuci indoor unit, outdoor unit, pembersihan filter, dan cek freon\n- Pengerjaan rapi, bersih tanpa bocor air di lantai\n- Teknisi berpengalaman & ramah\n- Garansi servis 30 hari.",
                'price' => 75000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?auto=format&fit=crop&w=800&q=80',
                ],
            ],
            [
                'category' => 'Jasa & Lainnya',
                'user_index' => 0,
                'title' => 'Jasa Desain Banner & Logo UMKM Warga Cepat 24 Jam',
                'description' => "Membantu tetangga dan pelaku usaha lokal untuk desain kebutuhan promosi:\n\n- Desain Logo, Banner Spanduk, Label Kemasan Makanan, Brosur Promosi\n- File master siap cetak (PDF, AI, CDR) + JPEG/PNG HD\n- Revisi minor hingga 3x sampai puas\n- Hasil cepat dalam 1-2 hari kerja.",
                'price' => 150000,
                'condition' => 'baru',
                'status' => 'tersedia',
                'images' => [
                    'https://images.unsplash.com/photo-1626785774573-4b799315345d?auto=format&fit=crop&w=800&q=80',
                ],
            ],
        ];

        // 5. Masukkan ke database
        foreach ($products as $p) {
            $cat = $categories[$p['category']] ?? null;
            $user = $users[$p['user_index']] ?? $adminUser;

            if (! $cat) {
                continue;
            }

            // Cari atau buat listing berdasarkan title dan user_id
            $listing = Listing::firstOrCreate(
                [
                    'title' => $p['title'],
                    'user_id' => $user->id,
                ],
                [
                    'community_id' => $community->id,
                    'category_id' => $cat->id,
                    'description' => $p['description'],
                    'price' => $p['price'],
                    'condition' => $p['condition'],
                    'status' => $p['status'],
                ]
            );

            // Buat gambar untuk listing jika belum ada
            if ($listing->images()->count() === 0) {
                foreach ($p['images'] as $order => $imageUrl) {
                    ListingImage::create([
                        'listing_id' => $listing->id,
                        'image_path' => $imageUrl,
                        'order' => $order,
                    ]);
                }
            }
        }
    }
}
