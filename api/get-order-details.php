<?php
// Incluir funciones
require_once '../includes/functions.php';

// Verificar si se recibió un ID de orden
if (isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
    
    // Cargar órdenes
    $orders = loadJsonData('orders');
    
    // Buscar la orden solicitada
    $orderDetails = null;
    foreach ($orders as $order) {
        if ($order['id'] == $orderId) {
            $orderDetails = $order;
            break;
        }
    }
    
    // Si encontramos la orden, devolver los detalles en formato JSON
    if ($orderDetails) {
        // Asegurarse de que los items tengan el formato correcto
        if (isset($orderDetails['items']) && is_array($orderDetails['items'])) {
            foreach ($orderDetails['items'] as &$item) {
                // Asegurarse de que los campos numéricos sean números
                if (isset($item['quantity'])) {
                    $item['quantity'] = intval($item['quantity']);
                }
                if (isset($item['price'])) {
                    $item['price'] = floatval($item['price']);
                }
                
                // Calcular el subtotal si no existe
                if (!isset($item['subtotal']) && isset($item['quantity']) && isset($item['price'])) {
                    $item['subtotal'] = $item['quantity'] * $item['price'];
                }
            }
        }

        // Asegurarse de que los campos necesarios estén presentes
        if (!isset($orderDetails['notes'])) {
            $orderDetails['notes'] = "";
        }
        
        // Calcular impuestos
        $subtotal = $orderDetails['total'];
        $tax = calculateTax($subtotal);
        $total = calculateTotal($subtotal, $tax);
        
        // Agregar los cálculos a la respuesta
        $orderDetails['subtotal'] = $subtotal;
        $orderDetails['tax'] = $tax;
        $orderDetails['total_with_tax'] = $total;

        // Agregar los campos de notas a la respuesta
        $orderDetails['notes'] = $orderDetails['notes'];
        
        // Registrar la respuesta para depuración
        error_log("API Response for Order #$orderId: " . json_encode($orderDetails));
        
        // Devolver la respuesta
        header('Content-Type: application/json');
        echo json_encode($orderDetails);
    } else {
        // Si no se encuentra la orden, devolver un error
        header('HTTP/1.1 404 Not Found');
        echo json_encode(['error' => 'Orden no encontrada']);
    }
} else {
    // Si no se proporcionó un ID, devolver un error
    header('HTTP/1.1 400 Bad Request');
    echo json_encode(['error' => 'ID de orden no proporcionado']);
}
?>

