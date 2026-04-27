<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

// Protected by middleware
if (!has_role('admin')) { auth_required('admin'); }

$msg = '';

// Handle Add/Edit Coupon
if (isset($_POST['save_coupon'])) {
    $id = $_POST['coupon_id'] ?? '';
    $codigo = strtoupper($_POST['codigo']);
    $tipo = $_POST['tipo'];
    $valor = $_POST['valor'];
    $expiracion = $_POST['expiracion'] ? $_POST['expiracion'] . ' 23:59:59' : null;
    $limite = $_POST['limite'] ?: 100;
    $activo = isset($_POST['activo']) ? 1 : 0;

    if (!empty($id)) {
        // Update
        db_execute("UPDATE public.cupones SET codigo = ?, tipo_descuento = ?, valor = ?, fecha_expiracion = ?, limite_uso = ?, activo = ? WHERE id = ?", 
                   [$codigo, $tipo, $valor, $expiracion, $limite, $activo, $id]);
        $msg = "CÓDIGO_PROMOCIONAL '$codigo' ACTUALIZADO.";
    } else {
        // Insert
        db_execute("INSERT INTO public.cupones (codigo, tipo_descuento, valor, fecha_expiracion, limite_uso, activo) VALUES (?, ?, ?, ?, ?, ?)", 
                   [$codigo, $tipo, $valor, $expiracion, $limite, $activo]);
        $msg = "NUEVA_PROMOCIÓN '$codigo' ACTIVADA EN SISTEMA.";
    }
}

if (isset($_GET['delete'])) {
    db_execute("DELETE FROM public.cupones WHERE id = ?", [$_GET['delete']]);
    $msg = "PROTOCOLO_ELIMINACIÓN_COMPLETO: CUPÓN REMOVIDO.";
}

