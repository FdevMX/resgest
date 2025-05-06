/**
 * Main JavaScript file for Restaurant Management System
 */

// Wait for the DOM to be fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Initialize tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map((tooltipTriggerEl) => new bootstrap.Tooltip(tooltipTriggerEl))
  
    // Show success messages
    const urlParams = new URLSearchParams(window.location.search)
    const status = urlParams.get("status")
  
    if (status === "success") {
      showAlert("Operación completada con éxito", "success")
    } else if (status === "error") {
      showAlert("Ha ocurrido un error. Inténtelo de nuevo.", "danger")
    } else if (status === "created") {
      showAlert("Nuevo registro creado exitosamente", "success")
    }
  
    // Offline detection for PWA
    window.addEventListener("online", updateOnlineStatus)
    window.addEventListener("offline", updateOnlineStatus)
  
  // Add offline indicator to the body
  const offlineIndicator = document.createElement("div")
  offlineIndicator.className = "offline-indicator"
  offlineIndicator.innerHTML = '<i class="fas fa-wifi me-2"></i>Sin conexión'
  document.body.appendChild(offlineIndicator)

  // Ahora que ya existe en el DOM, puedes llamar esto:
  updateOnlineStatus()

  /**
   * Update online status indicator
   */
  function updateOnlineStatus() {
    const offlineIndicator = document.querySelector(".offline-indicator")
  
    if (!navigator.onLine) {
      offlineIndicator.classList.add("visible")
    } else {
      offlineIndicator.classList.remove("visible")
    }
  }
  
  /**
   * Show an alert message
   *
   * @param {string} message The message to display
   * @param {string} type The alert type (success, danger, warning, info)
   */
  function showAlert(message, type = "info") {
    // Create alert element
    const alertDiv = document.createElement("div")
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`
    alertDiv.setAttribute("role", "alert")
    alertDiv.innerHTML = `
          ${message}
          <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
      `
  
    // Add to the top of the content container
    const contentContainer = document.querySelector(".content-container")
    contentContainer.insertBefore(alertDiv, contentContainer.firstChild)
  
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
      const bsAlert = new bootstrap.Alert(alertDiv)
      bsAlert.close()
    }, 5000)
  }
  
  /**
   * Format currency values
   *
   * @param {number} amount The amount to format
   * @returns {string} The formatted amount
   */
  function formatCurrency(amount) {
    return "$" + Number.parseFloat(amount).toFixed(2)
  }
  
  /**
   * Load data via AJAX
   *
   * @param {string} url The URL to fetch data from
   * @param {Function} callback The callback function to handle the response
   */
  function loadData(url, callback) {
    fetch(url)
      .then((response) => response.json())
      .then((data) => callback(data))
      .catch((error) => console.error("Error loading data:", error))
  }
  
  /**
   * Cachear datos JSON en localStorage
   *
   * @param {string} key La clave para almacenar los datos
   * @param {object} data Los datos a almacenar
   */
  function cacheData(key, data) {
    localStorage.setItem(key, JSON.stringify(data))
  }
  
  /**
   * Obtener datos cacheados de localStorage
   *
   * @param {string} key La clave para recuperar los datos
   * @return {object|null} Los datos recuperados o null si no existen
   */
  function getCachedData(key) {
    const data = localStorage.getItem(key)
    return data ? JSON.parse(data) : null
  }
  
  /**
   * Cargar datos con soporte offline
   *
   * @param {string} url La URL para cargar los datos
   * @param {string} cacheKey La clave para cachear los datos
   * @param {Function} callback La función de callback para manejar los datos
   */
  function loadDataWithOfflineSupport(url, cacheKey, callback) {
    // Primero intentamos obtener datos de la red
    fetch(url)
      .then((response) => response.json())
      .then((data) => {
        // Guardamos los datos en caché
        cacheData(cacheKey, data)
        callback(data)
      })
      .catch((error) => {
        console.warn("Error cargando datos, usando caché:", error)
  
        // Si hay un error, intentamos usar datos cacheados
        const cachedData = getCachedData(cacheKey)
        if (cachedData) {
          callback(cachedData)
        } else {
          console.error("No hay datos cacheados disponibles")
        }
      })
  }
  });
  