<?php
require_once '../../config/db.php';
include '../../includes/admin/header.php';

// Protegido por middleware via header.php (auth_required)
$msg = '';

if (isset($_POST['save_blog'])) {
    $id = $_POST['blog_id'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $contenido = $_POST['contenido'] ?? '';
    $imagen_db = $_POST['imagen_current'] ?? '';

    // Handle File Upload with Cloudinary & Local Fallback
    if (isset($_FILES['imagen_file']) && $_FILES['imagen_file']['error'] === UPLOAD_ERR_OK) {
        $temp_path = $_FILES['imagen_file']['tmp_name'];
        
        // Try Cloudinary
        require_once '../../libs/cloudinary_handler.php';
        $cloudinary = CloudinaryHandler::getInstance();
        $uploaded_url = $cloudinary->upload($temp_path, 'blog');
        
        if ($uploaded_url) {
            $imagen_db = $uploaded_url;
        } else {
            // Local Fallback
            $upload_dir = '../uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0777, true);
            
            $file_extension = pathinfo($_FILES['imagen_file']['name'], PATHINFO_EXTENSION);
            $file_name = 'blog_' . uniqid() . '.' . $file_extension;
            $upload_path = $upload_dir . $file_name;

            if (move_uploaded_file($temp_path, $upload_path)) {
                $imagen_db = 'uploads/' . $file_name;
                $msg = 'SISTEMA: Imagen guardada LOCALMENTE (Cloudinary falló).';
            } else {
                $msg = 'ERROR_UPLOAD: No se pudo guardar la imagen localmente.';
            }
        }
    }

    if (empty($imagen_db)) {
        $imagen_db = 'https://images.unsplash.com/photo-1541544741938-0af808871cc0?auto=format&fit=crop&q=80&w=600';
    }

    if (!empty($nombre)) {
        if (!empty($id)) {
            // Update existing
            db_execute("UPDATE public.blogs SET nombre = ?, contenido = ?, imagen = ? WHERE id = ?", 
                       [$nombre, $contenido, $imagen_db, $id]);
            $msg = 'Publicación actualizada con éxito.';
        } else {
            // Insert new
            db_execute("INSERT INTO public.blogs (nombre, contenido, imagen) VALUES (?, ?, ?)", 
                       [$nombre, $contenido, $imagen_db]);
            $msg = 'Publicación registrada con éxito.';
        }
    }
}

if (isset($_GET['delete'])) {
    db_execute("DELETE FROM public.blogs WHERE id = ?", [$_GET['delete']]);
    $msg = 'Publicación eliminada del sistema.';
}

$blogs = db_get_all("SELECT * FROM public.blogs ORDER BY creado_en DESC");
?>

<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 50px;">
    <h2 style="letter-spacing: 5px; font-weight: 900; margin: 0; color: white;">BLOG / NOTICIAS</h2>
    <button onclick="openBlogModal()" class="cyber-btn" style="padding: 15px 35px; background: var(--primary); color: white; border: none; cursor: pointer; clip-path: polygon(10% 0, 100% 0, 90% 100%, 0% 100%); font-weight: 800;">+ NUEVA PUBLICACIÓN</button>
</div>

<?php if ($msg): ?>
    <div class="alert-cyber">
        <i data-lucide="terminal"></i>
        <span>> STATUS_SYS: <?= strtoupper($msg) ?></span>
    </div>
<?php endif; ?>

<div class="glass" style="padding: 40px; margin-bottom: 60px; border-top: 2px solid var(--primary);">
    <h3 style="letter-spacing: 3px; font-weight: 800; border-bottom: 1px solid var(--glass-border); padding-bottom: 15px; margin-bottom: 30px;">REGISTROS_PUBLICADOS</h3>
    <div style="overflow-x: auto;">
        <table class="table-admin" style="min-width: 900px;">
            <thead>
                <tr>
                    <th>PREVISUALIZACIÓN</th>
                    <th>TÍTULO_ARTÍCULO</th>
                    <th>CONTENIDO</th>
                    <th>FECHA_PUBLICACIÓN</th>
                    <th>GESTIÓN</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($blogs as $b): 
                    $img_path = $b['imagen'];
                    // Display logic: if it's a URL (Cloudinary), use it. If not, prefix with base path.
                    $display_img = (strpos($img_path, 'http') === 0) ? $img_path : '../' . $img_path;
                ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($display_img) ?>" alt="" style="width: 80px; height: 50px; border: 1px solid var(--accent); padding: 2px; border-radius: 4px; object-fit: cover;"></td>
                        <td style="font-weight: 800; color: white;"><?= strtoupper(htmlspecialchars($b['nombre'])) ?></td>
                        <td>
                            <div style="font-size: 0.7rem; color: var(--text-secondary); max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                <?= htmlspecialchars($b['contenido']) ?>
                            </div>
                        </td>
                        <td style="color: var(--accent); font-size: 0.7rem;"><?= date('d/m/Y H:i', strtotime($b['creado_en'])) ?></td>
                        <td>
                            <div style="display: flex; gap: 10px;">
                                <button onclick='openBlogModal(<?= json_encode($b) ?>)' class="btn-edit" style="color: var(--accent); border: 1px solid var(--accent); padding: 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s; background: transparent; cursor: pointer;">
                                    <i data-lucide="edit-3" style="width: 20px;"></i>
                                </button>
                                <a href="?delete=<?= $b['id'] ?>" onclick="return confirm('¿CONFIRMAR_ELIMINACIÓN_ARTÍCULO?')" class="btn-delete" style="color: var(--primary); border: 1px solid var(--primary); padding: 10px; border-radius: 4px; display: inline-flex; align-items: center; justify-content: center; transition: 0.3s;">
                                    <i data-lucide="trash-2" style="width: 20px;"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <?php if(empty($blogs)): ?>
                <tr><td colspan="5" style="text-align:center; color: var(--text-secondary);">NO_EXISTEN_ARTÍCULOS</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<div id="modal-blog" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.85); z-index: 2000; align-items: center; justify-content: center; backdrop-filter: blur(12px); transition: all 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);">
    <div class="glass" style="padding: 30px; border-top: 4px solid var(--primary); box-shadow: 0 0 60px rgba(255, 71, 87, 0.25); max-width: 600px; width: 95%; position: relative; border-radius: 12px; transform: scale(1); animation: modalIn 0.3s ease-out; max-height: 90vh; overflow-y: auto;">
        <button type="button" onclick="closeBlogModal()" style="position: absolute; top: 20px; right: 20px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.1); color: var(--text-secondary); width: 35px; height: 35px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; transition: 0.3s;">&times;</button>
        
        <div style="display: flex; align-items: center; gap: 15px; margin-bottom: 35px; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 20px;">
            <div style="background: var(--primary-glow); padding: 12px; border-radius: 10px;">
                <i data-lucide="file-text" style="color: var(--primary); width: 24px; height: 24px;"></i>
            </div>
            <div>
                <h3 id="modal-title" style="letter-spacing: 3px; font-weight: 900; color: white; margin: 0; font-size: 1.2rem; text-shadow: 0 0 10px rgba(255,255,255,0.2);">NUEVA PUBLICACIÓN</h3>
                <p style="font-size: 0.6rem; color: var(--text-secondary); margin: 5px 0 0; letter-spacing: 2px;">PROTOCOLO_CMS_V3.0</p>
            </div>
        </div>

        <form id="blog-form" method="POST" enctype="multipart/form-data" style="display: grid; grid-template-columns: 1fr; gap: 20px;">
            <input type="hidden" name="blog_id" id="blog_id">
            <input type="hidden" name="imagen_current" id="imagen_current">

            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">TÍTULO_ARTÍCULO</label>
                <input type="text" name="nombre" id="blog_nombre" class="form-control" required placeholder="Ingresar titular de impacto..." style="height: 50px; font-size: 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 0 15px; border-radius: 6px; width: 100%;">
            </div>
            
            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">CONTENIDO_CUERPO</label>
                <textarea name="contenido" id="blog_contenido" required class="form-control" style="height: 150px; font-size: 0.9rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: white; padding: 15px; border-radius: 6px; width: 100%; resize: none; line-height: 1.6;" placeholder="Redactar el cuerpo de la noticia..."></textarea>
            </div>
            
            <div class="form-group">
                <label style="font-size: 0.7rem; color: var(--accent); letter-spacing: 2px; font-weight: 800; display: block; margin-bottom: 8px;">IMAGEN_PORTADA</label>
                <div style="display: flex; gap: 15px; align-items: center;">
                    <div id="image-preview-container" style="width: 80px; height: 50px; background: rgba(0,0,0,0.3); border: 1px dashed rgba(255,255,255,0.2); border-radius: 4px; overflow: hidden; display: flex; align-items: center; justify-content: center;">
                        <img id="image-preview" src="" style="width: 100%; height: 100%; object-fit: cover; display: none;">
                        <i data-lucide="image" id="preview-icon" style="color: rgba(255,255,255,0.2); width: 20px;"></i>
                    </div>
                    <input type="file" name="imagen_file" class="form-control" accept="image/*" style="padding-top: 12px; height: 50px; font-size: 0.8rem; background: rgba(0,0,0,0.6); border: 1px solid rgba(255,255,255,0.15); color: var(--text-secondary); width: 100%;">
                </div>
            </div>
            
            <div style="margin-top: 15px;">
                <button type="submit" name="save_blog" class="cyber-btn" style="width: 100%; height: 50px; background: var(--primary); color: white; font-weight: 900; letter-spacing: 3px; font-size: 0.9rem; border: none; cursor: pointer; border-radius: 6px; box-shadow: 0 0 25px rgba(255, 71, 87, 0.4); display: flex; align-items: center; justify-content: center; gap: 10px;">
                    <i data-lucide="save" style="width: 20px;"></i>
                    <span id="btn-text">PUBLICAR EN SISTEMA</span>
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function openBlogModal(data = null) {
    const modal = document.getElementById('modal-blog');
    const form = document.getElementById('blog-form');
    const title = document.getElementById('modal-title');
    const btnText = document.getElementById('btn-text');
    const preview = document.getElementById('image-preview');
    const icon = document.getElementById('preview-icon');

    form.reset();
    document.getElementById('blog_id').value = '';
    document.getElementById('imagen_current').value = '';
    preview.style.display = 'none';
    icon.style.display = 'block';

    if (data) {
        title.innerText = 'EDITAR PUBLICACIÓN';
        btnText.innerText = 'ACTUALIZAR REGISTRO';
        document.getElementById('blog_id').value = data.id;
        document.getElementById('blog_nombre').value = data.nombre;
        document.getElementById('blog_contenido').value = data.contenido;
        document.getElementById('imagen_current').value = data.imagen;
        
        if (data.imagen) {
            preview.src = data.imagen.startsWith('http') ? data.imagen : '../' + data.imagen;
            preview.style.display = 'block';
            icon.style.display = 'none';
        }
    } else {
        title.innerText = 'NUEVA PUBLICACIÓN';
        btnText.innerText = 'PUBLICAR EN SISTEMA';
    }

    modal.style.display = 'flex';
}

function closeBlogModal() {
    document.getElementById('modal-blog').style.display = 'none';
}

// Close on escape
window.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') closeBlogModal();
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
