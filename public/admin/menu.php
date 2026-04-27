<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

// Protected by middleware via header.php (auth_required)
$msg = '';

// Handle Add/Edit Menu
if (isset($_POST['save_menu'])) {
    $id = $_POST['menu_id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $precio = $_POST['precio'] ?? 0;
    $categoria_id = $_POST['categoria_id'] ?? 0;
    $imagen_db = $_POST['imagen_current'] ?? 'https://images.unsplash.com/photo-1598103442097-8b74394b95c6?auto=format&fit=crop&q=80&w=400';

    // Handle File Upload with Cloudinary & Local Fallback
    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $temp_path = $_FILES['imagen_file']['tmp_name'];

        // Try Cloudinary
        require_once '../../libs/cloudinary_handler.php';
        $cloudinary = CloudinaryHandler::getInstance();
        $uploaded_url = $cloudinary->upload($temp_path, 'menu');
        
        if ($uploaded_url) {
            $imagen_db = $uploaded_url;
        } else {
            // Local Fallback
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['imagen_file']['name'], PATHINFO_EXTENSION);
            $file_name = 'menu_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($temp_path, $upload_path)) {
                $imagen_db = 'uploads/' . $file_name;
                $msg = 'SISTEMA: Imagen guardada LOCALMENTE (Cloudinary falló).';
            } else {
                $msg = 'ERROR_UPLOAD: No se pudo guardar la imagen localmente.';
            }
        }
    }

    if (!empty($nombre)) {
        if (!empty($id)) {
            // Update existing
            db_execute("UPDATE public.menus SET nombre = ?, descripcion = ?, precio = ?, imagen = ?, categoria_id = ? WHERE id = ?", 
                       [$nombre, $descripcion, $precio, $imagen_db, $categoria_id, $id]);
            $msg = 'Plato actualizado con éxito.';
        } else {
            // Insert new
            db_execute("INSERT INTO public.menus (nombre, descripcion, precio, imagen, categoria_id) VALUES (?, ?, ?, ?, ?)", 
                       [$nombre, $descripcion, $precio, $imagen_db, $categoria_id]);
            $msg = 'Nuevo plato registrado en la carta.';
        }
    }
}

// Handle Delete Menu
if (isset($_GET['delete'])) {
    db_execute("DELETE FROM public.menus WHERE id = ?", [$_GET['delete']]);
    $msg = 'Plato eliminado de la carta.';
}

