<?php
// Load menu items and staff data
$menuCategories = loadJsonData('menus');
$staffCategories = loadJsonData('staff');

// Extract waiters from staff data
$waiters = [];
foreach ($staffCategories as $category) {
    if ($category['id'] === 'meseros') {
        $waiters = $category['staff'];
        break;
    }
}

// Handle form submission for creating a new order
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'create_order') {
        $tableNumber = intval($_POST['table_number']);
        $waiterId = intval($_POST['waiter_id']);
        $menuItems = $_POST['menu_items'];
        $quantities = $_POST['quantities'];
        $notes = $_POST['order_notes'];
        
        // Encontrar el nombre del mesero
        $waiterName = '';
        foreach ($waiters as $waiter) {
            if ($waiter['id'] == $waiterId) {
                $waiterName = $waiter['name'] . ' ' . $waiter['apPaterno'];
                break;
            }
        }
        
        // Crear los items de la orden
        $orderItems = [];
        $total = 0;
        
        for ($i = 0; $i < count($menuItems); $i++) {
            $menuItemId = intval($menuItems[$i]);
            $quantity = intval($quantities[$i]);
            
            // Buscar el ítem del menú
            $menuItem = null;
            foreach ($menuCategories as $category) {
                foreach ($category['items'] as $item) {
                    if ($item['id'] == $menuItemId) {
                        $menuItem = $item;
                        break 2;
                    }
                }
            }
            
            if ($menuItem) {
                $orderItem = [
                    'name' => $menuItem['name'],
                    'quantity' => $quantity,
                    'price' => $menuItem['price']
                ];
                
                $orderItems[] = $orderItem;
                $total += $menuItem['price'] * $quantity;
            }
        }
        
        // Crear la orden con hora completa (HH:MM:SS)
        $newOrder = [
            'table' => $tableNumber,
            'status' => 'active',
            'waiter' => $waiterName,
            'items' => $orderItems,
            'total' => $total,
            'time' => date('H:i:s'),
            'notes' => $notes,
            'date' => date('d/m/Y') // Agregar la fecha actual
        ];
        
        // Guardar la orden
        addJsonItem('orders', $newOrder);
        
        // Redirigir a la página de órdenes
        header('Location: index.php?page=orders&status=created');
        exit;
    }
}
?>

