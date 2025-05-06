<?php
/**
* Utility functions for the restaurant management system
*/

/**
* Load JSON data from a file
* 
* @param string $filename The JSON file to load
* @return array The decoded JSON data
*/
function loadJsonData($filename) {
   $jsonFile = 'data/' . $filename . '.json';
   
   if (file_exists($jsonFile)) {
       $jsonData = file_get_contents($jsonFile);
       return json_decode($jsonData, true);
   }
   
   return [];
}

/**
* Save data to a JSON file
* 
* @param string $filename The JSON file to save to
* @param array $data The data to save
* @return bool True if successful, false otherwise
*/
function saveJsonData($filename, $data) {
   $jsonFile = 'data/' . $filename . '.json';
   
   // Crear el directorio si no existe
   if (!file_exists('data')) {
       mkdir('data', 0755, true);
   }
   
   // Convertir a JSON con formato legible
   $jsonData = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
   
   // Guardar el archivo
   $result = file_put_contents($jsonFile, $jsonData);
   
   return $result !== false;
}

/**
* Agregar un nuevo elemento a un archivo JSON
* 
* @param string $filename El archivo JSON
* @param array $newItem El nuevo elemento a agregar
* @param string $arrayKey La clave del array donde agregar (opcional)
* @return bool True si fue exitoso, false en caso contrario
*/
function addJsonItem($filename, $newItem, $arrayKey = null) {
   // Cargar datos existentes
   $data = loadJsonData($filename);
   
   if ($arrayKey) {
       // Si es una estructura anidada (como en staff.json o menus.json)
       foreach ($data as &$category) {
           if ($category['id'] === $arrayKey) {
               // Generar ID único si no existe
               if (!isset($newItem['id'])) {
                   $maxId = 0;
                   foreach ($category['items'] ?? $category['staff'] as $item) {
                       if ($item['id'] > $maxId) {
                           $maxId = $item['id'];
                       }
                   }
                   $newItem['id'] = $maxId + 1;
               }
               
               // Agregar el nuevo elemento
               if (isset($category['items'])) {
                   $category['items'][] = $newItem;
               } else if (isset($category['staff'])) {
                   $category['staff'][] = $newItem;
               }
               break;
           }
       }
   } else {
       // Si es una estructura simple (como en orders.json o sales.json)
       if (!isset($newItem['id'])) {
           $maxId = 0;
           foreach ($data as $item) {
               if (isset($item['id']) && is_numeric($item['id']) && $item['id'] > $maxId) {
                   $maxId = $item['id'];
               }
           }
           $newItem['id'] = $maxId + 1;
       }
       
       // Agregar el nuevo elemento
       $data[] = $newItem;
   }
   
   // Guardar los datos actualizados
   return saveJsonData($filename, $data);
}

/**
* Actualizar un elemento existente en un archivo JSON
* 
* @param string $filename El archivo JSON
* @param array $updatedItem El elemento actualizado
* @param string $arrayKey La clave del array donde actualizar (opcional)
* @return bool True si fue exitoso, false en caso contrario
*/
function updateJsonItem($filename, $updatedItem, $arrayKey = null) {
   // Cargar datos existentes
   $data = loadJsonData($filename);
   
   if ($arrayKey) {
       // Si es una estructura anidada
       foreach ($data as &$category) {
           if ($category['id'] === $arrayKey) {
               if (isset($category['items'])) {
                   foreach ($category['items'] as &$item) {
                       if ($item['id'] == $updatedItem['id']) {
                           $item = $updatedItem;
                           break;
                       }
                   }
               } else if (isset($category['staff'])) {
                   foreach ($category['staff'] as &$item) {
                       if ($item['id'] == $updatedItem['id']) {
                           $item = $updatedItem;
                           break;
                       }
                   }
               }
               break;
           }
       }
   } else {
       // Si es una estructura simple
       foreach ($data as &$item) {
           if ($item['id'] == $updatedItem['id']) {
               $item = $updatedItem;
               break;
           }
       }
   }
   
   // Guardar los datos actualizados
   return saveJsonData($filename, $data);
}

/**
* Eliminar un elemento de un archivo JSON
* 
* @param string $filename El archivo JSON
* @param int $itemId El ID del elemento a eliminar
* @param string $arrayKey La clave del array donde eliminar (opcional)
* @return bool True si fue exitoso, false en caso contrario
*/
function deleteJsonItem($filename, $itemId, $arrayKey = null) {
   // Cargar datos existentes
   $data = loadJsonData($filename);
   
   if ($arrayKey) {
       // Si es una estructura anidada
       foreach ($data as &$category) {
           if ($category['id'] === $arrayKey) {
               if (isset($category['items'])) {
                   foreach ($category['items'] as $key => $item) {
                       if ($item['id'] == $itemId) {
                           array_splice($category['items'], $key, 1);
                           break;
                       }
                   }
               } else if (isset($category['staff'])) {
                   foreach ($category['staff'] as $key => $item) {
                       if ($item['id'] == $itemId) {
                           array_splice($category['staff'], $key, 1);
                           break;
                       }
                   }
               }
               break;
           }
       }
   } else {
       // Si es una estructura simple
       foreach ($data as $key => $item) {
           if ($item['id'] == $itemId) {
               array_splice($data, $key, 1);
               break;
           }
       }
   }
   
   // Guardar los datos actualizados
   return saveJsonData($filename, $data);
}

/**
* Format currency values
* 
* @param float $amount The amount to format
* @return string The formatted amount
*/
function formatCurrency($amount) {
   return '$' . number_format($amount, 2);
}

/**
* Generate a unique ID for new records
* 
* @param string $prefix Prefix for the ID
* @return string The generated ID
*/
function generateId($prefix = '') {
   return $prefix . uniqid();
}

/**
* Calculate tax amount based on subtotal
* 
* @param float $subtotal The subtotal amount
* @param float $taxRate The tax rate (default: 0.16 for 16%)
* @return float The calculated tax amount
*/
function calculateTax($subtotal, $taxRate = 0.16) {
   return $subtotal * $taxRate;
}

/**
* Calculate total amount including tax
* 
* @param float $subtotal The subtotal amount
* @param float $tax The tax amount
* @return float The total amount
*/
function calculateTotal($subtotal, $tax) {
   return $subtotal + $tax;
}

/**
* Get current date and time in the specified format
* 
* @param string $format The date format
* @return string The formatted date and time
*/
function getCurrentDateTime($format = 'd/m/Y H:i:s') {
   return date($format);
}

/**
* Debug function to log data to a file
* 
* @param mixed $data The data to log
* @param string $label Optional label for the log entry
*/
function debugLog($data, $label = '') {
    $logFile = 'debug.log';
    $timestamp = date('Y-m-d H:i:s');
    $logEntry = "[$timestamp]" . ($label ? " [$label]" : "") . ": ";
    
    if (is_array($data) || is_object($data)) {
        $logEntry .= print_r($data, true);
    } else {
        $logEntry .= $data;
    }
    
    file_put_contents($logFile, $logEntry . "\n", FILE_APPEND);
}
?>