$categories = db_get_all("SELECT * FROM public.categorias ORDER BY nombre ASC");
$menus = db_get_all("SELECT m.*, c.nombre as categoria_nombre FROM public.menus m JOIN public.categorias c ON m.categoria_id = c.id ORDER BY c.nombre, m.nombre");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">GESTIÓN DE CARTA</h2>
    <button onclick="openMenuModal()" class="cyber-btn" style="padding: 15px 35px; background: var(--primary); color: white; border: none; cursor: pointer; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%); font-weight: 800;">+ AGREGAR PLATO</button>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--primary);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">REGISTRO_ACTIVO_PRODUCTOS</h3>
    <div style="overflow-x: auto;">
        <table class="table-admin" style="min-width: 900px;">
            <thead>
                <tr>
                    <th>PREVISUALIZACIÓN</th>
                    <th>IDENTIFICADOR</th>
                    <th>NÚCLEO_ASIGNADO</th>
                    <th>VALOR_S/</th>
                    <th>GESTIÓN_SISTEMA</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($menus as $m): 
                    $img_path = $m['imagen'];
                    $display_img = (strpos($img_path, 'http') === 0) ? $img_path : '../' . $img_path;
                ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($display_img) ?>" alt="" style="width: 65px; height: 65px; border: 1px solid var(--accent); padding: 2px; border-radius: 4px; object-fit: cover;" onerror="this.src='https://via.placeholder.com/65'"></td>
                        <td style="font-weight: 800; color: white;"><?= strtoupper(htmlspecialchars($m['nombre'])) ?></td>
                        <td style="color: var(--text-secondary); font-size: 0.8rem;"><?= strtoupper(htmlspecialchars($m['categoria_nombre'])) ?></td>
                        <td style="color: var(--accent); font-weight: 900; font-family: monospace;">S/ <?= number_format($m['precio'], 2) ?></td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <button onclick='openMenuModal(<?= json_encode($m) ?>)' class="btn-edit" style="color: var(--accent); border: 1px solid var(--accent); padding: 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; background: transparent; cursor: pointer;">
                                    <i data-lucide="edit-3" style="width: 20px;"></i>
                                </button>
                                <a href="?delete=<?= $m['id'] ?>" onclick="return confirm('¿CONFIRMAR_ELIMINACIÓN_PROTOCOLO?')" class="btn-delete" style="color: var(--primary); border: 1px solid var(--primary); padding: 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s;">
                                    <i data-lucide="trash-2" style="width: 20px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($menus)): ?>
                <tr><td colspan="5" style="text-align:center; color: var(--text-secondary);">CARTA_VACÍA</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-menu" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(12px); transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);">
    <div class="glass" style="padding: 30px; border-top: 4px solid var(--primary); box-shadow: 0 0 60px rgba(255, 71, 87, 0.25); max-width: 550px; width: 95%; position: relative; border-radius: 12px; transform: scale(1); animation: modalIn 0.3s ease-out; max-height: 90vh; overflow-y: auto;">
        <button type="button" onclick="closeMenuModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-secondary); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 35px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <div style="background: var(--primary-glow); padding: 12px; border-radius: 10px;">
                <i data-lucide="utensils" style="color: var(--primary); width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h3 id="modal-title" style="letter-spacing: 3px; font-weight: 900; color: white; margin: 0; font-size: 1.2rem;">NUEVO PLATO</h3>
                <p style="font-size: 0.6rem; color: var(--text-secondary); margin: 5px 0 0; letter-spacing: 2px;">GESTIÓN_INVENTARIO_GASTRONÓMICO</p>
            </div>
        </div>

        <form id="menu-form" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <input type="hidden" name="menu_id" id="menu_id">
            <input type="hidden" name="imagen_current" id="imagen_current">

            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">IDENTIFICADOR_PRODUCTO</label>
                <input type="text" name="nombre" id="menu_nombre" class="form-control" required placeholder="Ej: Lomo Saltado..." style="height: 50px; font-size: 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 0 15px; border-radius: 6px; width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px;">
                <div class="form-group">
                    <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">CATEGORÍA</label>
                    <select name="categoria_id" id="menu_categoria" class="form-control" style="background: rgba(0,0,0,0.6); height: 50px; font-size: 0.9rem; border: 1px solid rgba(255,255,255,0.15); color: white; width: 100%; border-radius: 6px; padding: 0 15px;" required>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>"><?= strtoupper(htmlspecialchars($cat['nombre'])) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">VALOR_MERCADO (S/)</label>
                    <input type="number" step="0.01" name="precio" id="menu_precio" class="form-control" required placeholder="0.00" style="height: 50px; font-size: 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 0 15px; border-radius: 6px; width: 100%;">
                </div>
            </div>
            
            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">DETALLES_ESPECIFICACIONES</label>
                <textarea name="descripcion" id="menu_descripcion" class="form-control" style="height: 90px; font-size: 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 15px; border-radius: 6px; width: 100%; resize: none; line-height: 1.6;" placeholder="Descripción de ingredientes o preparación..."></textarea>
            </div>
            
            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">REPRESENTACIÓN_VISUAL</label>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div id="image-preview-container" style="width: 80px; height: 50px; background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.2); border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img id="image-preview" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <i data-lucide="image" id="preview-icon" style="color: rgba(255,255,255,0.2); width: 20px;"></i>
                    </div>
                    <input type="file" name="imagen_file" class="form-control" accept="image/*" style="padding-top: 12px; height: 50px; font-size: 0.8rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: var(--text-secondary); width: 100%;">
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <button type="submit" name="save_menu" class="cyber-btn" style="width: 100%; height: 50px; background: var(--primary); color: white; font-weight: 900; letter-spacing: 3px; font-size: 0.9rem; border: none; cursor: pointer; border-radius: 6px; box-shadow: 0 0 25px rgba(255, 71, 87, 0.4); display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i data-lucide="save" style="width: 20px;"></i>
                    <span id="btn-text">SINCRONIZAR CON CARTA</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openMenuModal(data = null) {
    const modal = document.getElementById('modal-menu');
    const form = document.getElementById('menu-form');
    const title = document.getElementById('modal-title');
    const btnText = document.getElementById('btn-text');
    const preview = document.getElementById('image-preview');
    const icon = document.getElementById('preview-icon');

    form.reset();
    document.getElementById('menu_id').value = '';
    document.getElementById('imagen_current').value = '';
    preview.style.display = 'none';
    icon.style.display = 'block';

    if (data) {
        title.innerText = 'EDITAR PLATO';
        btnText.innerText = 'ACTUALIZAR REGISTRO';
        document.getElementById('menu_id').value = data.id;
        document.getElementById('menu_nombre').value = data.nombre;
        document.getElementById('menu_descripcion').value = data.descripcion;
        document.getElementById('menu_precio').value = data.precio;
        document.getElementById('menu_categoria').value = data.categoria_id;
        document.getElementById('imagen_current').value = data.imagen;
        
        if (data.imagen) {
            preview.src = data.imagen.startsWith('http') ? data.imagen : '../' + data.imagen;
            preview.style.display = 'block';
            icon.style.display = 'none';
        }
    } else {
        title.innerText = 'NUEVO PLATO';
        btnText.innerText = 'SINCRONIZAR CON CARTA';
    }

    modal.style.display = 'flex';
}

function closeMenuModal() {
    document.getElementById('modal-menu').style.display = 'none';
}

// Close on escape
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeMenuModal();
});
</script>

<style>
    @keyframes modalIn {
        from { opacity: 0; transform: translateY(-30px) scale(0.95); }
        to { opacity: 1; transform: translateY(0) scale(1); }
    }
    .btn-delete:hover { background: var(--primary); color: white !important; box-shadow: 0 0 20px var(--primary-glow); transform: scale(1.1); }
    .btn-edit:hover { background: var(--accent); color: white !important; box-shadow: 0 0 20px rgba(52, 231, 228, 0.4); transform: scale(1.1); }
</style>

<?php include '../../includes/admin/footer.php'; ?>
