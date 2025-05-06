<?php
// Load orders data from JSON file
$orders = loadJsonData('orders');
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

// Handle form submission for updating order status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'complete_order') {
        $orderId = intval($_POST['order_id']);
        
        // Buscar la orden
        $orderToComplete = null;
        foreach ($orders as $key => $order) {
            if ($order['id'] == $orderId) {
                $orderToComplete = $order;
                
                // Actualizar el estado de la orden
                $orders[$key]['status'] = 'completed';
                break;
            }
        }
        
        // Guardar las órdenes actualizadas
        saveJsonData('orders', $orders);
        
        // Si encontramos la orden, crear una venta
        if ($orderToComplete) {
            // Cargar ventas existentes
            $sales = loadJsonData('sales');
            
            // Calcular subtotal y tax
            $subtotal = $orderToComplete['total'];
            $tax = calculateTax($subtotal);
            $total = calculateTotal($subtotal, $tax);
            
            // Crear la venta
            $newSale = [
                'id' => 'V-' . str_pad(count($sales) + 1, 3, '0', STR_PAD_LEFT),
                'table' => $orderToComplete['table'],
                'date' => isset($orderToComplete['date']) ? $orderToComplete['date'] : date('d/m/Y'),
                'time' => isset($orderToComplete['time']) ? $orderToComplete['time'] : date('H:i:s'),
                'waiter' => $orderToComplete['waiter'],
                'items' => $orderToComplete['items'],
                'subtotal' => $subtotal,
                'tax' => $tax,
                'total' => $total
            ];
            
            // Guardar la venta
            addJsonItem('sales', $newSale);
        }
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=orders&status=success');
        exit;
    }
    
    // Eliminar orden
    if ($_POST['action'] === 'delete_order') {
        $orderId = intval($_POST['order_id']);
        
        deleteJsonItem('orders', $orderId);
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=orders&status=success');
        exit;
    }
    
    // Agregar productos a una orden existente
    if ($_POST['action'] === 'add_to_order') {
        $orderId = intval($_POST['order_id']);
        $menuItems = isset($_POST['menu_items']) ? $_POST['menu_items'] : [];
        $quantities = isset($_POST['quantities']) ? $_POST['quantities'] : [];
        
        if (!empty($menuItems) && !empty($quantities)) {
            // Buscar la orden
            foreach ($orders as &$order) {
                if ($order['id'] == $orderId) {
                    // Crear los nuevos items de la orden
                    for ($i = 0; $i < count($menuItems); $i++) {
                        $menuItemId = intval($menuItems[$i]);
                        $quantity = intval($quantities[$i]);
                        
                        if ($menuItemId > 0 && $quantity > 0) {
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
                                
                                // Agregar el item a la orden
                                $order['items'][] = $orderItem;
                                
                                // Actualizar el total
                                $order['total'] += $menuItem['price'] * $quantity;
                            }
                        }
                    }
                    
                    break;
                }
            }
            
            // Guardar las órdenes actualizadas
            saveJsonData('orders', $orders);
            
            // Redirigir con mensaje de éxito
            header('Location: index.php?page=orders&status=updated');
            exit;
        } else {
            // Redirigir con mensaje de error
            header('Location: index.php?page=orders&status=error&message=No se seleccionaron productos');
            exit;
        }
    }
}

