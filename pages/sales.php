<?php
// Load sales data from JSON file
$sales = loadJsonData('sales');

// Calculate totals
$dailyTotal = 0;
$dailySales = count($sales);

foreach ($sales as $sale) {
    $dailyTotal += $sale['total'];
}

// Handle form submission for filtering sales
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'filter_sales') {
        // En una aplicación real, aquí filtrarías las ventas
        // Para esta demo, simplemente recargamos la página
        header('Location: index.php?page=sales&status=filtered');
        exit;
    }
    
    // Eliminar venta
    if ($_POST['action'] === 'delete_sale') {
        $saleId = $_POST['sale_id'];
        
        // Buscar y eliminar la venta
        foreach ($sales as $key => $sale) {
            if ($sale['id'] === $saleId) {
                array_splice($sales, $key, 1);
                break;
            }
        }
        
        // Guardar los cambios
        saveJsonData('sales', $sales);
        
        // Redirigir con mensaje de éxito
        header('Location: index.php?page=sales&status=success');
        exit;
    }
}
?>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h2"><i class="fas fa-file-invoice-dollar me-2"></i>Ventas</h1>
        <div>
            <button type="button" class="btn btn-primary" id="exportSalesBtn">
                <i class="fas fa-download me-2"></i>Exportar
            </button>
        </div>
    </div>
    
    <!-- Sales Summary Cards -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Ventas del Día</h6>
                    <h2 class="card-title"><?php echo formatCurrency($dailyTotal); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-subtitle mb-2 text-muted">Número de Ventas</h6>
                    <h2 class="card-title"><?php echo $dailySales; ?></h2>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Sales Tabs -->
    <ul class="nav nav-tabs mb-4" id="salesTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="receipts-tab" data-bs-toggle="tab" data-bs-target="#receipts" type="button" role="tab">
                Notas de Venta
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="summary-tab" data-bs-toggle="tab" data-bs-target="#summary" type="button" role="tab">
                Resumen
            </button>
        </li>
    </ul>
    
    <!-- Tab Content -->
    <div class="tab-content" id="salesTabsContent">
        <!-- Receipts Tab -->
        <div class="tab-pane fade show active" id="receipts" role="tabpanel">
            <?php foreach ($sales as $sale): ?>
            <div class="card mb-4 sale-card" data-status="completed" data-id="<?php echo $sale['id']; ?>">
                <div class="card-header bg-secondary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-utensils me-2"></i>Mesa <?php echo $sale['table']; ?>
                            <span class="badge bg-secondary ms-2">
                        Completada
                    </span>
                        </h5>
                        <div>
                            <span class="me-3"><i class="far fa-clock me-1"></i><?php echo $sale['time']; ?></span>
                            <span><i class="fas fa-user me-1"></i><?php echo $sale['waiter']; ?></span>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-8">
                            <h6 class="card-subtitle mb-3 text-primary">Productos vendidos:</h6>
                            <div class="table-responsive">
                                <table class="table table-hover sale-items-table">
                                    <thead class="table-light">
                                    <tr>
                                        <th>Producto</th>
                                        <th class="text-center">Cantidad</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-end">Subtotal</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php foreach ($sale['items'] as $item): ?>
                                        <tr class="sale-item-row">
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
                                        <td class="text-end"><strong><?php echo formatCurrency($sale['total']); ?></strong></td>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="card h-100 bg-light">
                                <div class="card-body">
                                    <h5 class="card-title text-center mb-3">Acciones</h5>
                                    <div class="d-grid gap-2">
                                        <button type="button" class="btn btn-outline-primary view-receipt"
                                                data-id="<?php echo $sale['id']; ?>"
                                                data-table="<?php echo $sale['table']; ?>"
                                                data-waiter="<?php echo $sale['waiter']; ?>"
                                                data-date="<?php echo $sale['date']; ?>"
                                                data-time="<?php echo $sale['time']; ?>"
                                                data-total="<?php echo $sale['total']; ?>">
                                            <i class="fas fa-eye me-1"></i>Ver Detalles
                                        </button>

                                        <button type="button" class="btn btn-danger delete-sale"
                                                data-id="<?php echo $sale['id']; ?>">
                                            <i class="fas fa-trash me-1"></i>Eliminar Venta
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
        
        <!-- Summary Tab -->
        <div class="tab-pane fade" id="summary" role="tabpanel">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title mb-4">Resumen de Ventas</h5>
                    
                    <!-- Date Range Selector -->
                    <div class="mb-4 d-flex align-items-center">
                        <i class="fas fa-calendar me-3 text-primary"></i>
                        <div class="d-flex flex-wrap align-items-center">
                            <input type="date" class="form-control me-2 mb-2 mb-md-0" id="startDate" value="<?php echo date('Y-m-d'); ?>">
                            <span class="me-2 mb-2 mb-md-0">a</span>
                            <input type="date" class="form-control me-2 mb-2 mb-md-0" id="endDate" value="<?php echo date('Y-m-d'); ?>">
                            <button type="button" class="btn btn-primary mb-2 mb-md-0" id="applyDateFilter">Aplicar</button>
                        </div>
                    </div>
                    
                    <!-- Sales by Category -->
                    <div class="card mb-4">
                        <div class="card-body">
                            <h6 class="card-title">Ventas por Categoría</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Desayunos</span>
                                    <span class="fw-medium">$0.00</span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Comidas</span>
                                    <span class="fw-medium">$556.80</span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Cenas</span>
                                    <span class="fw-medium">$208.80</span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Bebidas</span>
                                    <span class="fw-medium">$0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sales by Waiter -->
                    <div class="card">
                        <div class="card-body">
                            <h6 class="card-title">Ventas por Mesero</h6>
                            <div class="list-group list-group-flush">
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Carlos Rodríguez</span>
                                    <span class="fw-medium">$359.60</span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>María González</span>
                                    <span class="fw-medium">$197.20</span>
                                </div>
                                <div class="list-group-item px-0 d-flex justify-content-between">
                                    <span>Juan Martínez</span>
                                    <span class="fw-medium">$208.80</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Receipt Details Modal -->
<div class="modal fade" id="receiptDetailsModal" tabindex="-1" aria-labelledby="receiptDetailsModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="receiptDetailsModalLabel">Detalles de la Orden</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body" id="receiptDetailsContent">
                <!-- Content will be loaded dynamically via JavaScript -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="button" class="btn btn-primary" id="printReceiptBtn">
                    <i class="fas fa-print me-1"></i>Imprimir
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Delete Sale Form (hidden) -->
<form id="deleteSaleForm" action="index.php?page=sales" method="post" style="display: none;">
    <input type="hidden" name="action" value="delete_sale">
    <input type="hidden" name="sale_id" id="delete_sale_id">
</form>

