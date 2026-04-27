<?php
// Fetch Branding Config
$site_name = 'HUARIQUE';
if (function_exists('db_get_one')) {
    try {
        $conf = db_get_one("SELECT valor FROM public.configuraciones WHERE clave = 'site_name'");
        if ($conf) $site_name = strtoupper($conf['valor']);
    } catch (Exception $e) {}
}

// Fetch footer info
try {
    $footer_phones = db_get_all("SELECT * FROM public.telefonos_restaurante ORDER BY id LIMIT 3");
    $footer_socials = db_get_all("SELECT * FROM public.redes_sociales ORDER BY id");
    $footer_address = db_get_one("SELECT * FROM public.direcciones_restaurante ORDER BY id LIMIT 1");
    $footer_hours = db_get_all("SELECT * FROM public.horarios_restaurante ORDER BY id");
} catch (Exception $e) {
    $footer_phones = []; $footer_socials = []; $footer_address = null; $footer_hours = [];
}
?>
<footer id="contacto">
    <div class="container">
        <div class="footer-grid">
            <div class="footer-col">
                <a href="#" class="logo neon-glow"><?= $site_name ?></a>
                <p style="margin-top: 20px; color: var(--text-secondary);">Donde la tradición se encuentra con la modernidad. El mejor sabor en <?= $site_name ?>.</p>
                <?php if ($footer_address): ?>
                    <p style="margin-top: 15px; font-size: 0.9rem; color: var(--text-primary);">
                        <i data-lucide="map-pin" style="width: 16px; margin-right: 5px;"></i> <?= htmlspecialchars($footer_address['direccion']) ?>
                    </p>
                <?php endif; ?>
            </div>
            
            <div class="footer-col">
                <h4>Menú Rápido</h4>
                <ul class="footer-links">
                    <li><a href="index.php">Inicio</a></li>
                    <li><a href="menu_completo.php">Nuestro Menú</a></li>
                    <li><a href="#reservas">Reservaciones</a></li>
                    <li><a href="admin/login.php">Acceso Admin</a></li>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Horarios</h4>
                <ul class="footer-links" style="color: var(--text-secondary);">
                    <?php if (empty($footer_hours)): ?>
                        <li>Lunes - Domingo: 12:00 PM - 10:00 PM</li>
                    <?php else: ?>
                        <?php foreach ($footer_hours as $h): ?>
                            <li><?= htmlspecialchars($h['horario']) ?></li>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-col">
                <h4>Síguenos</h4>
                <div style="display: flex; gap: 15px; margin-top: 15px;">
                    <?php if (empty($footer_socials)): ?>
                        <a href="#" class="glass" style="padding: 10px; border-radius: 50%;"><i data-lucide="facebook"></i></a>
                    <?php else: ?>
                        <?php foreach ($footer_socials as $s): ?>
                            <a href="https://<?= htmlspecialchars($s['red_social']) ?>.com/<?= htmlspecialchars($s['usuario']) ?>" class="glass" style="padding: 12px; border-radius: 50%; color: var(--primary);">
                                <i data-lucide="<?= htmlspecialchars($s['red_social']) ?>"></i>
                            </a>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="footer-bottom" style="display: flex; justify-content: space-between; align-items: center; border-top: 1px solid var(--glass-border); padding-top: 30px; flex-wrap: wrap; gap: 20px;">
            <p>&copy; <?= date('Y') ?> <?= $site_name ?>. <span style="color: var(--primary);">Sabor Futurista</span>. Todos los derechos reservados.</p>
            <div style="display: flex; gap: 20px; font-size: 0.75rem;">
                <a href="terminos.php" style="color: var(--text-secondary); text-decoration: none;">TÉRMINOS Y CONDICIONES GLOBALES</a>
                <a href="politicas.php" style="color: var(--text-secondary); text-decoration: none;">TEXTOS LEGALES Y POLÍTICAS</a>
            </div>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    // Initialize Lucide icons
    lucide.createIcons();

    // Navbar scroll effect
    window.addEventListener('scroll', function() {
        const navbar = document.getElementById('navbar');
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    });

    // Smooth scroll for anchors
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            document.querySelector(this.getAttribute('href')).scrollIntoView({
                behavior: 'smooth'
            });
        });
    });
</script>

</body>
</html>