<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-4">
      <h1 class="h2"><i class="fas fa-plus-circle me-2"></i>Nueva Orden</h1>
      <a href="index.php?page=orders" class="btn btn-outline-secondary">
          <i class="fas fa-arrow-left me-2"></i>Volver a Órdenes
      </a>
  </div>
  
  <form id="newOrderForm" action="index.php?page=new-order" method="post" class="needs-validation" novalidate>
      <input type="hidden" name="action" value="create_order">
      
      <div class="row">
          <!-- Left Column - Order Details -->
          <div class="col-md-8">
              <!-- Order Information Card -->
              <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-primary text-white">
                      <h5 class="card-title mb-0"><i class="fas fa-info-circle me-2"></i>Información de la Orden</h5>
                  </div>
                  <div class="card-body">
                      <div class="row">
                          <div class="col-md-6 mb-3">
                              <label for="table_number" class="form-label">Mesa</label>
                              <select class="form-select form-select-lg" id="table_number" name="table_number" required>
                                  <option value="">Seleccionar mesa</option>
                                  <?php for ($i = 1; $i <= 10; $i++): ?>
                                  <option value="<?php echo $i; ?>"><?php echo "Mesa " . $i; ?></option>
                                  <?php endfor; ?>
                              </select>
                              <div class="invalid-feedback">
                                  Por favor seleccione una mesa.
                              </div>
                          </div>
                          <div class="col-md-6 mb-3">
                              <label for="waiter_id" class="form-label">Mesero</label>
                              <select class="form-select form-select-lg" id="waiter_id" name="waiter_id" required>
                                  <option value="">Seleccionar mesero</option>
                                  <?php foreach ($waiters as $waiter): ?>
                                  <option value="<?php echo $waiter['id']; ?>">
                                      <?php echo $waiter['name'] . ' ' . $waiter['apPaterno']; ?>
                                  </option>
                                  <?php endforeach; ?>
                              </select>
                              <div class="invalid-feedback">
                                  Por favor seleccione un mesero.
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <!-- Order Items Card -->
              <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                      <h5 class="card-title mb-0"><i class="fas fa-utensils me-2"></i>Alimentos y Bebidas</h5>
                      <button type="button" class="btn btn-light btn-sm" id="addOrderItemBtn">
                          <i class="fas fa-plus me-1"></i>Agregar Item
                      </button>
                  </div>
                  <div class="card-body">
                      <div id="orderItemsContainer">
                          <!-- Order items will be added here dynamically -->
                          <div class="order-item-row mb-3 p-3 border rounded bg-light">
                              <div class="row align-items-center">
                                  <div class="col-md-6 mb-2 mb-md-0">
                                      <label class="form-label small">Producto</label>
                                      <select class="form-select menu-item-select" name="menu_items[]" required>
                                          <option value="">Seleccionar producto</option>
                                          <?php foreach ($menuCategories as $category): ?>
                                              <optgroup label="<?php echo $category['name']; ?>">
                                                  <?php foreach ($category['items'] as $item): ?>
                                                  <option value="<?php echo $item['id']; ?>" data-price="<?php echo $item['price']; ?>">
                                                      <?php echo $item['name']; ?> - <?php echo formatCurrency($item['price']); ?>
                                                  </option>
                                                  <?php endforeach; ?>
                                              </optgroup>
                                          <?php endforeach; ?>
                                      </select>
                                      <div class="invalid-feedback">
                                          Por favor seleccione un producto.
                                      </div>
                                  </div>
                                  <div class="col-md-2 mb-2 mb-md-0">
                                      <label class="form-label small">Cantidad</label>
                                      <input type="number" class="form-control item-quantity" name="quantities[]" min="1" value="1" required>
                                      <div class="invalid-feedback">
                                          Cantidad inválida.
                                      </div>
                                  </div>
                                  <div class="col-md-3 mb-2 mb-md-0">
                                      <label class="form-label small">Subtotal</label>
                                      <div class="input-group">
                                          <span class="input-group-text">$</span>
                                          <input type="text" class="form-control item-price" readonly value="0.00">
                                      </div>
                                  </div>
                                  <div class="col-md-1 d-flex align-items-end">
                                      <button type="button" class="btn btn-outline-danger remove-item mt-4">
                                          <i class="fas fa-trash"></i>
                                      </button>
                                  </div>
                              </div>
                          </div>
                      </div>
                  </div>
              </div>
              
              <!-- Notes Card -->
              <div class="card mb-4 shadow-sm">
                  <div class="card-header bg-primary text-white">
                      <h5 class="card-title mb-0"><i class="fas fa-sticky-note me-2"></i>Notas</h5>
                  </div>
                  <div class="card-body">
                      <textarea class="form-control" id="order_notes" name="order_notes" rows="3" placeholder="Agregar notas o instrucciones especiales..."></textarea>
                  </div>
              </div>
          </div>
          
          <!-- Right Column - Order Summary -->
          <div class="col-md-4">
              <div class="card sticky-top shadow" style="top: 20px;">
                  <div class="card-header bg-primary text-white">
                      <h5 class="card-title mb-0"><i class="fas fa-receipt me-2"></i>Resumen de la Orden</h5>
                  </div>
                  <div class="card-body">
                      <div id="orderSummary">
                          <div class="mb-3 p-3 bg-light rounded">
                              <p class="mb-1" id="summaryTable">Mesa: <span class="fw-bold">-</span></p>
                              <p class="mb-1" id="summaryWaiter">Mesero: <span class="fw-bold">-</span></p>
                          </div>
                          
                          <h6 class="border-bottom pb-2 mb-3">Items:</h6>
                          <ul class="list-group list-group-flush mb-3" id="summaryItems">
                              <!-- Items will be added here dynamically -->
                              <li class="list-group-item px-0 text-muted">No hay items seleccionados</li>
                          </ul>
                          
                          <div class="bg-light p-3 rounded">
                              <div class="d-flex justify-content-between mb-2">
                                  <span>Subtotal:</span>
                                  <span id="summarySubtotal" class="fw-bold">$0.00</span>
                              </div>
                              <div class="d-flex justify-content-between mb-2">
                                  <span>IVA (16%):</span>
                                  <span id="summaryTax" class="fw-bold">$0.00</span>
                              </div>
                              <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2">
                                  <span>Total:</span>
                                  <span id="summaryTotal" class="text-primary fs-5">$0.00</span>
                              </div>
                          </div>
                      </div>
                  </div>
                  <div class="card-footer">
                      <button type="submit" class="btn btn-primary w-100 btn-lg">
                          <i class="fas fa-save me-2"></i>Confirmar Orden
                      </button>
                  </div>
              </div>
          </div>
      </div>
  </form>
</div>

<script>
// Add form validation
(function() {
 'use strict';
 window.addEventListener('load', function() {
   var forms = document.getElementsByClassName('needs-validation');
   var validation = Array.prototype.filter.call(forms, function(form) {
     form.addEventListener('submit', function(event) {
       if (form.checkValidity() === false) {
         event.preventDefault();
         event.stopPropagation();
       }
       form.classList.add('was-validated');
     }, false);
   });
 }, false);
})();
</script>