$cupones = db_get_all("SELECT * FROM public.cupones ORDER BY creado_en DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">ESTACIÓN_DE_PROMOCIONES</h2>
    <button onclick="openCouponModal()" class="cyber-btn" style="padding: 15px 35px; background: var(--primary); color: white; border: none; cursor: pointer; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%); font-weight: 800;">+ NUEVO CUPÓN</button>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--accent);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">MATRIZ_CUPONES_HUARIQUE</h3>
    <table class="table-admin">
        <thead>
            <tr>
                <th>CÓDIGO_LLAVE</th>
                <th>TIPO_VALOR</th>
                <th>RATIO_USO</th>
                <th>EXPIRACIÓN</th>
                <th>ESTADO_NÚCLEO</th>
                <th>GESTIÓN</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($cupones as $c): ?>
                <tr>
                    <td style="font-weight: 800; color: var(--accent); letter-spacing: 1px;"><?= $c['codigo'] ?></td>
                    <td>
                        <span style="color: white; font-weight: 700;"><?= $c['tipo_descuento'] == 'porcentaje' ? $c['valor'].'%' : 'S/ '.$c['valor'] ?></span>
                        <div style="font-size: 0.6rem; color: var(--text-secondary);"><?= strtoupper($c['tipo_descuento']) ?></div>
                    </td>
                    <td>
                        <div style="font-size: 0.75rem; color: #eee;"><?= $c['usos_actuales'] ?> <span style="color: #666;">/</span> <?= $c['limite_uso'] ?></div>
                        <div style="width: 100px; height: 3px; background: rgba(255,255,255,0.05); margin-top: 5px; border-radius: 2px; overflow: hidden;">
                            <?php $prog = ($c['usos_actuales'] / $c['limite_uso']) * 100; ?>
                            <div style="width: <?= min(100, $prog) ?>%; height: 100%; background: var(--accent);"></div>
                        </div>
                    </td>
                    <td style="font-size: 0.75rem; color: var(--text-secondary);">
                        <?= $c['fecha_expiracion'] ? date('d/m/Y', strtotime($c['fecha_expiracion'])) : '♾ PERMANENTE' ?>
                    </td>
                    <td>
                        <?php 
                            $is_expired = $c['fecha_expiracion'] && strtotime($c['fecha_expiracion']) < time();
                            $is_full = $c['usos_actuales'] >= $c['limite_uso'];
                            
                            if (!$c['activo']) {
                                echo '<span style="color: #666; font-size: 0.65rem; border: 1px solid #444; padding: 2px 6px; border-radius: 3px;">DESACTIVADO</span>';
                            } elseif ($is_expired) {
                                echo '<span style="color: var(--primary); font-size: 0.65rem; border: 1px solid var(--primary); padding: 2px 6px; border-radius: 3px;">CADUCADO</span>';
                            } elseif ($is_full) {
                                echo '<span style="color: #ffa502; font-size: 0.65rem; border: 1px solid #ffa502; padding: 2px 6px; border-radius: 3px;">AGOTADO</span>';
                            } else {
                                echo '<span style="color: #2ed573; font-size: 0.65rem; border: 1px solid #2ed573; padding: 2px 6px; border-radius: 3px;">OPERATIVO</span>';
                            }
                        ?>
                    </td>
                    <td>
                        <div style="display: flex; gap: 10px;">
                            <button onclick='openCouponModal(<?= json_encode($c) ?>)' class="btn-action" style="color: var(--accent); border: 1px solid var(--accent); padding: 8px; border-radius: 4px; background: transparent; cursor: pointer;">
                                <i data-lucide="edit-3" style="width: 18px;"></i>
                            </button>
                            <a href="?delete=<?= $c['id'] ?>" onclick="return confirm('¿ELIMINAR_PROMOCIÓN?')" style="color: var(--primary); border: 1px solid var(--primary); padding: 8px; border-radius: 4px; display: inline-flex;">
                                <i data-lucide="trash-2" style="width: 18px;"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="modal-coupon" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass" style="padding: 40px; border-top: 4px solid var(--accent); max-width: 500px; width: 90%; position: relative; border-radius: 12px; animation: modalIn 0.3s ease-out;">
        <button onclick="closeCouponModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
        
        <h3 id="modal-title" style="letter-spacing: 2px; font-weight: 800; color: white; margin-bottom: 30px;">NUEVO_CUPÓN</h3>

        <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <input type="hidden" name="coupon_id" id="coupon_id">
            
            <div class="form-group" style="grid-column: span 2;">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">CÓDIGO_LLAVE</label>
                <input type="text" name="codigo" id="coupon_codigo" class="form-control" required style="text-transform: uppercase;">
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">TIPO_REDUCCIÓN</label>
                <select name="tipo" id="coupon_tipo" class="form-control">
                    <option value="porcentaje">PORCENTAJE (%)</option>
                    <option value="fijo">MONTO FIJO (S/)</option>
                </select>
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">VALOR_ALGORITMO</label>
                <input type="number" name="valor" id="coupon_valor" class="form-control" step="0.01" required>
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">LÍMITE_USOS</label>
                <input type="number" name="limite" id="coupon_limite" class="form-control">
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">FIN_PROTOCOLO</label>
                <input type="date" name="expiracion" id="coupon_expiracion" class="form-control">
            </div>

            <div class="form-group" style="grid-column: span 2; display: flex; align-items: center; gap: 10px; margin: 10px 0;">
                <input type="checkbox" name="activo" id="coupon_activo" value="1" checked style="width: 20px; height: 20px; accent-color: var(--accent);">
                <label style="font-size: 0.75rem; color: white; cursor: pointer;" for="coupon_activo">CUPÓN_ACTIVO_EN_SISTEMA</label>
            </div>
            
            <button type="submit" name="save_coupon" class="cyber-btn" style="grid-column: span 2; height: 55px; background: var(--accent); color: black; font-weight: 900; letter-spacing: 3px; border: none; cursor: pointer; box-shadow: 0 0 20px rgba(52, 231, 228, 0.25);">
                SINCRONIZAR_PROMOCIÓN
            </button>
        </form>
    </div>
</div>

<script>
function openCouponModal(data = null) {
    const modal = document.getElementById('modal-coupon');
    const title = document.getElementById('modal-title');
    
    document.getElementById('coupon_id').value = '';
    document.getElementById('coupon_codigo').value = '';
    document.getElementById('coupon_tipo').value = 'porcentaje';
    document.getElementById('coupon_valor').value = '';
    document.getElementById('coupon_limite').value = '100';
    document.getElementById('coupon_expiracion').value = '';
    document.getElementById('coupon_activo').checked = true;
    
    if (data) {
        title.innerText = 'EDITAR_PROMOCIÓN';
        document.getElementById('coupon_id').value = data.id;
        document.getElementById('coupon_codigo').value = data.codigo;
        document.getElementById('coupon_tipo').value = data.tipo_descuento;
        document.getElementById('coupon_valor').value = data.valor;
        document.getElementById('coupon_limite').value = data.limite_uso;
        if (data.fecha_expiracion) {
            document.getElementById('coupon_expiracion').value = data.fecha_expiracion.split(' ')[0];
        }
        document.getElementById('coupon_activo').checked = data.activo == 1;
    } else {
        title.innerText = 'NUEVO_CUPÓN';
    }
    
    modal.style.display = 'flex';
}

function closeCouponModal() {
    document.getElementById('modal-coupon').style.display = 'none';
}
</script>

<style>
    @keyframes modalIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
    .btn-action:hover { background: rgba(255,255,255,0.05); transform: translateY(-2px); box-shadow: 0 5px 15px rgba(0,0,0,0.3); }
</style>

<?php include '../../includes/admin/footer.php'; ?>