// Verificar si hay mensajes de estado
$statusMessage = '';
if (isset($_GET['status'])) {
    switch ($_GET['status']) {
        case 'success':
            $statusMessage = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Operación completada con éxito.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
            break;
        case 'updated':
            $statusMessage = '<div class="alert alert-success alert-dismissible fade show" role="alert">
                                Orden actualizada correctamente.
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
            break;
        case 'error':
            $errorMsg = isset($_GET['message']) ? $_GET['message'] : 'Ha ocurrido un error.';
            $statusMessage = '<div class="alert alert-danger alert-dismissible fade show" role="alert">
                                ' . $errorMsg . '
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                              </div>';
            break;
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-clipboard-list me-2"></i>Órdenes</h1>
        <a href="index.php?page=new-order" class="btn btn-primary">
            <i class="fas fa-plus me-2"></i>Nueva Orden
        </a>
    </div>
    
    <!-- Mostrar mensajes de estado -->
    <?php echo $statusMessage; ?>
    
    <!-- Filter Buttons -->
    <div class="mb-4">
        <div class="btn-group" role="group">
            <button type="button" class="btn btn-outline-primary active" data-filter="all">Todas</button>
            <button type="button" class="btn btn-outline-primary" data-filter="active">Activas</button>
            <button type="button" class="btn btn-outline-primary" data-filter="completed">Completadas</button>
        </div>
    </div>
    
    <!-- Orders List -->
<div class="orders-container">
   <?php foreach ($orders as $order): ?>
   <div class="card mb-4 order-card shadow-sm" data-status="<?php echo $order['status']; ?>" data-id="<?php echo $order['id']; ?>">
       <div class="card-header bg-<?php echo ($order['status'] === 'active') ? 'primary' : 'secondary'; ?> text-white">
           <div class="d-flex justify-content-between align-items-center">
               <h5 class="mb-0">
                   <i class="fas fa-utensils me-2"></i>Mesa <?php echo $order['table']; ?>
                   <span class="badge bg-<?php echo ($order['status'] === 'active') ? 'success' : 'secondary'; ?> ms-2">
                       <?php echo ($order['status'] === 'active') ? 'Activa' : 'Completada'; ?>
                   </span>
               </h5>
               <div>
                   <span class="me-3"><i class="far fa-clock me-1"></i><?php echo $order['time']; ?></span>
                   <span><i class="fas fa-user me-1"></i><?php echo $order['waiter']; ?></span>
               </div>
           </div>
       </div>
       <div class="card-body">
           <div class="row">
               <div class="col-md-8">
                   <h6 class="card-subtitle mb-3 text-primary"><i class="fas fa-list me-2"></i>Productos a consumir:</h6>
                   <div class="table-responsive">
                       <table class="table table-hover order-items-table">
                           <thead class="table-light">
                               <tr>
                                   <th>Producto</th>
                                   <th class="text-center">Cantidad</th>
                                   <th class="text-end">Precio</th>
                                   <th class="text-end">Subtotal</th>
                               </tr>
                           </thead>
                           <tbody>
                               <?php foreach ($order['items'] as $item): ?>
                               <tr class="order-item-row">
                                   <td><strong><?php echo $item['name']; ?></strong></td>
                                   <td class="text-center"><?php echo $item['quantity']; ?></td>
                                   <td class="text-end"><?php echo formatCurrency($item['price']); ?></td>
                                   <td class="text-end"><?php echo formatCurrency($item['price'] * $item['quantity']); ?></td>
                               </tr>
                               <?php endforeach; ?>
                           </tbody>
                           <tfoot class="table-light">
                               <tr>
                                   <td colspan="3" class="text-end"><strong>Total:</strong></td>
                                   <td class="text-end"><strong><?php echo formatCurrency($order['total']); ?></strong></td>
                               </tr>
                           </tfoot>
                       </table>
                   </div>
                   <?php if (isset($order['notes']) && !empty($order['notes'])): ?>
                   <div class="mt-3 p-2 bg-light rounded">
                       <h6><i class="fas fa-sticky-note me-2"></i>Notas:</h6>
                       <p class="mb-0"><?php echo $order['notes']; ?></p>
                   </div>
                   <?php endif; ?>
               </div>
               <div class="col-md-4">
                   <div class="card h-100 bg-light">
                       <div class="card-body">
                           <h5 class="card-title text-center mb-3"><i class="fas fa-cogs me-2"></i>Acciones</h5>
                           <div class="d-grid gap-2">
                               <button type="button" class="btn btn-outline-primary view-order-details" 
                                       data-id="<?php echo $order['id']; ?>"
                                       data-table="<?php echo $order['table']; ?>"
                                       data-waiter="<?php echo $order['waiter']; ?>"
                                       data-time="<?php echo $order['time']; ?>"
                                       data-total="<?php echo $order['total']; ?>">
                                   <i class="fas fa-eye me-1"></i>Ver Detalles
                               </button>
                               
                               <?php if ($order['status'] === 'active'): ?>
                               <button type="button" class="btn btn-success complete-order" 
                                       data-id="<?php echo $order['id']; ?>">
                                   <i class="fas fa-check me-1"></i>Completar Orden
                               </button>
                               
                               <button type="button" class="btn btn-info add-to-order" 
                                       data-id="<?php echo $order['id']; ?>"
                                       data-bs-toggle="modal" 
                                       data-bs-target="#addToOrderModal">
                                   <i class="fas fa-plus me-1"></i>Agregar Productos
                               </button>
                               <?php endif; ?>
                               
                               <button type="button" class="btn btn-danger delete-order" 
                                       data-id="<?php echo $order['id']; ?>">
                                   <i class="fas fa-trash me-1"></i>Eliminar Orden
                               </button>
                           </div>
                       </div>
                   </div>
               </div>
           </div>
       </div>
   </div>
   <?php endforeach; ?>
</div>

<!-- Order Details Modal -->
<div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-labelledby="orderDetailsModalLabel" aria-hidden="true">
   <div class="modal-dialog modal-lg">
       <div class="modal-content">
           <div class="modal-header bg-primary text-white">
               <h5 class="modal-title" id="orderDetailsModalLabel"><i class="fas fa-receipt me-2"></i>Detalles de la Orden</h5>
               <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
           </div>
           <div class="modal-body" id="orderDetailsContent">
               <!-- Content will be loaded dynamically via JavaScript -->
           </div>
           <div class="modal-footer">
               <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                   <i class="fas fa-times me-1"></i>Cerrar
               </button>
               <button type="button" class="btn btn-primary" id="printOrderBtn">
                   <i class="fas fa-print me-1"></i>Imprimir
               </button>
           </div>
       </div>
   </div>
</div>

<!-- Add to Order Modal -->
<div class="modal fade" id="addToOrderModal" tabindex="-1" aria-labelledby="addToOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addToOrderModalLabel"><i class="fas fa-plus-circle me-2"></i>Agregar Productos a la Orden</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="addToOrderForm" action="index.php?page=orders" method="post">
                <input type="hidden" name="action" value="add_to_order">
                <input type="hidden" name="order_id" id="add_to_order_id" value="">
                
                <div class="modal-body">
                    <div id="additionalItemsContainer">
                        <div class="additional-item-row mb-3 p-3 border rounded bg-light">
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
                    
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addAdditionalItemBtn">
                        <i class="fas fa-plus me-1"></i>Agregar Otro Producto
                    </button>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save me-1"></i>Agregar a la Orden
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Después del modal de "Add to Order" y antes de los formularios ocultos, agregar este nuevo modal: -->

<!-- Payment Modal -->
<div class="modal fade" id="paymentModal" tabindex="-1" aria-labelledby="paymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="paymentModalLabel"><i class="fas fa-money-bill-wave me-2"></i>Completar Pago</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-7">
                        <div class="card mb-3">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-receipt me-2"></i>Resumen de la Orden</h5>
                                <div class="table-responsive mt-3">
                                    <table class="table table-striped" id="paymentItemsTable">
                                        <thead class="table-primary">
                                            <tr>
                                                <th>Producto</th>
                                                <th class="text-center">Cantidad</th>
                                                <th class="text-end">Precio</th>
                                                <th class="text-end">Subtotal</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <!-- Items will be added dynamically -->
                                        </tbody>
                                    </table>
                                </div>
                                
                                <div class="mt-3 p-3 bg-light rounded">
                                    <div class="d-flex justify-content-between">
                                        <span>Subtotal:</span>
                                        <span id="paymentSubtotal">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between">
                                        <span>IVA (16%):</span>
                                        <span id="paymentTax">$0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between fw-bold border-top pt-2 mt-2">
                                        <span>Total a Pagar:</span>
                                        <span id="paymentTotal" class="text-success fs-5">$0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Información de la Orden</h5>
                                <ul class="list-group list-group-flush">
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Orden #:</span>
                                        <strong id="paymentOrderId">-</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Mesa:</span>
                                        <strong id="paymentTable">-</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Mesero:</span>
                                        <strong id="paymentWaiter">-</strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Fecha:</span>
                                        <strong><?php echo date('d/m/Y'); ?></strong>
                                    </li>
                                    <li class="list-group-item d-flex justify-content-between">
                                        <span>Hora:</span>
                                        <strong id="paymentTime">-</strong>
                                    </li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-md-5">
                        <div class="card bg-success text-white mb-4">
                            <div class="card-body text-center py-4">
                                <h3 class="card-title mb-3"><i class="fas fa-money-bill-wave me-2"></i>Total a Pagar</h3>
                                <div class="display-3 mb-3 fw-bold" id="paymentTotalLarge">$0.00</div>
                                <p class="mb-0">Gracias por su preferencia</p>
                            </div>
                        </div>
                        
                        <div class="card">
                            <div class="card-body">
                                <h5 class="card-title"><i class="fas fa-credit-card me-2"></i>Método de Pago</h5>
                                <div class="mt-3">
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethodCash" value="cash" checked>
                                        <label class="form-check-label" for="paymentMethodCash">
                                            <i class="fas fa-money-bill-wave me-2"></i>Efectivo
                                        </label>
                                    </div>
                                    <div class="form-check mb-3">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethodCard" value="card">
                                        <label class="form-check-label" for="paymentMethodCard">
                                            <i class="fas fa-credit-card me-2"></i>Tarjeta de Crédito/Débito
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="paymentMethod" id="paymentMethodTransfer" value="transfer">
                                        <label class="form-check-label" for="paymentMethodTransfer">
                                            <i class="fas fa-exchange-alt me-2"></i>Transferencia
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="fas fa-times me-1"></i>Cancelar
                </button>
                <button type="button" class="btn btn-outline-primary me-2" id="printPaymentBtn">
                    <i class="fas fa-print me-1"></i>Imprimir Recibo
                </button>
                <button type="button" class="btn btn-success" id="confirmPaymentBtn">
                    <i class="fas fa-check me-1"></i>Confirmar Pago
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Complete Order Form (hidden) -->
<form id="completeOrderForm" action="index.php?page=orders" method="post" style="display: none;">
    <input type="hidden" name="action" value="complete_order">
    <input type="hidden" name="order_id" id="complete_order_id">
</form>

<!-- Delete Order Form (hidden) -->
<form id="deleteOrderForm" action="index.php?page=orders" method="post" style="display: none;">
    <input type="hidden" name="action" value="delete_order">
    <input type="hidden" name="order_id" id="delete_order_id">
</form>

<script>
// Inicializar los eventos para el modal de agregar productos
document.addEventListener('DOMContentLoaded', function() {
    // Configurar el modal de agregar productos
    const addToOrderModal = document.getElementById('addToOrderModal');
    if (addToOrderModal) {
        addToOrderModal.addEventListener('shown.bs.modal', function() {
            // Actualizar los precios iniciales
            const rows = document.querySelectorAll('.additional-item-row');
            rows.forEach(row => {
                const select = row.querySelector('.menu-item-select');
                const quantity = row.querySelector('.item-quantity');
                const price = row.querySelector('.item-price');
                
                if (select.selectedIndex > 0) {
                    const selectedOption = select.options[select.selectedIndex];
                    const itemPrice = selectedOption.getAttribute('data-price') || 0;
                    price.value = (itemPrice * quantity.value).toFixed(2);
                }
            });
        });
    }
});
</script>

