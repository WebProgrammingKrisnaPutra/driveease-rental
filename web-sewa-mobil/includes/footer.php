<?php
$base_url = (strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) ? '../' : '';
?>
    </main>

    <!-- Footer -->
    <footer class="bg-black text-zinc-400 border-t border-zinc-900 pt-16 pb-8 transition-colors duration-300 dark:bg-black dark:text-zinc-400 light:bg-zinc-100 light:text-zinc-650 light:border-zinc-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                <!-- Branding -->
                <div class="space-y-4">
                    <span class="text-xl font-black tracking-wider text-white dark:text-white light:text-zinc-900">
                        DRIVE<span class="text-goldAccent font-extrabold">EASE</span>
                    </span>
                    <p class="text-xs leading-relaxed">
                        Layanan rental mobil premium dan mewah terbaik di Indonesia. Kami menghadirkan kenyamanan berkendara eksklusif dengan pilihan armada bensin, diesel, dan hybrid/EV termutakhir.
                    </p>
                    <div class="flex space-x-4 pt-2">
                        <a href="https://instagram.com/driveease_rental" target="_blank" class="hover:text-goldAccent transition-all duration-200"><i class="fa-brands fa-instagram text-lg"></i></a>
                        <a href="https://facebook.com/driveease.id" target="_blank" class="hover:text-goldAccent transition-all duration-200"><i class="fa-brands fa-facebook text-lg"></i></a>
                        <a href="https://youtube.com/@driveease_auto" target="_blank" class="hover:text-goldAccent transition-all duration-200"><i class="fa-brands fa-youtube text-lg"></i></a>
                        <a href="https://twitter.com/driveease_id" target="_blank" class="hover:text-goldAccent transition-all duration-200"><i class="fa-brands fa-twitter text-lg"></i></a>
                    </div>
                </div>

                <!-- Tipe Mobil -->
                <div>
                    <h4 class="text-white dark:text-white light:text-zinc-900 font-bold text-sm tracking-widest uppercase mb-4">Armada Mobil</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?php echo $base_url; ?>index.php#catalog" class="hover:text-goldAccent transition-all duration-200">Kategori Diesel (SUV/MPV)</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php#catalog" class="hover:text-goldAccent transition-all duration-200">Kategori Bensin (Sedan/City Car)</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php#catalog" class="hover:text-goldAccent transition-all duration-200">Kategori Hybrid & Elektrik (EV)</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php#catalog" class="hover:text-goldAccent transition-all duration-200">Layanan Lepas Kunci</a></li>
                        <li><a href="<?php echo $base_url; ?>index.php#catalog" class="hover:text-goldAccent transition-all duration-200">Layanan Dengan Supir</a></li>
                    </ul>
                </div>

                <!-- Tautan Bantuan -->
                <div>
                    <h4 class="text-white dark:text-white light:text-zinc-900 font-bold text-sm tracking-widest uppercase mb-4">Dukungan</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li><a href="<?php echo $base_url; ?>info.php?tab=terms" class="hover:text-goldAccent transition-all duration-200">Syarat & Ketentuan</a></li>
                        <li><a href="<?php echo $base_url; ?>info.php?tab=privacy" class="hover:text-goldAccent transition-all duration-200">Kebijakan Privasi</a></li>
                        <li><a href="<?php echo $base_url; ?>info.php?tab=faq" class="hover:text-goldAccent transition-all duration-200">FAQ & Bantuan</a></li>
                        <li><a href="<?php echo $base_url; ?>info.php?tab=location" class="hover:text-goldAccent transition-all duration-200">Peta Garasi & Kantor</a></li>
                        <li><a href="<?php echo $base_url; ?>info.php?tab=contact" class="hover:text-goldAccent transition-all duration-200">Hubungi CS WhatsApp</a></li>
                    </ul>
                </div>

                <!-- Kontak Admin -->
                <div>
                    <h4 class="text-white dark:text-white light:text-zinc-900 font-bold text-sm tracking-widest uppercase mb-4">Hubungi Kami</h4>
                    <ul class="space-y-2.5 text-xs">
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-location-dot text-goldAccent"></i>
                            <span>Jl. Mutiara Raya No. 42, Sleman, D.I. Yogyakarta</span>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-phone text-goldAccent"></i>
                            <a href="tel:+6285778297231" class="hover:text-goldAccent transition-all duration-200">+62 857-7829-7231</a>
                        </li>
                        <li class="flex items-center space-x-2">
                            <i class="fa-solid fa-envelope text-goldAccent"></i>
                            <a href="mailto:support@driveease.com" class="hover:text-goldAccent transition-all duration-200">support@driveease.com</a>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Bottom Line -->
            <div class="border-t border-zinc-900 pt-8 flex flex-col md:flex-row items-center justify-between text-xs text-zinc-500 light:border-zinc-200">
                <p>&copy; <?php echo date('Y'); ?> DriveEase Premium Car Rental. Hak Cipta Dilindungi.</p>
                <p class="mt-2 md:mt-0 flex items-center">
                    <span class ="text-goldAccent text-sm">Designed for by krisna ramadhana putra<span><span class="text-white font-bold"> visual excellence & premium performance 
                    <span class="text-goldAccent ml-1.5"><i class="fa-solid fa-gem"></i></span>
                </p>
            </div>
        </div>
    </footer>

    <!-- Global Main Javascript -->
    <script src="<?php echo $base_url; ?>assets/js/main.js"></script>
</body>
</html>
