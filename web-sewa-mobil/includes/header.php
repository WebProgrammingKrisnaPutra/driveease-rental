<?php
require_once __DIR__ . '/auth_check.php';
// Set base path for assets depending on folder level
$base_url = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '';
?>
<!DOCTYPE html>
<html lang="id" class="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>DriveEase | Premium Car Rental & Luxury Vehicles</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            darkMode: 'class',
            theme: {
                extend: {
                    colors: {
                        darkBg: '#0A0A0A',
                        darkCard: '#161616',
                        goldAccent: '#D4AF37',
                        goldHover: '#c5a028',
                    }
                }
            },
            plugins: [
                tailwind.plugin(function({ addVariant }) {
                    addVariant('light', '.light &');
                })
            ]
        }
    </script>
    
    <!-- Google Fonts & Custom CSS -->
    <link rel="stylesheet" href="<?php echo $base_url; ?>assets/css/style.css">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Leaflet JS (Interactive Map) -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
</head>
<body class="bg-darkBg text-zinc-100 min-h-screen flex flex-col transition-colors duration-300 dark:bg-darkBg dark:text-zinc-100 light:bg-[#FAF9F6] light:text-zinc-900">
    
    <!-- Navigation Bar -->
    <nav class="sticky top-0 z-50 premium-glass border-b border-zinc-800/80 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Logo -->
                <div class="flex-shrink-0 flex items-center">
                    <a href="<?php echo $base_url; ?>index.php" class="flex items-center space-x-2">
                        <span class="text-2xl font-black tracking-wider text-white dark:text-white light:text-zinc-900">
                            DRIVE<span class="text-goldAccent font-extrabold">EASE</span>
                        </span>
                    </a>
                </div>
                
                <!-- Desktop Navigation Menu -->
                <div class="hidden md:flex space-x-8 items-center">
                <a href="<?php echo $base_url; ?>index.php" class="text-sm font-semibold tracking-wide hover:text-goldAccent transition-all duration-200">Home</a>
                
                <a href="<?php echo $base_url; ?>catalog.php" class="text-sm font-semibold tracking-wide hover:text-goldAccent transition-all duration-200">Katalog</a>
                
                <a href="<?php echo $base_url; ?>routes.php" class="text-sm font-semibold tracking-wide hover:text-goldAccent transition-all duration-200">Rute Wisata</a>
                
                <?php if (is_logged_in()): ?>
                    <?php if (get_user_role() === 'admin'): ?>
                        <a href="<?php echo $base_url; ?>admin/index.php" class="text-sm font-semibold text-goldAccent tracking-wide hover:underline">Panel Admin</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>dashboard.php" class="text-sm font-semibold tracking-wide hover:text-goldAccent transition-all duration-200">Dashboard</a>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

                <!-- Theme Toggle & Auth Button -->
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Light / Dark Toggle Switch -->
                    <button class="theme-toggle-btn text-zinc-400 hover:text-goldAccent p-2 rounded-full transition-all duration-200" title="Ganti Tema">
                        <i class="fa-solid fa-circle-half-stroke text-lg"></i>
                    </button>
                    
                    <?php if (is_logged_in()): ?>
                        <div class="flex items-center space-x-4">
                            <span class="text-xs text-zinc-400 font-medium">Hello, <span class="text-white dark:text-white light:text-zinc-900 font-semibold"><?php echo htmlspecialchars($_SESSION['user_name']); ?></span></span>
                            <a href="<?php echo $base_url; ?>logout.php" class="py-2.5 px-5 bg-zinc-800 hover:bg-zinc-700 text-white font-semibold rounded-xl text-xs transition-all duration-200 border border-zinc-700 shadow-md">
                                Keluar
                            </a>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center space-x-3">
                            <a href="<?php echo $base_url; ?>login.php" class="text-xs font-semibold hover:text-goldAccent transition-all duration-200">Masuk</a>
                            <a href="<?php echo $base_url; ?>register.php" class="py-2.5 px-5 bg-goldAccent hover:bg-[#c5a028] text-black font-semibold rounded-xl text-xs transition-all duration-200 shadow-md shadow-goldAccent/10">
                                Daftar Member
                            </a>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mobile Menu Button -->
                <div class="md:hidden flex items-center space-x-4">
                    <button class="theme-toggle-btn text-zinc-400 hover:text-goldAccent p-2" title="Ganti Tema">
                        <i class="fa-solid fa-circle-half-stroke text-lg"></i>
                    </button>
                    <button id="mobile-menu-btn" class="text-zinc-400 hover:text-white focus:outline-none">
                        <i class="fa-solid fa-bars text-xl"></i>
                    </button>
                </div>
            </div>
        </div>

        <!-- Mobile Navigation Menu -->
        <div id="mobile-menu" class="hidden md:hidden premium-glass border-b border-zinc-800 transition-all duration-300">
            <div class="px-4 pt-2 pb-6 space-y-3">
                <a href="<?php echo $base_url; ?>index.php" class="block py-2 text-base font-medium hover:text-goldAccent">Home</a>
                
                <a href="<?php echo $base_url; ?>catalog.php" class="block py-2 text-base font-medium hover:text-goldAccent">Katalog Mobil</a>
                
                <a href="<?php echo $base_url; ?>routes.php" class="block py-2 text-base font-medium hover:text-goldAccent">Rute Wisata</a>
                
                <?php if (is_logged_in()): ?>
                    <?php if (get_user_role() === 'admin'): ?>
                        <a href="<?php echo $base_url; ?>admin/index.php" class="block py-2 text-base font-medium text-goldAccent">Panel Admin</a>
                    <?php else: ?>
                        <a href="<?php echo $base_url; ?>dashboard.php" class="block py-2 text-base font-medium hover:text-goldAccent">Dashboard Saya</a>
                    <?php endif; ?>
                    <hr class="border-zinc-800 my-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-zinc-400">Masuk sebagai <strong><?php echo htmlspecialchars($_SESSION['user_name']); ?></strong></span>
                        <a href="<?php echo $base_url; ?>logout.php" class="py-2 px-4 bg-zinc-850 hover:bg-zinc-800 border border-zinc-700 text-white rounded-lg text-xs font-semibold">
                            Keluar
                        </a>
                    </div>
                <?php else: ?>
                    <hr class="border-zinc-800 my-2">
                    <div class="grid grid-cols-2 gap-3 pt-2">
                        <a href="<?php echo $base_url; ?>login.php" class="py-2.5 text-center bg-zinc-800 hover:bg-zinc-700 border border-zinc-700 text-white rounded-xl text-xs font-semibold">
                            Masuk
                        </a>
                        <a href="<?php echo $base_url; ?>register.php" class="py-2.5 text-center bg-goldAccent hover:bg-[#c5a028] text-black rounded-xl text-xs font-semibold shadow-md">
                            Daftar
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    
    <main class="flex-grow">
