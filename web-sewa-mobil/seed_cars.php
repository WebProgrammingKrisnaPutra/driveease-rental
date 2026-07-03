<?php
require_once __DIR__ . '/config/database.php';

$new_cars = [
    // BENSIN - Tambahan
    [
        'name' => 'Xpander Ultimate',
        'brand' => 'Mitsubishi',
        'fuel_type' => 'bensin',
        'capacity' => 7,
        'transmission' => 'automatic',
        'luggage' => 360,
        'price_per_day' => 550000.00,
        'image_url' => 'https://images.unsplash.com/photo-1494976388531-d1058494cdd8?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'MPV keluarga dengan desain sporty crossover, kabin lapang, dan fitur multimedia canggih. Cocok untuk mobilitas harian maupun perjalanan keluarga.'
    ],
    [
        'name' => 'Rush GR Sport',
        'brand' => 'Toyota',
        'fuel_type' => 'bensin',
        'capacity' => 7,
        'transmission' => 'automatic',
        'luggage' => 350,
        'price_per_day' => 480000.00,
        'image_url' => 'https://images.unsplash.com/photo-1503376780353-7e6692767b70?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'SUV kompak berjiwa petualang dengan ground clearance tinggi, cocok untuk jalan perkotaan maupun medan ringan. Mesin 1.5L Dual VVT-i responsif.'
    ],
    [
        'name' => 'Jazz RS CVT',
        'brand' => 'Honda',
        'fuel_type' => 'bensin',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 380,
        'price_per_day' => 400000.00,
        'image_url' => 'https://images.unsplash.com/photo-1542362567-b07e54358753?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Hatchback legendaris dengan ruang bagasi Ultra Seat yang sangat fleksibel. Performa CVT halus dan irit bahan bakar untuk jelajah kota.'
    ],
    [
        'name' => 'Almaz RS Pro',
        'brand' => 'Wuling',
        'fuel_type' => 'bensin',
        'capacity' => 7,
        'transmission' => 'automatic',
        'luggage' => 430,
        'price_per_day' => 600000.00,
        'image_url' => 'https://images.unsplash.com/photo-1619767886558-efdc259cde1a?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'SUV pintar dilengkapi WIND (Wuling Indonesian Command) untuk kontrol suara, ADAS Level 2, panoramic sunroof, dan teknologi autonomous terkini.'
    ],
    // DIESEL - Tambahan
    [
        'name' => 'Fortuner VRZ TRD',
        'brand' => 'Toyota',
        'fuel_type' => 'diesel',
        'capacity' => 7,
        'transmission' => 'automatic',
        'luggage' => 500,
        'price_per_day' => 1500000.00,
        'image_url' => 'https://images.unsplash.com/photo-1519641471654-76ce0107ad1b?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'SUV flagship Toyota dengan mesin 2.8L turbo diesel 204 PS, suspensi empuk namun kokoh, dan tampilan sporty body kit TRD. Raja segala medan.'
    ],
    [
        'name' => 'Triton Athlete',
        'brand' => 'Mitsubishi',
        'fuel_type' => 'diesel',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 520,
        'price_per_day' => 900000.00,
        'image_url' => 'https://images.unsplash.com/photo-1544636331-e26879cd4d9b?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Double cabin tangguh bertenaga diesel 2.4L MIVEC turbo dengan Super Select 4WD-II. Pilihan sempurna untuk medan off-road dan perjalanan jauh.'
    ],
    [
        'name' => 'CX-5 Elite',
        'brand' => 'Mazda',
        'fuel_type' => 'diesel',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 478,
        'price_per_day' => 1350000.00,
        'image_url' => 'https://images.unsplash.com/photo-1580273916550-e323be2ae537?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'SUV premium Jepang dengan filosofi KODO Design, mesin SkyActiv-D diesel super halus, interior Nappa leather, dan Bose premium audio system.'
    ],
    // HYBRID / EV - Tambahan
    [
        'name' => 'Kijang Innova Zenix V HEV',
        'brand' => 'Toyota',
        'fuel_type' => 'hybrid',
        'capacity' => 7,
        'transmission' => 'automatic',
        'luggage' => 420,
        'price_per_day' => 1100000.00,
        'image_url' => 'https://images.unsplash.com/photo-1552519507-da3b142c6e3d?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'MPV premium generasi baru dengan powertrain hybrid elektrifikasi, kabin sunyi, fitur Toyota Safety Sense, dan efisiensi bahan bakar superior.'
    ],
    [
        'name' => 'Neta V',
        'brand' => 'Neta',
        'fuel_type' => 'hybrid',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 350,
        'price_per_day' => 450000.00,
        'image_url' => 'https://images.unsplash.com/photo-1560958089-b8a1929cea89?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Kendaraan listrik mungil nan stylish dengan jarak tempuh hingga 380 km per charge, fast charging 30 menit, dan biaya operasional sangat rendah.'
    ],
    [
        'name' => 'Air ev Long Range',
        'brand' => 'Wuling',
        'fuel_type' => 'hybrid',
        'capacity' => 4,
        'transmission' => 'automatic',
        'luggage' => 200,
        'price_per_day' => 350000.00,
        'image_url' => 'https://images.unsplash.com/photo-1593941707882-a5bba14938c7?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'City car elektrik paling terjangkau dengan jarak tempuh 300 km, desain futuristik kompak, dan nol emisi. Ideal untuk perjalanan kota sehari-hari.'
    ],
    [
        'name' => 'Corolla Cross HEV',
        'brand' => 'Toyota',
        'fuel_type' => 'hybrid',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 440,
        'price_per_day' => 950000.00,
        'image_url' => 'https://images.unsplash.com/photo-1617654112368-307921291f42?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Crossover SUV hybrid premium dengan efisiensi 23 km/liter, Toyota Safety Sense generasi terbaru, power tailgate, dan panoramic moonroof.'
    ],
    // Sedan Mewah - Bensin
    [
        'name' => 'Camry 2.5 V',
        'brand' => 'Toyota',
        'fuel_type' => 'bensin',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 500,
        'price_per_day' => 1200000.00,
        'image_url' => 'https://images.unsplash.com/photo-1621007947382-bb3c3994e3fb?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Sedan eksekutif premium yang menjadi andalan para pebisnis. Mesin Dynamic Force 2.5L, kabin senyap, jok kulit ventilasi, dan JBL 9-speaker system.'
    ],
    [
        'name' => 'City Hatchback RS',
        'brand' => 'Honda',
        'fuel_type' => 'bensin',
        'capacity' => 5,
        'transmission' => 'automatic',
        'luggage' => 290,
        'price_per_day' => 420000.00,
        'image_url' => 'https://images.unsplash.com/photo-1616422285623-13ff0162193c?auto=format&fit=crop&w=800&q=80',
        'status' => 'available',
        'description' => 'Hatchback urban premium dengan Honda Sensing, mesin 1.5L i-VTEC bertenaga namun irit, dan interior digital cockpit bergaya modern.'
    ],
];

