</div> <!-- End of content-container -->
    </div> <!-- End of app-container -->
    
    <!-- Bootstrap JS Bundle with Popper -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!--    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script> --!>
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <!-- Custom JS -->
    <script src="assets/js/main.js"></script>
    
    <?php if ($page == 'menus'): ?>
    <script src="assets/js/menus.js"></script>
    <?php endif; ?>
    
    <?php if ($page == 'staff'): ?>
    <script src="assets/js/staff.js"></script>
    <?php endif; ?>
    
    <?php if ($page == 'orders' || $page == 'new-order'): ?>
    <script src="assets/js/orders.js"></script>
    <?php endif; ?>
    
    <?php if ($page == 'sales'): ?>
    <script src="assets/js/sales.js"></script>
    <?php endif; ?>
    
    <!-- Registro del Service Worker -->
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', function() {
                navigator.serviceWorker.register('service-worker.js')
                    .then(function(registration) {
                        console.log('Service Worker registrado con éxito:', registration.scope);
                    })
                    .catch(function(error) {
                        console.log('Error al registrar el Service Worker:', error);
                    });
            });
        }
    </script>
</body>
</html>

