<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <!-- Configuración de vista responsiva -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- Manifest -->
    <link rel="manifest" href="manifest.json">
    <!-- Color de la barra superior (Android) -->
    <meta name="theme-color" content="#0d6efd">
    <!-- Descripción de la aplicación -->
    <meta name="description" content="Sistema de Gestión de Restaurante - Administre menús, personal, órdenes y ventas">
    <!-- Para Android (Chrome, etc.) -->
    <meta name="mobile-web-app-capable" content="yes">
    <!-- Para iOS (Safari) -->
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <title>Sistema de Gestión de Restaurante</title>  
    <!-- Iconos para iOS -->
    <link rel="apple-touch-icon" href="assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="152x152" href="assets/icons/icon-152x152.png">
    <link rel="apple-touch-icon" sizes="180x180" href="assets/icons/icon-192x192.png">
    <link rel="apple-touch-icon" sizes="167x167" href="assets/icons/icon-152x152.png"> 
    <!-- Icono para navegadores -->
    <link rel="icon" type="image/png" sizes="32x32" href="assets/icons/icon-72x72.png">

    <!-- Bootstrap Bundle JS (incluye Popper) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<!--    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet"> --!>
    <!-- Font Awesome for icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Custom CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
</head>
<body>
    <div class="app-container">
        <!-- Navigation Header -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
            <div class="container-fluid">
                <a class="navbar-brand" href="index.php">
                    <i class="fas fa-utensils me-2"></i>
                    RestaurantApp
                </a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarNav">
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'home') ? 'active' : ''; ?>" href="index.php">Inicio</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'menus') ? 'active' : ''; ?>" href="index.php?page=menus">Menús</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'staff') ? 'active' : ''; ?>" href="index.php?page=staff">Personal</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'orders') ? 'active' : ''; ?>" href="index.php?page=orders">Órdenes</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link <?php echo ($page == 'sales') ? 'active' : ''; ?>" href="index.php?page=sales">Ventas</a>
                        </li>
                    </ul>
                </div>
            </div>
        </nav>
        
        <!-- Main Content Container -->
        <div class="content-container">

