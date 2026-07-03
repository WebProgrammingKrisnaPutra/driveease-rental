<?php
require_once __DIR__ . '/config/database.php';

try {
    $sql = "CREATE TABLE IF NOT EXISTS routes (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(150) NOT NULL,
        type_label VARCHAR(100) NOT NULL,
        description TEXT NOT NULL,
        distance VARCHAR(100) NOT NULL,
        duration VARCHAR(100) NOT NULL,
        recommended_car VARCHAR(150) NOT NULL,
        fuel_type_link VARCHAR(50) NOT NULL,
        image_url VARCHAR(255) NOT NULL,
        lat DECIMAL(10, 8) NOT NULL,
        lng DECIMAL(11, 8) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $pdo->exec($sql);

    // Insert Default Data
    $check = $pdo->query("SELECT COUNT(*) FROM routes")->fetchColumn();
    if ($check == 0) {
        $insert = "INSERT INTO routes (title, type_label, description, distance, duration, recommended_car, fuel_type_link, image_url, lat, lng) VALUES 
        (
            'Ekspedisi Merbabu & Kaliurang', 
            'Gunung & Alam', 
            'Menelusuri jalanan menanjak menuju kawasan Kaliurang dan lereng Gunung Merbabu. Udara sejuk dan pemandangan hutan pinus akan menemani perjalanan Anda. Rute ini membutuhkan kendaraan yang tangguh dan memiliki torsi besar agar perjalanan tetap nyaman dan aman.', 
            '± 45 KM dari Pusat Kota', 
            '1.5 - 2 Jam perjalanan', 
            'SUV Diesel (Pajero, Fortuner)', 
            'diesel', 
            'https://images.unsplash.com/photo-1542856391-010fb87dcfed?auto=format&fit=crop&w=1000&q=80', 
            -7.6045, 
            110.4402
        ),
        (
            'Yogyakarta Heritage City Tour', 
            'City Tour & Sejarah', 
            'Eksplorasi keindahan kota pelajar mulai dari Tugu Jogja, jalanan sibuk Malioboro, kemegahan Kraton, hingga kawasan bersejarah Kotagede. Menghadapi jalanan kota yang padat, Anda memerlukan kendaraan yang lincah, senyap, dan memiliki kenyamanan suspensi yang tinggi.', 
            'Keliling Kota ± 20 KM', 
            'Half-day (4-6 Jam)', 
            'Sedan / Hatchback Bensin (Civic, City)', 
            'bensin', 
            'https://images.unsplash.com/photo-1625244724108-a53d340b36c2?auto=format&fit=crop&w=1000&q=80', 
            -7.7956, 
            110.3695
        ),
        (
            'Susur Pantai Selatan Gunungkidul', 
            'Pantai & Laut', 
            'Melarikan diri dari hiruk-pikuk kota menuju deretan pantai berpasir putih di Gunungkidul. Mulai dari Pantai Baron, Indrayanti, hingga bukit paralayang Watugupit. Rute panjang yang indah ini akan sangat cocok dinikmati dengan kendaraan eco-friendly yang senyap dan super efisien.', 
            '± 70 KM dari Pusat Kota', 
            '2 - 2.5 Jam perjalanan satu arah', 
            'Hybrid / EV (Ioniq 5, Innova Zenix HEV)', 
            'hybrid', 
            'https://images.unsplash.com/photo-1601584115197-04ecc0da31d7?auto=format&fit=crop&w=1000&q=80', 
            -8.1287, 
            110.5739
        )";
        $pdo->exec($insert);
        echo "Table created and seeded successfully.";
    } else {
        echo "Table exists and already has data.";
    }

} catch (PDOException $e) {
    die("Migration failed: " . $e->getMessage());
}
?>
