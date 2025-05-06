<?php
// Load staff data from JSON file
$staffCategories = loadJsonData('staff');

// Handle form submission for adding/editing staff
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'save_staff') {
        $staffId = !empty($_POST['staff_id']) ? intval($_POST['staff_id']) : null;
        $staffName = $_POST['staff_name'];
        $staffApPaterno = $_POST['staff_appaterno'];
        $staffApMaterno = $_POST['staff_apmaterno'];
        $staffRole = $_POST['staff_role'];
        $staffRfc = $_POST['staff_rfc'];
        
        // Determinar la categoría basada en el rol
        $staffCategory = ($staffRole === 'Mesero') ? 'meseros' : 'cocina';
        
        // Crear el array del empleado
        $staffItem = [
            'name' => $staffName,
            'apPaterno' => $staffApPaterno,
            'apMaterno' => $staffApMaterno,
            'role' => $staffRole,
            'rfc' => $staffRfc
        ];
        
        // Si es una edición, incluir el ID
        if ($staffId) {
            $staffItem['id'] = $staffId;
            updateJsonItem('staff', $staffItem, $staffCategory);
        } else {
            addJsonItem('staff', $staffItem, $staffCategory);
        }
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=staff&status=success');
        exit;
    }
    
    // Eliminar empleado
    if ($_POST['action'] === 'delete_staff') {
        $staffId = intval($_POST['staff_id']);
        $staffCategory = $_POST['staff_category'];
        
        deleteJsonItem('staff', $staffId, $staffCategory);
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=staff&status=success');
        exit;
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-users me-2"></i>Personal</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addStaffModal">
            <i class="fas fa-plus me-2"></i>Nuevo Empleado
        </button>
    </div>
    
    <!-- Search Bar -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="input-group">
                <span class="input-group-text"><i class="fas fa-search"></i></span>
                <input type="text" class="form-control" id="staffSearch" placeholder="Buscar personal...">
            </div>
        </div>
    </div>
    
    <!-- Staff Tabs -->
    <ul class="nav nav-tabs mb-4" id="staffTabs" role="tablist">
        <?php foreach ($staffCategories as $index => $category): ?>
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
    <div class="tab-content" id="staffTabsContent">
        <?php foreach ($staffCategories as $index => $category): ?>
        <div class="tab-pane fade <?php echo ($index === 0) ? 'show active' : ''; ?>" 
             id="<?php echo $category['id']; ?>" 
             role="tabpanel">
            
            <div class="list-group staff-list">
                <?php foreach ($category['staff'] as $person): ?>
                <div class="list-group-item list-group-item-action">
                    <div class="d-flex w-100 justify-content-between align-items-center">
                        <div>
                            <h5 class="mb-1"><?php echo $person['name']; ?> <?php echo $person['apPaterno']; ?> <?php echo $person['apMaterno']; ?></h5>
                            <p class="mb-1 text-primary"><?php echo $person['role']; ?></p>
                            <small class="text-muted">RFC: <?php echo $person['rfc']; ?></small>
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-primary edit-staff" 
                                    data-id="<?php echo $person['id']; ?>"
                                    data-name="<?php echo $person['name']; ?>"
                                    data-appaterno="<?php echo $person['apPaterno']; ?>"
                                    data-apmaterno="<?php echo $person['apMaterno']; ?>"
                                    data-role="<?php echo $person['role']; ?>"
                                    data-rfc="<?php echo $person['rfc']; ?>"
                                    data-category="<?php echo $category['id']; ?>">
                                <i class="fas fa-edit me-1"></i>Editar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-danger delete-staff ms-2"
                                    data-id="<?php echo $person['id']; ?>"
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

<!-- Add/Edit Staff Modal -->
<div class="modal fade" id="addStaffModal" tabindex="-1" aria-labelledby="addStaffModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="addStaffModalLabel">Agregar Empleado</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="staffForm" action="index.php?page=staff" method="post">
                <div class="modal-body">
                    <input type="hidden" name="action" value="save_staff">
                    <input type="hidden" name="staff_id" id="staff_id" value="">
                    
                    <div class="mb-3">
                        <label for="staff_name" class="form-label">Nombre</label>
                        <input type="text" class="form-control" id="staff_name" name="staff_name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="staff_appaterno" class="form-label">Apellido Paterno</label>
                        <input type="text" class="form-control" id="staff_appaterno" name="staff_appaterno" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="staff_apmaterno" class="form-label">Apellido Materno</label>
                        <input type="text" class="form-control" id="staff_apmaterno" name="staff_apmaterno" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="staff_role" class="form-label">Rol</label>
                        <select class="form-select" id="staff_role" name="staff_role" required>
                            <option value="Mesero">Mesero</option>
                            <option value="Cocina">Cocina</option>
                        </select>
                    </div>
                    
                    <div class="mb-3">
                        <label for="staff_rfc" class="form-label">RFC</label>
                        <input type="text" class="form-control" id="staff_rfc" name="staff_rfc" required>
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

<!-- Delete Staff Form (hidden) -->
<form id="deleteStaffForm" action="index.php?page=staff" method="post" style="display: none;">
    <input type="hidden" name="action" value="delete_staff">
    <input type="hidden" name="staff_id" id="delete_staff_id">
    <input type="hidden" name="staff_category" id="delete_staff_category">
</form>

