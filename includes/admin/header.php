<?php
require_once __DIR__ . '/../../config/middleware.php';
auth_required(); // Verifica sesión activa, redirige a login.php si no está autenticado
// Fetch Branding Config
$site_name = 'HUARIQUE';
try {
    $conf = db_get_one("SELECT valor FROM public.configuraciones WHERE clave = 'site_name'");
    if ($conf) $site_name = strtoupper($conf['valor']);
} catch (Exception $e) {}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ESTACIÓN_ALFA - <?= $site_name ?> OS</title>
    <!-- CSS -->
    <link rel="stylesheet" href="../css/style.css">
    <!-- Lucide Icons -->
    <script src="https://unpkg.com/lucide@latest"></script>
    <style>
        :root {
            --sidebar-width: 230px;
            --sidebar-collapsed-width: 62px;
            --sidebar-transition: 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }
        body {
            display: flex;
            background: #020202;
            overflow-x: hidden;
            font-family: 'Inter', sans-serif;
            font-size: 0.85rem;
        }

        /* ── SIDEBAR ─────────────────────────────────── */
        .admin-sidebar {
            width: var(--sidebar-width);
            height: 100vh;
            position: fixed;
            left: 0; top: 0;
            background: rgba(10, 10, 11, 0.98);
            border-right: 2px solid var(--primary);
            padding: 0;
            z-index: 1001;
            box-shadow: 10px 0 30px rgba(0,0,0,1);
            overflow-y: auto;
            overflow-x: hidden;
            transition: width var(--sidebar-transition), padding var(--sidebar-transition);
        }
        .admin-sidebar::-webkit-scrollbar { width: 4px; }
        .admin-sidebar::-webkit-scrollbar-track { background: transparent; }
        .admin-sidebar::-webkit-scrollbar-thumb { background: var(--primary); }

        /* Collapsed state */
        .admin-sidebar.collapsed {
            width: var(--sidebar-collapsed-width);
        }

        /* Branding (logo + label) */
        .sidebar-brand-label,
        .sidebar-section-label,
        .sidebar-link-text {
            transition: opacity var(--sidebar-transition), width var(--sidebar-transition);
            white-space: nowrap;
            overflow: hidden;
        }
        .admin-sidebar.collapsed .sidebar-brand-label,
        .admin-sidebar.collapsed .sidebar-section-label,
        .admin-sidebar.collapsed .sidebar-link-text,
        .admin-sidebar.collapsed .sidebar-header h1 {
            opacity: 0;
            width: 0;
            pointer-events: none;
        }

        /* Toggle button */
        .sidebar-toggle {
            width: 100%;
            background: none;
            border: 1px solid rgba(255,71,87,0.25);
            color: var(--text-secondary);
            cursor: pointer;
            padding: 8px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 15px;
            width: calc(100% - 30px);
            transition: all 0.3s;
        }
        .sidebar-toggle:hover { border-color: var(--primary); color: var(--primary); background: rgba(255,71,87,0.08); }
        .sidebar-toggle svg { width: 18px; height: 18px; flex-shrink: 0; transition: transform var(--sidebar-transition); }
        .admin-sidebar.collapsed .sidebar-toggle svg { transform: rotate(180deg); }

        /* Nav links */
        .admin-main {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 30px 40px;
            min-height: 100vh;
            background-image:
                radial-gradient(circle at top right, rgba(255, 71, 87, 0.05), transparent 60%),
                radial-gradient(circle at bottom left, rgba(0, 245, 255, 0.03), transparent 60%);
            transition: margin-left var(--sidebar-transition);
        }
        .admin-main.collapsed { margin-left: var(--sidebar-collapsed-width); }

        .admin-nav { margin-top: 10px; padding: 0 15px; }
        .admin-nav-link {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 15px;
            border-radius: 4px;
            color: var(--text-secondary);
            margin-bottom: 6px;
            transition: all 0.3s;
            font-size: 0.75rem;
            letter-spacing: 2px;
            font-weight: 700;
            text-transform: uppercase;
            border-left: 0px solid var(--primary);
            position: relative;
            white-space: nowrap;
            overflow: hidden;
        }
        .admin-nav-link:hover, .admin-nav-link.active {
            background: linear-gradient(90deg, rgba(255, 71, 87, 0.1), transparent);
            color: var(--primary);
            border-left: 4px solid var(--primary);
            padding-left: 25px;
        }
        .admin-nav-link svg { width: 18px; height: 18px; flex-shrink: 0; }

        /* Tooltip al estar colapsado */
        .admin-sidebar.collapsed .admin-nav-link {
            padding: 12px;
            justify-content: center;
            border-left: none !important;
        }
        .admin-sidebar.collapsed .admin-nav-link:hover {
            padding-left: 12px;
        }
        .admin-sidebar.collapsed .admin-nav-link::after {
            content: attr(data-label);
            position: absolute;
            left: calc(var(--sidebar-collapsed-width) + 10px);
            background: rgba(20,20,20,0.96);
            color: white;
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.7rem;
            letter-spacing: 1px;
            white-space: nowrap;
            border: 1px solid var(--primary);
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s;
            z-index: 9999;
        }
        .admin-sidebar.collapsed .admin-nav-link:hover::after { opacity: 1; }

        /* ── TOPBAR ──────────────────────────────────── */
        .admin-topbar {
            background: rgba(20, 20, 20, 0.7);
            backdrop-filter: blur(20px);
            padding: 15px 30px;
            border: 1px solid var(--glass-border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            border-radius: 4px;
            border-left: 5px solid var(--primary);
        }
        .admin-topbar h3 { font-size: 1rem; letter-spacing: 3px; margin: 0; color: white; }

        /* ── TABLE ───────────────────────────────────── */
        .table-admin {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0 5px;
            margin-top: 20px;
        }
        .table-admin th {
            text-align: left;
            padding: 10px 15px;
            background: rgba(255,255,255,0.02);
            color: var(--accent);
            font-size: 0.55rem;
            letter-spacing: 1px;
            text-transform: uppercase;
            border: none;
        }
        .table-admin td {
            padding: 10px 15px;
            background: rgba(20, 20, 22, 0.6);
            border-top: 1px solid var(--glass-border);
            border-bottom: 1px solid var(--glass-border);
            font-size: 0.75rem;
            color: white;
            font-family: monospace;
        }
        .table-admin tr td:first-child { border-left: 1px solid var(--glass-border); border-top-left-radius: 6px; border-bottom-left-radius: 6px; }
        .table-admin tr td:last-child  { border-right: 1px solid var(--glass-border); border-top-right-radius: 6px; border-bottom-right-radius: 6px; }

        /* ── ALERTS ──────────────────────────────────── */
        @keyframes alertGlow {
            0%   { box-shadow: 0 0 5px rgba(0, 255, 136, 0.1); opacity: 0; transform: translateY(-10px); }
            100% { box-shadow: 0 0 25px rgba(0, 255, 136, 0.3); opacity: 1; transform: translateY(0); }
        }
        .alert-cyber {
            background: rgba(0, 255, 136, 0.05);
            color: var(--accent);
            padding: 15px 20px;
            border-left: 4px solid var(--accent);
            margin-bottom: 25px;
            font-family: monospace;
            font-size: 0.75rem;
            letter-spacing: 1px;
            animation: alertGlow 0.4s ease-out forwards;
            backdrop-filter: blur(5px);
            display: flex;
            align-items: center;
            gap: 15px;
            border-radius: 0 4px 4px 0;
        }

        .tag { padding: 6px 12px; border-radius: 0; font-size: 0.6rem; letter-spacing: 1px; text-transform: uppercase; }
        .tag-success { background: rgba(0, 255, 136, 0.1); color: var(--accent); border: 1px solid var(--accent); }
        .tag-warning { background: rgba(255,165,2,0.1); color: #ffa502; border: 1px solid rgba(255,165,2,0.4); }
        .tag-error   { background: rgba(255,71,87,0.1);  color: var(--primary); border: 1px solid rgba(255,71,87,0.4); }

        /* Overflow scroll for tables */
        .table-scroll-wrapper { width: 100%; overflow-x: auto; -webkit-overflow-scrolling: touch; }
        .table-scroll-wrapper::-webkit-scrollbar { height: 4px; }
        .table-scroll-wrapper::-webkit-scrollbar-track { background: rgba(255,255,255,0.03); }
        .table-scroll-wrapper::-webkit-scrollbar-thumb { background: var(--primary); border-radius: 2px; }
    </style>
</head>
<body>

<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-header" style="padding: 30px; text-align: center; border-bottom: 1px solid var(--glass-border); position: relative; overflow: hidden;">
        <div style="font-size: 0.6rem; letter-spacing: 4px; color: var(--primary); font-weight: 900; margin-bottom: 5px;">ALPHA_STATION</div>
        <h1 style="font-size: 1.5rem; letter-spacing: 2px; font-weight: 900; margin: 0; color: white; text-shadow: 0 0 15px var(--primary-glow);"><?= $site_name ?></h1>
        <div style="position: absolute; bottom: 0; left: 0; width: 100%; height: 2px; background: linear-gradient(90deg, transparent, var(--primary), transparent);"></div>
    </div>

    <!-- Toggle button -->
    <button class="sidebar-toggle" id="sidebarToggle" title="Colapsar menú">
        <i data-lucide="chevrons-left"></i>
    </button>

    <nav class="admin-nav">
        <a href="index.php"    class="admin-nav-link" data-label="INICIO">
            <i data-lucide="home"></i>
            <span class="sidebar-link-text">INICIO</span>
        </a>
        <a href="reservas.php" class="admin-nav-link" data-label="RESERVAS">
            <i data-lucide="calendar"></i>
            <span class="sidebar-link-text">RESERVAS</span>
        </a>
        <a href="pedidos.php"  class="admin-nav-link" data-label="PEDIDOS">
            <i data-lucide="shopping-cart"></i>
            <span class="sidebar-link-text">PEDIDOS DELIVERY</span>
        </a>
        <a href="menu.php"     class="admin-nav-link" data-label="PLATOS">
            <i data-lucide="book-open"></i>
            <span class="sidebar-link-text">PLATOS</span>
        </a>
        <a href="categorias.php" class="admin-nav-link" data-label="CATEGORÍAS">
            <i data-lucide="list"></i>
            <span class="sidebar-link-text">CATEGORÍAS</span>
        </a>

        <?php if ($_SESSION['admin_role'] === 'admin'): ?>
            <div class="sidebar-section-label" style="font-size: 0.5rem; color: var(--text-secondary); margin: 18px 0 8px 4px; letter-spacing: 2px;">GESTIÓN AVANZADA</div>

            <a href="clientes.php"      class="admin-nav-link" data-label="CLIENTES">
                <i data-lucide="user-check"></i>
                <span class="sidebar-link-text">CLIENTES</span>
            </a>
            <a href="cupones.php"       class="admin-nav-link" data-label="CUPONES">
                <i data-lucide="ticket"></i>
                <span class="sidebar-link-text">CUPONES / PROMOS</span>
            </a>
            <a href="blogs.php"         class="admin-nav-link" data-label="BLOG">
                <i data-lucide="file-text"></i>
                <span class="sidebar-link-text">BLOG / NOTICIAS</span>
            </a>
            <a href="empleados.php"     class="admin-nav-link" data-label="EMPLEADOS">
                <i data-lucide="users"></i>
                <span class="sidebar-link-text">EMPLEADOS</span>
            </a>
            <a href="configuracion.php" class="admin-nav-link" data-label="CONFIG">
                <i data-lucide="settings"></i>
                <span class="sidebar-link-text">CONFIGURACIÓN</span>
            </a>
        <?php endif; ?>

        <div style="margin-top: 50px; border-top: 1px solid var(--glass-border); padding-top: 20px;">
            <a href="logout.php" class="admin-nav-link" data-label="SALIR" style="color: #ff4757;">
                <i data-lucide="log-out"></i>
                <span class="sidebar-link-text">CERRAR SESIÓN</span>
            </a>
        </div>
    </nav>
</aside>

<main class="admin-main" id="adminMain">
    <div class="admin-topbar">
        <h3>ADMINISTRADOR <?= $site_name ?></h3>
        <div style="display: flex; align-items: center; gap: 20px;">
            <div style="text-align: right;">
                <div style="font-size: 0.8rem; font-weight: 900; color: white;">OPERADOR: <?= strtoupper($_SESSION['admin_user']) ?></div>
                <div style="font-size: 0.6rem; color: var(--primary); letter-spacing: 1px;">
                    <?= strtoupper($_SESSION['admin_role'] ?? 'empleado') ?> — SISTEMA ACTIVO
                </div>
            </div>
            <div style="width: 45px; height: 45px; background: var(--primary); clip-path: polygon(20% 0, 100% 0, 80% 100%, 0% 100%); display: flex; align-items: center; justify-content: center; font-weight: bold; color: white; font-size: 1.2rem;">
                <?= strtoupper(substr($_SESSION['admin_user'], 0, 1)) ?>
            </div>
        </div>
    </div>

<script>
(function () {
    const sidebar  = document.getElementById('adminSidebar');
    const main     = document.getElementById('adminMain');
    const toggle   = document.getElementById('sidebarToggle');
    const STORAGE_KEY = 'huarique_sidebar_collapsed';

    // Restore saved state
    if (localStorage.getItem(STORAGE_KEY) === '1') {
        sidebar.classList.add('collapsed');
        main.classList.add('collapsed');
    }

    toggle.addEventListener('click', function () {
        const isCollapsed = sidebar.classList.toggle('collapsed');
        main.classList.toggle('collapsed', isCollapsed);
        localStorage.setItem(STORAGE_KEY, isCollapsed ? '1' : '0');
        // Re-render lucide after toggle (icons may need refresh)
        setTimeout(() => { if (window.lucide) lucide.createIcons(); }, 320);
    });
})();
</script>