$inserted = 0;
$skipped = 0;

try {
    $check_stmt = $pdo->prepare("SELECT COUNT(*) FROM cars WHERE name = ? AND brand = ?");
    $insert_stmt = $pdo->prepare("
        INSERT INTO cars (name, brand, fuel_type, capacity, transmission, luggage, price_per_day, image_url, status, description)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");

    foreach ($new_cars as $car) {
        $check_stmt->execute([$car['name'], $car['brand']]);
        if ((int)$check_stmt->fetchColumn() > 0) {
            $skipped++;
            continue;
        }

        $insert_stmt->execute([
            $car['name'], $car['brand'], $car['fuel_type'], $car['capacity'],
            $car['transmission'], $car['luggage'], $car['price_per_day'],
            $car['image_url'], $car['status'], $car['description']
        ]);
        $inserted++;
    }

    echo "<div style='font-family:monospace;padding:40px;background:#0a0a0a;color:#10b981;'>
        <h2 style='color:#D4AF37;'>✅ Seed Mobil Berhasil!</h2>
        <p>$inserted mobil baru ditambahkan.</p>
        <p>$skipped mobil dilewati (sudah ada).</p>
        <br><a href='catalog.php' style='color:#D4AF37;font-weight:bold;'>→ Buka Katalog Sekarang</a>
    </div>";
} catch (PDOException $e) {
    echo "<div style='font-family:monospace;padding:40px;background:#0a0a0a;color:#ef4444;'>
        <h2>❌ Gagal menambahkan mobil</h2>
        <p>" . htmlspecialchars($e->getMessage()) . "</p>
    </div>";
}
