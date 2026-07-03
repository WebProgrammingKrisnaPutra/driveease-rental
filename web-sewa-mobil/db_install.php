<?php
$host = 'localhost';
$user = 'root';
$pass = ''; // Default XAMPP/WAMP empty password

header('Content-Type: text/html; charset=utf-8');

try {
    // Connect to MySQL server (without selecting a DB)
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Read the SQL script content
    $sqlFile = __DIR__ . '/database.sql';
    if (!file_exists($sqlFile)) {
        throw new Exception("File database.sql tidak ditemukan di: " . $sqlFile);
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute SQL script
    // Note: PDO exec() can run multiple queries if the driver supports it, which MySQL PDO does.
    $pdo->exec($sql);
    
    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Installer | DriveEase</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-[#0A0A0A] text-white flex items-center justify-center min-h-screen p-6'>
        <div class='max-w-md w-full bg-[#161616] border border-zinc-800 p-8 rounded-2xl shadow-2xl text-center'>
            <div class='w-16 h-16 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-emerald-500/20'>
                <svg xmlns='http://www.w3.org/2000/svg' class='h-8 w-8' fill='none' viewBox='0/0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='=M5 13l4 4L19 7' />
                </svg>
            </div>
            <h1 class='text-2xl font-bold tracking-tight text-white mb-2'>Instalasi Sukses!</h1>
            <p class='text-zinc-400 text-sm mb-6'>Database <span class='text-[#D4AF37] font-semibold'>driveease_db</span> telah berhasil dibuat dan diisi dengan data mobil mewah bawaan.</p>
            <div class='bg-zinc-900/50 rounded-xl p-4 mb-6 border border-zinc-800 text-left text-xs space-y-2 text-zinc-300 font-mono'>
                <div>[OK] Membuat Database: driveease_db</div>
                <div>[OK] Membuat Tabel: users, cars, bookings</div>
                <div>[OK] Seeding Data: 2 Akun Pengguna</div>
                <div>[OK] Seeding Data: 7 Mobil Premium</div>
                <div>[OK] Seeding Data: 6 Transaksi Sewa</div>
            </div>
            <a href='index.php' class='inline-block w-full py-3 px-4 bg-[#D4AF37] hover:bg-[#c5a028] text-black font-semibold rounded-xl transition-all duration-200 shadow-lg shadow-[#D4AF37]/10'>
                Masuk ke Landing Page
            </a>
        </div>
    </body>
    </html>";
} catch (Exception $e) {
    echo "
    <!DOCTYPE html>
    <html lang='id'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Database Installer | DriveEase</title>
        <script src='https://cdn.tailwindcss.com'></script>
    </head>
    <body class='bg-[#0A0A0A] text-white flex items-center justify-center min-h-screen p-6'>
        <div class='max-w-md w-full bg-[#161616] border border-zinc-800 p-8 rounded-2xl shadow-2xl text-center'>
            <div class='w-16 h-16 bg-red-500/10 text-red-500 rounded-full flex items-center justify-center mx-auto mb-6 border border-red-500/20'>
                <svg xmlns='http://www.w3.org/2000/svg' class='h-8 w-8' fill='none' viewBox='0 0 24 24' stroke='currentColor'>
                    <path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12' />
                </svg>
            </div>
            <h1 class='text-2xl font-bold tracking-tight text-white mb-2'>Instalasi Gagal</h1>
            <p class='text-zinc-400 text-sm mb-6'>Terjadi kesalahan saat menginisialisasi database.</p>
            <div class='bg-zinc-900/50 rounded-xl p-4 mb-6 border border-zinc-800 text-left text-xs text-red-400 font-mono overflow-x-auto max-h-40'>
                " . htmlspecialchars($e->getMessage()) . "
            </div>
            <p class='text-zinc-500 text-xs mb-6'>Pastikan layanan MySQL (seperti XAMPP/WAMP) sudah aktif dan menggunakan user 'root' tanpa password.</p>
            <button onclick='window.location.reload()' class='w-full py-3 px-4 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold rounded-xl transition-all duration-200 border border-zinc-700'>
                Coba Lagi
            </button>
        </div>
    </body>
    </html>";
}
?>
