<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

$msg = '';

// Handle Status Update & Email Notification
if (isset($_GET['status']) && isset($_GET['id'])) {
    $status = $_GET['status'];
    $id = intval($_GET['id']);
    
    // Update DB
    db_execute("UPDATE public.reservas_mesa SET estado_reserva = ? WHERE id = ?", [$status, $id]);
    $msg = 'Estado de reserva actualizado.';

    // Send Confirmation Email
    if ($status === 'confirmada') {
        try {
            require_once '../../includes/mailer.php';
            $res = db_get_one("SELECT * FROM public.reservas_mesa WHERE id = ?", [$id]);
            if ($res && !empty($res['email'])) {
                $body = get_reservation_email_template($res);
                if (send_huarique_email($res['email'], "RESERVA CONFIRMADA - {$site_name}", $body)) {
                    $msg .= ' Notificación enviada al cliente.';
                }
            }
        } catch (Exception $e) {
            error_log("Reservation email error: " . $e->getMessage());
        }
    }
}

// Handle Delete Reservation
if (isset($_GET['delete'])) {
    db_execute("DELETE FROM public.reservas_mesa WHERE id = ?", [$_GET['delete']]);
    $msg = 'Registro de reserva eliminado.';
}

// Only show paid reservations (or all if you prefer, but usually paid ones are the valid ones in this system)
$reservations = db_get_all("SELECT * FROM public.reservas_mesa ORDER BY fecha DESC, hora DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">ESTACIÓN_DE_RESERVAS</h2>
    <div style="font-size: 0.7rem; color: var(--text-secondary); font-family: monospace;">
        PENDIENTES: <span style="color: var(--primary); font-weight: bold;"><?= count(array_filter($reservations, fn($r) => $r['estado_reserva'] == 'pendiente')) ?></span>
    </div>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--accent);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">REGISTRO_CRONOLÓGICO_MESAS</h3>
    
    <div style="overflow-x: auto;">
        <table class="table-admin" style="min-width: 1100px;">
            <thead>
                <tr>
                    <th>IDENTIFICADOR_CLIENTE</th>
                    <th>CRONOGRAMA</th>
                    <th>QUÓRUM</th>
                    <th>ESTADO_PAGO</th>
                    <th>ESTADO_RESERVA</th>
                    <th>GESTIÓN_FLUJO</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($reservations)): ?>
                    <tr><td colspan="6" style="text-align: center; padding: 50px; color: var(--text-secondary);">SIN_RESERVAS_REGISTRADAS</td></tr>
                <?php else: ?>
                    <?php foreach ($reservations as $res): ?>
                        <tr>
                            <td>
                                <div style="font-weight: 800; color: white;"><?= strtoupper(htmlspecialchars($res['nombre'])) ?></div>
                                <div style="font-size: 0.65rem; color: var(--accent);">DNI: <?= htmlspecialchars($res['dni']) ?></div>
                                <div style="font-size: 0.6rem; color: var(--text-secondary);"><?= htmlspecialchars($res['email']) ?></div>
                            </td>
                            <td>
                                <div style="color: white; font-weight: bold;"><?= date('d/m/Y', strtotime($res['fecha'])) ?></div>
                                <div style="font-size: 0.75rem; color: var(--accent);"><?= date('H:i', strtotime($res['hora'])) ?></div>
                            </td>
                            <td style="font-weight: 800; color: var(--primary);"><?= $res['cantidad_personas'] ?> PER.</td>
                            <td>
                                <?php if ($res['estado_pago'] === 'pagado'): ?>
                                    <span style="color: #2ed573; font-size: 0.7rem; font-weight: bold;">● PAGADO (S/ <?= number_format($res['monto_adelanto'], 2) ?>)</span>
                                <?php else: ?>
                                    <span style="color: var(--primary); font-size: 0.7rem; font-weight: bold;">○ PENDIENTE</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php 
                                    $st = $res['estado_reserva'] ?? 'pendiente';
                                    $st_color = '#ffa502';
                                    if ($st === 'confirmada') $st_color = '#2ed573';
                                    if ($st === 'cancelada') $st_color = '#ff4757';
                                ?>
                                <span style="color: <?= $st_color ?>; font-size: 0.75rem; font-weight: 900; letter-spacing: 1px; border: 1px solid <?= $st_color ?>; padding: 4px 10px; border-radius: 4px;">
                                    <?= strtoupper($st) ?>
                                </span>
                            </td>
                            <td>
                                <div style="display: flex; gap: 8px;">
                                    <?php if ($st === 'pendiente'): ?>
                                        <a href="?status=confirmada&id=<?= $res['id'] ?>" class="btn-action" title="CONFIRMAR" style="color: #2ed573; border: 1px solid #2ed573; padding: 8px; border-radius: 4px; transition: 0.3s;">
                                            <i data-lucide="check-circle" style="width: 18px;"></i>
                                        </a>
                                    <?php endif; ?>
                                    <a href="?status=cancelada&id=<?= $res['id'] ?>" class="btn-action" title="CANCELAR" style="color: var(--primary); border: 1px solid var(--primary); padding: 8px; border-radius: 4px; transition: 0.3s;">
                                        <i data-lucide="x-circle" style="width: 18px;"></i>
                                    </a>
                                    <a href="?delete=<?= $res['id'] ?>" onclick="return confirm('¿ELIMINAR_REGISTRO?')" class="btn-action" title="ELIMINAR" style="color: #888; border: 1px solid #444; padding: 8px; border-radius: 4px; transition: 0.3s;">
                                        <i data-lucide="trash-2" style="width: 18px;"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<style>
    .btn-action:hover { background: rgba(255,255,255,0.05); transform: scale(1.1); box-shadow: 0 0 15px rgba(255,255,255,0.1); }
</style>

<?php include '../../includes/admin/footer.php'; ?>
