<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

// Protected by middleware via header.php (auth_required)
$msg = '';

// Handle Add/Edit Category
if (isset($_POST['save_category'])) {
    $id = $_POST['category_id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    
    if (!empty($nombre)) {
        if (!empty($id)) {
            // Update
            db_execute("UPDATE public.categorias SET nombre = ? WHERE id = ?", [$nombre, $id]);
            $msg = 'Categoría actualizada con éxito.';
        } else {
            // Insert
            db_execute("INSERT INTO public.categorias (nombre) VALUES (?)", [$nombre]);
            $msg = 'Categoría creada en el sistema.';
        }
    }
}

// Handle Delete Category
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        db_execute("DELETE FROM public.categorias WHERE id = ?", [$id]);
        $msg = 'Protocolo de eliminación completado.';
    } catch (Exception $e) {
        $msg = 'ERROR_SYS: No se puede eliminar una categoría con platos vinculados.';
    }
}

$categories = db_get_all("SELECT * FROM public.categorias ORDER BY id DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">GESTIÓN DE CATEGORÍAS</h2>
    <button onclick="openCategoryModal()" class="cyber-btn" style="padding: 15px 35px; background: var(--primary); color: white; border: none; cursor: pointer; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%); font-weight: 800;">+ NUEVA CATEGORÍA</button>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--primary);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">REGISTRO_NÚCLEOS_CARTA</h3>
    <table class="table-admin">
        <thead>
            <tr>
                <th>ID_PROTOCOLO</th>
                <th>IDENTIFICADOR_NÚCLEO</th>
                <th>ESTADO_DATOS</th>
                <th>GESTIÓN_SISTEMA</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($categories as $cat): ?>
                <tr>
                    <td style="font-family: monospace; color: var(--accent);">#CAT_<?= str_pad($cat['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td style="font-weight: 800; color: white;"><?= strtoupper(htmlspecialchars($cat['nombre'])) ?></td>
                    <td><span style="color: #2ed573; font-size: 0.7rem; font-weight: bold;">● SINCRONIZADO</span></td>
                    <td>
                        <div style="display: flex; gap: 10px;">
                            <button onclick='openCategoryModal(<?= json_encode($cat) ?>)' class="btn-edit" style="color: var(--accent); border: 1px solid var(--accent); padding: 10px; border-radius: 4px; background: transparent; cursor: pointer; transition: 0.3s;">
                                <i data-lucide="edit-3" style="width: 20px;"></i>
                            </button>
                            <a href="?delete=<?= $cat['id'] ?>" onclick="return confirm('¿CONFIRMAR_ELIMINACIÓN_PROTOCOLO?')" class="btn-delete" style="color: var(--primary); border: 1px solid var(--primary); padding: 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s;">
                                <i data-lucide="trash-2" style="width: 20px;"></i>
                            </a>
                        </div>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<!-- Category Modal -->
<div id="modal-category" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(10px);">
    <div class="glass" style="padding: 40px; border-top: 4px solid var(--primary); box-shadow: 0 0 60px rgba(255, 71, 87, 0.2); max-width: 450px; width: 90%; position: relative; border-radius: 12px; animation: modalIn 0.3s ease-out;">
        <button type="button" onclick="closeCategoryModal()" style="position: absolute; top: 20px; right: 20px; background: none; border: none; color: var(--text-secondary); font-size: 1.5rem; cursor: pointer;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 30px;">
            <i data-lucide="layers" style="color: var(--primary); width: 24px;"></i>
            <h3 id="modal-title" style="letter-spacing: 2px; font-weight: 800; color: white; margin: 0;">NUEVA CATEGORÍA</h3>
        </div>

        <form id="category-form" method="POST" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <input type="hidden" name="category_id" id="category_id">
            
            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 10px;">NOMBRE_DEL_NÚCLEO</label>
                <input type="text" name="nombre" id="category_nombre" class="form-control" required placeholder="Ej. PARRILLAS..." style="height: 50px; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 0 15px; border-radius: 6px;">
            </div>
            
            <button type="submit" name="save_category" class="cyber-btn" style="width: 100%; height: 55px; background: var(--primary); color: white; font-weight: 900; letter-spacing: 3px; border: none; cursor: pointer; border-radius: 6px; box-shadow: 0 0 20px rgba(255, 71, 87, 0.3);">
                GUARDAR_CAMBIOS
            </button>
        </form>
    </div>
</div>

<script>
function openCategoryModal(data = null) {
    const modal = document.getElementById('modal-category');
    const form = document.getElementById('category-form');
    const title = document.getElementById('modal-title');
    
    form.reset();
    document.getElementById('category_id').value = '';
    
    if (data) {
        title.innerText = 'EDITAR CATEGORÍA';
        document.getElementById('category_id').value = data.id;
        document.getElementById('category_nombre').value = data.nombre;
    } else {
        title.innerText = 'NUEVA CATEGORÍA';
    }
    
    modal.style.display = 'flex';
}

function closeCategoryModal() {
    document.getElementById('modal-category').style.display = 'none';
}
</script>

<style>
    @keyframes modalIn { from { opacity: 0; transform: translateY(-20px); } to { opacity: 1; transform: translateY(0); } }
    .btn-delete:hover { background: var(--primary); color: white !important; box-shadow: 0 0 20px var(--primary-glow); transform: scale(1.1); }
    .btn-edit:hover { background: var(--accent); color: white !important; box-shadow: 0 0 20px rgba(52, 231, 228, 0.4); transform: scale(1.1); }
</style>

<?php include '../../includes/admin/footer.php'; ?>
