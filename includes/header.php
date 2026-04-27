<?php
// Fetch Branding Config
$site_name = 'HUARIQUE';
if (function_exists('db_get_one')) {
    try {
        $conf = db_get_one("SELECT valor FROM public.configuraciones WHERE clave = 'site_name'");
        if ($conf) $site_name = strtoupper($conf['valor']);
    } catch (Exception $e) {}
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $site_name ?> | POLLERÍA & RESTAURANTE</title>
    <meta name="description" content="La mejor pollería tradicional con un toque moderno en Huarique. Pollo a la brasa, carnes y más.">
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;800&family=Montserrat:wght@700;900&display=swap" rel="stylesheet">
    
    <!-- CSS -->
    <link rel="stylesheet" href="css/style.css">
    
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="js/cart.js" defer></script>
</head>
<body>

<!-- Navbar -->
<nav class="navbar" id="navbar">
    <div class="container nav-flex">
        <div class="logo-wrapper" style="display: flex; flex-direction: column;">
            <a href="index.php" class="logo neon-glow" style="text-decoration: none; font-size: 2rem;">
                <?= $site_name ?>
            </a>
            <div style="font-size: 0.5rem; letter-spacing: 3px; color: var(--accent); font-weight: bold; margin-top: -5px;">ESTACIÓN GASTRONÓMICA</div>
        </div>
        
        <ul class="nav-links">
            <li><a href="index.php">INICIO</a></li>
            <li><a href="menu_completo.php">VER MENÚ</a></li>
            <li><a href="#reservas">RESERVAR MESA</a></li>
        </ul>
        
        <div class="nav-actions" style="display: flex; gap: 20px; align-items: center;">
            <!-- Cart Indicator UI -->
            <div class="glass cart-trigger" style="position: relative; padding: 10px; border-radius: 50%; width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; cursor: pointer; border-color: var(--accent);" onclick="window.Cart.showDrawer()">
                <i data-lucide="shopping-cart" style="width: 22px; color: var(--accent);"></i>
                <span class="cart-counter" style="position: absolute; top: -5px; right: -5px; background: var(--primary); color: white; min-width: 24px; height: 24px; border-radius: 50%; display: none; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 800; border: 2px solid var(--dark); box-shadow: 0 0 15px var(--primary-glow);">0</span>
            </div>
            
            <a href="admin/login.php" class="btn btn-primary" style="padding: 10px 20px; font-size: 0.7rem;">ACCESO ADMIN</a>
        </div>
    </div>
</nav>

<!-- Extreme Cyberpunk Cart Drawer -->
<div id="cart-drawer" class="glass-drawer cyber-sidebar" style="position: fixed; right: -480px; top: 0; width: 450px; height: 100vh; z-index: 25000; transition: 0.7s cubic-bezier(0.85, 0, 0.15, 1); padding: 0; display: flex; flex-direction: column; background: #050505; border-left: 3px solid var(--primary); box-shadow: -30px 0 80px rgba(0,0,0,0.95);">
    
    <!-- Cyber Background Patterns -->
    <div class="sidebar-vfx">
        <div class="cyber-grid"></div>
        <div class="cyber-scanner"></div>
    </div>

    <!-- Header UI -->
    <div style="padding: 40px; position: relative; z-index: 5; background: linear-gradient(to bottom, rgba(255, 71, 87, 0.1), transparent);">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 5px;">
            <h2 class="neon-glow glitch" data-text="MI PEDIDO" style="margin: 0; font-size: 1.8rem; letter-spacing: 6px; font-weight: 900; color: var(--primary);">MI PEDIDO</h2>
            <button onclick="window.Cart.hideDrawer()" class="cyber-close" style="background: var(--primary); border: none; color: white; cursor: pointer; padding: 10px; clip-path: polygon(20% 0, 100% 0, 80% 100%, 0% 100%);">
                <i data-lucide="x-circle" style="width: 25px;"></i>
            </button>
        </div>
        <p style="font-family: monospace; font-size: 0.6rem; color: var(--accent); opacity: 0.8; letter-spacing: 1px;">CARRITO DE COMPRAS - POLLERÍA HUARIQUE</p>
    </div>
    
    <!-- Items Unit -->
    <div id="cart-items-list" style="flex: 1; overflow-y: auto; padding: 20px 40px; position: relative; z-index: 5; scrollbar-width: none;">
        <!-- Items will be here -->
    </div>
    
    <!-- Footer Protocol -->
    <div style="padding: 40px; position: relative; z-index: 5; background: #0a0a0a; border-top: 1px solid var(--primary);">
        <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
            <span style="font-family: monospace; font-size: 0.7rem; color: var(--text-secondary);">TOTAL A PAGAR:</span>
            <span id="cart-total" class="neon-glow" style="font-weight: 900; color: var(--primary); font-size: 1.8rem;">S/ 0.00</span>
        </div>
        <p style="font-family: monospace; font-size: 0.55rem; color: var(--accent); margin-bottom: 25px;">PAGO SEGURO CON MERCADO PAGO</p>
        
        <a href="checkout.php" class="cyber-btn" style="width: 100%; text-align: center; height: 65px; display: flex; align-items: center; justify-content: center; background: var(--primary); color: white; font-weight: 900; font-size: 1rem; letter-spacing: 4px; text-transform: uppercase; clip-path: polygon(0 0, 90% 0, 100% 30%, 100% 100%, 10% 100%, 0 70%); box-shadow: 0 0 20px var(--primary-glow); border: none; cursor:pointer;">
            PAGAR AHORA
        </a>
    </div>
</div>

<style>
    .cyber-sidebar { overflow: hidden; }
    .sidebar-vfx { position: absolute; top: 0; left: 0; width: 100%; height: 100%; pointer-events: none; opacity: 0.15; }
    .cyber-grid { 
        width: 100%; height: 100%; 
        background-image: radial-gradient(var(--primary) 1px, transparent 0);
        background-size: 30px 30px;
    }
    .cyber-scanner {
        position: absolute; top: 0; left: 0; width: 100%; height: 100%;
        background: linear-gradient(to bottom, transparent, var(--primary), transparent);
        height: 10%; opacity: 0.2; animation: scanLine 8s linear infinite;
    }
    @keyframes scanLine { from { top: -10%; } to { top: 110%; } }
    #cart-drawer.active { right: 0 !important; }
    .cyber-btn:hover { background: #ff2a3b; box-shadow: 0 0 40px var(--primary); transform: translate(-2px, -2px); }
    .cyber-close:hover { background: var(--accent); color: var(--dark); }
</style>

<script>
    document.addEventListener('DOMContentLoaded', () => {
        if(typeof lucide !== 'undefined') lucide.createIcons();
        console.log("Header: UI Icons Transmitted.");
    });
</script>

<style>
    #cart-drawer.active { right: 0 !important; }
    .glass-drawer { clip-path: none !important; } /* Disable global clip-path for drawer */
    .cart-trigger:hover { border-color: var(--primary); box-shadow: 0 0 25px var(--primary-glow); transform: scale(1.1); }
</style>
