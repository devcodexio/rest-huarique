<?php
// Core System Configuration & .env Loader
require_once __DIR__ . '/env_loader.php';
require_once __DIR__ . '/middleware.php';
EnvLoader::load(__DIR__ . '/../.env');

// Database Settings from .env
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_PORT', getenv('DB_PORT') ?: '5432');
define('DB_NAME', getenv('DB_NAME') ?: 'rest-huarique');
define('DB_USER', getenv('DB_USER') ?: 'postgres');
define('DB_PASS', getenv('DB_PASS') ?: 'root');

try {
    $dsn = "pgsql:host=" . DB_HOST . ";port=" . DB_PORT . ";dbname=" . DB_NAME;
    $pdo = new PDO($dsn, DB_USER, DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);

    // --- AUTO-MIGRATE CORES ---
    // This runs on every request but is fast. It ensures the DB has the required columns.
    $check_col = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='pedidos' AND column_name='cupon_id'")->fetch();
    if (!$check_col) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS public.cupones (
            id serial PRIMARY KEY,
            codigo varchar NOT NULL UNIQUE,
            tipo_descuento varchar NOT NULL CHECK (tipo_descuento IN ('porcentaje', 'fijo')),
            valor numeric NOT NULL,
            fecha_expiracion timestamp,
            limite_uso int DEFAULT 100,
            usos_actuales int DEFAULT 0,
            activo boolean DEFAULT true,
            creado_en timestamp DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("ALTER TABLE public.pedidos ADD COLUMN IF NOT EXISTS cupon_id int REFERENCES public.cupones(id)");
        $pdo->exec("ALTER TABLE public.pedidos ADD COLUMN IF NOT EXISTS monto_descuento numeric DEFAULT 0");
        $pdo->exec("ALTER TABLE public.pedidos ALTER COLUMN tipo_pedido SET DEFAULT 'online'");
        $pdo->exec("INSERT INTO public.cupones (codigo, tipo_descuento, valor) VALUES ('HUARIQUE20', 'porcentaje', 20) ON CONFLICT (codigo) DO NOTHING");
    }

    // Auto-migrate newly required features
    $check_dni = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='clientes' AND column_name='dni'")->fetch();
    if (!$check_dni) {
        $pdo->exec("ALTER TABLE public.clientes ADD COLUMN dni varchar");
    }

    // Auto-migrate: correo field in clientes
    $check_correo_cli = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='clientes' AND column_name='correo'")->fetch();
    if (!$check_correo_cli) {
        $pdo->exec("ALTER TABLE public.clientes ADD COLUMN correo varchar");
    }

    $check_estado = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='reservas_mesa' AND column_name='estado_pago'")->fetch();
    if (!$check_estado) {
        $pdo->exec("ALTER TABLE public.reservas_mesa ADD COLUMN estado_pago varchar DEFAULT 'pendiente'");
        $pdo->exec("ALTER TABLE public.reservas_mesa ADD COLUMN payment_id varchar");
        $pdo->exec("ALTER TABLE public.reservas_mesa ADD COLUMN monto_adelanto numeric DEFAULT 25.00");
    }

    $check_res_status = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='reservas_mesa' AND column_name='estado_reserva'")->fetch();
    if (!$check_res_status) {
        $pdo->exec("ALTER TABLE public.reservas_mesa ADD COLUMN estado_reserva varchar DEFAULT 'pendiente' CHECK (estado_reserva IN ('pendiente', 'confirmada', 'cancelada'))");
    }

    $check_pago_online = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='pedidos' AND column_name='estado_pago_online'")->fetch();
    if (!$check_pago_online) {
        $pdo->exec("ALTER TABLE public.pedidos ADD COLUMN estado_pago_online varchar DEFAULT 'no_pagado' CHECK (estado_pago_online IN ('pagado', 'no_pagado'))");
    }

    $check_config = $pdo->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public' AND table_name='configuraciones'")->fetch();
    if (!$check_config) {
        $pdo->exec("CREATE TABLE IF NOT EXISTS public.configuraciones (
            id serial PRIMARY KEY,
            clave varchar NOT NULL UNIQUE,
            valor text NOT NULL,
            creado_en timestamp DEFAULT CURRENT_TIMESTAMP,
            actualizado_en timestamp DEFAULT CURRENT_TIMESTAMP
        )");
        $pdo->exec("INSERT INTO public.configuraciones (clave, valor) VALUES 
            ('site_name', 'HUARIQUE RESTAURANTE'),
            ('hero_title', 'EL SABOR QUE TRASCIENDE EL TIEMPO'),
            ('hero_subtitle', 'Sabor 2.0: Tradición milenaria, algoritmos de sabor modernos.')
            ON CONFLICT (clave) DO NOTHING");
    }

    $check_star = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='testimonios' AND column_name='calificacion'")->fetch();
    if (!$check_star) {
        $pdo->exec("ALTER TABLE public.testimonios ADD COLUMN calificacion int DEFAULT 5");
    }

    // Auto-migrate: correo field in testimonios
    $check_correo_test = $pdo->query("SELECT column_name FROM information_schema.columns WHERE table_name='testimonios' AND column_name='correo'")->fetch();
    if (!$check_correo_test) {
        $pdo->exec("ALTER TABLE public.testimonios ADD COLUMN correo varchar");
    }

} catch (Throwable $e) {
    error_log("DB_CRITICAL: " . $e->getMessage());
    die("Error de Conexión: " . $e->getMessage());
}

/**
 * Helper to fetch all rows from a query
 */
function db_get_all($query, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll();
}

/**
 * Helper to fetch a single row
 */
function db_get_one($query, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);
    $stmt->execute($params);
    return $stmt->fetch();
}

/**
 * Helper for INSERT/UPDATE/DELETE
 */
function db_execute($query, $params = [])
{
    global $pdo;
    $stmt = $pdo->prepare($query);
    return $stmt->execute($params);
}
?>