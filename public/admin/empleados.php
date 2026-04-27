<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

// Protected by middleware: only admin can access
if (!has_role('admin')) { auth_required('admin'); }

$msg = '';

// Handle Add/Edit Employee
if (isset($_POST['save_empleado'])) {
    $id = $_POST['empleado_id'] ?? '';
    $primer_nombre = $_POST['primer_nombre'] ?? '';
    $apellido = $_POST['apellido'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $rol = $_POST['rol'] ?? 'admin';
    
    if (!empty($primer_nombre) && !empty($email)) {
        try {
            if (!empty($id)) {
                // Update logic
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    db_execute("UPDATE public.usuarios SET primer_nombre = ?, apellido = ?, email = ?, password = ?, rol = ? WHERE id = ?",
                        [$primer_nombre, $apellido, $email, $hashed, $rol, $id]);
                } else {
                    db_execute("UPDATE public.usuarios SET primer_nombre = ?, apellido = ?, email = ?, rol = ? WHERE id = ?",
                        [$primer_nombre, $apellido, $email, $rol, $id]);
                }
                $msg = 'Datos de operador actualizados.';
            } else {
                // Insert logic
                if (!empty($password)) {
                    $hashed = password_hash($password, PASSWORD_DEFAULT);
                    db_execute("INSERT INTO public.usuarios (primer_nombre, apellido, email, password, rol, estado) VALUES (?, ?, ?, ?, ?, 1)",
                        [$primer_nombre, $apellido, $email, $hashed, $rol]);
                    $msg = 'Operador registrado con éxito.';
                } else {
                    $msg = 'Error: Se requiere contraseña para nuevos operadores.';
                }
            }
        } catch (Exception $e) {
            $msg = 'Error: El correo electrónico ya podría estar en uso.';
        }
    }
}

// Handle Delete Employee
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    if ($delete_id != ($_SESSION['admin_id'] ?? 0)) {
        db_execute("DELETE FROM public.usuarios WHERE id = ?", [$delete_id]);
        $msg = 'Acceso de operador revocado.';
    } else {
        $msg = 'SYS_ERROR: No puedes auto-eliminarte del sistema.';
    }
}

$empleados = db_get_all("SELECT * FROM public.usuarios ORDER BY creado_en DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">ESTACIÓN_DE_PERSONAL</h2>
    <button onclick="openEmployeeModal()" class="cyber-btn" style="padding: 15px 35px; background: var(--primary); color: white; border: none; cursor: pointer; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%); font-weight: 800;">+ NUEVO OPERADOR</button>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--primary);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">REGISTRO_BIO_PERSONAL</h3>
    <table class="table-admin">
        <thead>
            <tr>
                <th>OPERADOR</th>
                <th>CREDEDNCIALES</th>
                <th>ESTADO_NÚCLEO</th>
                <th>GESTIÓN_ACCESO</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($empleados as $e): ?>
                <tr>
                    <td>
                        <div style="font-weight: 800; color: white;"><?= strtoupper(htmlspecialchars($e['primer_nombre'])) ?> <?= strtoupper(htmlspecialchars($e['apellido'])) ?></div>
                        <div style="font-size: 0.65rem; color: var(--accent); letter-spacing: 2px;"><?= strtoupper(htmlspecialchars($e['rol'])) ?></div>
                    </td>
                    <td>
                        <div style="color: white; font-family: monospace;"><?= htmlspecialchars($e['email']) ?></div>
                        <div style="font-size: 0.6rem; color: var(--text-secondary);">UID: <?= $e['id'] ?></div>
                    </td>
                    <td>
                        <span style="color: #2ed573; font-size: 0.7rem; font-weight: bold;">● ACTIVO</span>
                    </td>
                    <td>
                        <div style="display: flex; gap: 10px; align-items: center;">
                            <button onclick='openEmployeeModal(<?= json_encode($e) ?>)' class="btn-action" style="color: var(--accent); border: 1px solid var(--accent); padding: 10px; border-radius: 4px; background: transparent; cursor: pointer;">
                                <i data-lucide="user-cog" style="width: 20px;"></i>
                            </button>
                            
                            <?php if ($e['id'] != ($_SESSION['admin_id'] ?? 0)): ?>
                                <a href="?delete=<?= $e['id'] ?>" onclick="return confirm('¿REVOCAR_ACCESO_TOTAL?')" class="btn-action" style="color: var(--primary); border: 1px solid var(--primary); padding: 10px; border-radius: 4px; display: inline-flex;">
                                    <i data-lucide="user-minus" style="width: 20px;"></i>
                                </a>
                            <?php else: ?>
                                <span style="font-size: 0.6rem; color: #555; letter-spacing: 1px;">[ACCESO_ACTUAL]</span>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Modal -->
