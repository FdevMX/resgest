<?php
// Load menu data from JSON file
$menuCategories = loadJsonData('menus');

// Handle form submission for adding/editing menu items
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_menu_item') {
        $itemId = !empty($_POST['item_id']) ? intval($_POST['item_id']) : null;
        $itemName = $_POST['item_name'];
        $itemPrice = floatval($_POST['item_price']);
        $itemCategory = $_POST['item_category'];
        
        // Manejar la carga de imágenes
        $imagePath = 'assets/img/placeholder.jpg'; // Imagen por defecto
        
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'assets/img/menu/';
            
            // Crear directorio si no existe
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileName = basename($_FILES['item_image']['name']);
            $uploadFile = $uploadDir . $fileName;
            
            // Mover el archivo subido
            if (move_uploaded_file($_FILES['item_image']['tmp_name'], $uploadFile)) {
                $imagePath = $uploadFile;
            }
        }
        
        // Crear el array del ítem
        $menuItem = [
            'name' => $itemName,
            'price' => $itemPrice,
            'image' => $imagePath
        ];
        
        // Si es una edición, incluir el ID
        if ($itemId) {
            $menuItem['id'] = $itemId;
            updateJsonItem('menus', $menuItem, $itemCategory);
        } else {
            addJsonItem('menus', $menuItem, $itemCategory);
        }
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=menus&status=success');
        exit;
    }
    
    // Eliminar ítem del menú
    if ($_POST['action'] === 'delete_menu_item') {
        $itemId = intval($_POST['item_id']);
        $itemCategory = $_POST['item_category'];
        
        deleteJsonItem('menus', $itemId, $itemCategory);
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=menus&status=success');
        exit;
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-utensils me-2"></i>Menús</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addMenuItemModal">
            <i class="fas fa-plus me-2"></i>Nuevo Ítem
        </button>
    </div>
    
    <!-- Menu Tabs -->
    <ul class="nav nav-tabs mb-4" id="menuTabs" role="tablist">
        <?php foreach ($menuCategories as $index => $category): ?>
        <li class="nav-item" role="presentation">
            <button class="nav-link <?php echo ($index === 0) ? 'active' : ''; ?>" 
                    id="<?php echo $category['id']; ?>-tab" 
                    data-bs-toggle="tab" 
                    data-bs-target="#<?php echo $category['id']; ?>" 
                    type="button" 
                    role="tab">
                <?php echo $category['name']; ?>
            </button>
        </li>
        <?php endforeach; ?>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="menuTabsContent">
        <?php foreach ($menuCategories as $index => $category): ?>
        <div class="tab-pane fade <?php echo ($index === 0) ? 'show active' : ''; ?>" 
             id="<?php echo $category['id']; ?>" 
             role="tabpanel">
            
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
                <?php foreach ($category['items'] as $item): ?>
                <div class="col">
                    <div class="card h-100">
                        <img src="<?php echo $item['image']; ?>" class="card-img-top menu-img" alt="<?php echo $item['name']; ?>">
                        <div class="card-body">
                            <h5 class="card-title"><?php echo $item['name']; ?></h5>
                            <p class="card-text text-primary fw-bold"><?php echo formatCurrency($item['price']); ?></p>
                        </div>
                        <div class="card-footer bg-transparent border-top-0">
                            <button type="button" class="btn btn-sm btn-outline-primary edit-menu-item" 
                                    data-id="<?php echo $item['id']; ?>"
                                    data-name="<?php echo $item['name']; ?>"
                                    data-price="<?php echo $item['price']; ?>"
                                    data-category="<?php echo $category['id']; ?>">
                                <i class="fas fa-edit me-1"></i>Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-menu-item ms-2"
                                    data-id="<?php echo $item['id']; ?>"
                                    data-category="<?php echo $category['id']; ?>">
                                <i class="fas fa-trash me-1"></i>Eliminar
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Add/Edit Menu Item Modal -->
<div class="modal fade" id="addMenuItemModal" tabindex="-1" aria-labelledby="addMenuItemModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addMenuItemModalLabel">Agregar Ítem al Menú</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="menuItemForm" action="index.php?page=menus" method="post" enctype="multipart/form-data">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_menu_item">
                    <input type="hidden" name="item_id" id="item_id" value="">
                    
                    <div class="mb-3">
                        <label for="item_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="item_name" name="item_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="item_price" class="form-label">Precio</label>
                        <div class="input-group">
                            <span class="input-group-text">$</span>
                            <input type="number" class="form-control" id="item_price" name="item_price" step="0.01" min="0" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="item_category" class="form-label">Categoría</label>
                        <select class="form-select" id="item_category" name="item_category" required>
                            <?php foreach ($menuCategories as $category): ?>
                            <option value="<?php echo $category['id']; ?>"><?php echo $category['name']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="item_image" class="form-label">Imagen</label>
                        <input type="file" class="form-control" id="item_image" name="item_image" accept="image/*">
                        <div class="form-text">Seleccione una imagen para el ítem del menú.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Delete Menu Item Form (hidden) -->
<form id="deleteMenuItemForm" action="index.php?page=menus" method="post" style="display: none;">
    <input type="hidden" name="action" value="delete_menu_item">
    <input type="hidden" name="item_id" id="delete_item_id">
    <input type="hidden" name="item_category" id="delete_item_category">
</form>