<div id="modal-employee" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass" style="padding: 40px; border-top: 4px solid var(--primary); max-width: 500px; width: 90%; position: relative; border-radius: 12px; animation: modalIn 0.3s ease-out;">
        <button onclick="closeEmployeeModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
        
        <h3 id="modal-title" style="letter-spacing: 2px; font-weight: 800; color: white; margin-bottom: 30px;">NUEVO_OPERADOR</h3>

        <form method="POST" style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
            <input type="hidden" name="empleado_id" id="empleado_id">
            
            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">NOMBRES</label>
                <input type="text" name="primer_nombre" id="emp_nombre" class="form-control" required placeholder="IDENTIDAD">
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">APELLIDOS</label>
                <input type="text" name="apellido" id="emp_apellido" class="form-control" required placeholder="LINAJE">
            </div>

            <div class="form-group" style="grid-column: span 2;">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">EMAIL_CORP</label>
                <input type="email" name="email" id="emp_email" class="form-control" required placeholder="usuario@huarique.com">
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">NIVEL_ACCESO</label>
                <select name="rol" id="emp_rol" class="form-control">
                    <option value="admin">ADMINISTRADOR</option>
                    <option value="super_admin">SUPER ADMIN</option>
                </select>
            </div>

            <div class="form-group">
                <label style="font-size: 0.65rem; color: var(--accent); letter-spacing: 1px;">NUEVO_PASSWORD</label>
                <input type="password" name="password" id="emp_pass" class="form-control" placeholder="••••••••">
                <p id="pass-hint" style="font-size: 0.5rem; color: var(--text-secondary); margin-top: 5px; display: none;">DEJAR VACÍO PARA MANTENER ACTUAL</p>
            </div>
            
            <button type="submit" name="save_empleado" class="cyber-btn" style="grid-column: span 2; height: 55px; background: var(--primary); color: white; font-weight: 900; letter-spacing: 3px; border: none; cursor: pointer; box-shadow: 0 0 20px rgba(255, 71, 87, 0.3);">
                AUTORIZAR_OPERADOR
            </button>
        </form>
    </div>
</div>

<script>
function openEmployeeModal(data = null) {
    const modal = document.getElementById('modal-employee');
    const title = document.getElementById('modal-title');
    const hint = document.getElementById('pass-hint');
    const passInput = document.getElementById('emp_pass');
    
    document.getElementById('empleado_id').value = '';
    document.getElementById('emp_nombre').value = '';
    document.getElementById('emp_apellido').value = '';
    document.getElementById('emp_email').value = '';
    document.getElementById('emp_rol').value = 'admin';
    passInput.value = '';
    passInput.required = true;
    hint.style.display = 'none';
    
    if (data) {
        title.innerText = 'CONFIGURAR_OPERADOR';
        document.getElementById('empleado_id').value = data.id;
        document.getElementById('emp_nombre').value = data.primer_nombre;
        document.getElementById('emp_apellido').value = data.apellido;
        document.getElementById('emp_email').value = data.email;
        document.getElementById('emp_rol').value = data.rol;
        passInput.required = false;
        hint.style.display = 'block';
    } else {
        title.innerText = 'NUEVO_OPERADOR';
    }
    
    modal.style.display = 'flex';
}

function closeEmployeeModal() {
    document.getElementById('modal-employee').style.display = 'none';
}
</script>

<style>
    @keyframes modalIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    .btn-action:hover { background: rgba(255,255,255,0.05); transform: scale(1.1); box-shadow: 0 0 15px rgba(255,255,255,0.1); }
</style>

<?php include '../../includes/admin/footer.php'; ?>
