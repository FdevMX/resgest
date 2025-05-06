/**
 * JavaScript for the Sales page
 */

document.addEventListener("DOMContentLoaded", () => {
  // Declare bootstrap variable
  let bootstrap
  if (typeof window.bootstrap === "undefined") {
    try {
      window.bootstrap = require("bootstrap")
      bootstrap = window.bootstrap
    } catch (e) {
      console.error("Bootstrap is not available. Ensure it is included in your project.")
    }
  } else {
    bootstrap = window.bootstrap
  }

  // Declare loadDataWithOfflineSupport (assuming it's defined elsewhere or imported)
  let loadDataWithOfflineSupport

  // Check if loadDataWithOfflineSupport is a function before using it
  if (typeof window.loadDataWithOfflineSupport === "function") {
    loadDataWithOfflineSupport = window.loadDataWithOfflineSupport
  } else {
    console.warn("loadDataWithOfflineSupport is not defined. Ensure it is included in your project.")
    loadDataWithOfflineSupport = () => {
      console.warn("loadDataWithOfflineSupport function is not available.")
    } // Provide a no-op function to prevent errors
  }

  // Cargar datos de ventas con soporte offline
  if (typeof loadDataWithOfflineSupport !== "undefined") {
    loadDataWithOfflineSupport("data/sales.json", "restaurant_sales", (data) => {
      console.log("Datos de ventas cargados:", data)
    })
  }

  // View receipt details
  const viewReceiptButtons = document.querySelectorAll(".view-receipt")
  if (viewReceiptButtons.length > 0) {
    viewReceiptButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        // Prevenir comportamiento predeterminado para mayor control
        event.preventDefault()

        // Obtener los datos directamente del botón que se hizo clic
        const receiptId = this.getAttribute("data-id")
        const receiptTable = this.getAttribute("data-table")
        const receiptWaiter = this.getAttribute("data-waiter")
        const receiptDate = this.getAttribute("data-date")
        const receiptTime = this.getAttribute("data-time")
        const receiptTotal = Number.parseFloat(this.getAttribute("data-total"))

        console.log("Mostrando detalles de la venta:", {
          id: receiptId,
          table: receiptTable,
          waiter: receiptWaiter,
          date: receiptDate,
          time: receiptTime,
          total: receiptTotal,
        })

        // Show loading state
        const receiptDetailsContent = document.getElementById("receiptDetailsContent")
        if (!receiptDetailsContent) {
          console.error("No se encontró el elemento receiptDetailsContent")
          return
        }

        receiptDetailsContent.innerHTML = `
          <div class="text-center p-3">
            <h4>Nota de Venta #${receiptId}</h4>
            <p>Cargando detalles de la nota...</p>
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
        `

        // Encontrar la tarjeta de venta correspondiente usando el ID exacto
        const saleCard = document.querySelector(`.sale-card[data-id="${receiptId}"]`)

        if (!saleCard) {
          console.error(`No se encontró la tarjeta de venta con ID ${receiptId}`)
          return
        }

        // Extraer los productos de la tabla en la tarjeta de venta
        const saleItems = []
        const itemRows = saleCard.querySelectorAll(".sale-item-row")

        console.log(`Encontradas ${itemRows.length} filas de productos`)

        itemRows.forEach((row, index) => {
          console.log(`Procesando fila ${index + 1}:`, row.innerHTML)

          if (row.cells && row.cells.length >= 4) {
            const name = row.cells[0].textContent.trim()
            const quantity = Number.parseInt(row.cells[1].textContent.trim())
            const priceText = row.cells[2].textContent.trim()
            const subtotalText = row.cells[3].textContent.trim()

            // Extraer solo los números de los textos de precio
            const price = Number.parseFloat(priceText.replace(/[^\d.,]/g, "").replace(",", "."))
            const subtotal = Number.parseFloat(subtotalText.replace(/[^\d.,]/g, "").replace(",", "."))

            console.log(`Producto extraído: ${name}, Cantidad: ${quantity}, Precio: ${price}, Subtotal: ${subtotal}`)

            saleItems.push({
              name: name,
              quantity: quantity,
              price: price,
              subtotal: subtotal,
            })
          } else {
            console.warn(`La fila ${index + 1} no tiene suficientes celdas`)
          }
        })

        console.log(`Total de productos extraídos: ${saleItems.length}`)

        // Obtener los totales de la tabla
        const subtotalElement = saleCard.querySelector("tfoot tr:nth-child(1) td:last-child")
        const taxElement = saleCard.querySelector("tfoot tr:nth-child(2) td:last-child")
        const totalElement = saleCard.querySelector("tfoot tr:nth-child(3) td:last-child")

        const subtotalText = subtotalElement ? subtotalElement.textContent.trim() : ""
        const taxText = taxElement ? taxElement.textContent.trim() : ""
        const totalText = totalElement ? totalElement.textContent.trim() : ""

        const subtotal = Number.parseFloat(subtotalText.replace(/[^\d.,]/g, "").replace(",", "."))
        const tax = Number.parseFloat(taxText.replace(/[^\d.,]/g, "").replace(",", "."))
        const total = Number.parseFloat(totalText.replace(/[^\d.,]/g, "").replace(",", "."))

        // Actualizar el modal con los detalles de la venta
        updateReceiptDetailsModal(
          receiptId,
          receiptTable,
          receiptWaiter,
          receiptDate,
          receiptTime,
          subtotal,
          tax,
          total,
          saleItems,
        )

        // Mostrar el modal manualmente
        if (typeof bootstrap !== "undefined") {
          const receiptDetailsModal = document.getElementById("receiptDetailsModal")
          if (receiptDetailsModal) {
            const modalInstance = bootstrap.Modal.getInstance(receiptDetailsModal)
            if (modalInstance) {
              modalInstance.show()
            } else {
              const newModalInstance = new bootstrap.Modal(receiptDetailsModal)
              newModalInstance.show()
            }
          }
        }
      })
    })
  }

  // Función para actualizar el modal con los detalles de la venta
  function updateReceiptDetailsModal(
    receiptId,
    receiptTable,
    receiptWaiter,
    receiptDate,
    receiptTime,
    subtotal,
    tax,
    total,
    saleItems,
  ) {
    const receiptDetailsContent = document.getElementById("receiptDetailsContent")
    if (!receiptDetailsContent) {
      console.error("No se encontró el elemento receiptDetailsContent")
      return
    }

    // Generate items HTML
    let itemsHtml = ""
    if (saleItems.length > 0) {
      saleItems.forEach((item) => {
        itemsHtml += `
          <tr>
            <td>${item.name}</td>
            <td class="text-center">${item.quantity}</td>
            <td class="text-end">$${item.price.toFixed(2)}</td>
            <td class="text-end">$${item.subtotal.toFixed(2)}</td>
          </tr>
        `
      })
    } else {
      itemsHtml = `
        <tr>
          <td colspan="4" class="text-center">No se encontraron productos para esta venta</td>
        </tr>
      `
    }

    // Update modal content with receipt details
    receiptDetailsContent.innerHTML = `
    <div class="p-3">
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body">
              <h5 class="card-title">Información de la Orden</h5>
              <ul class="list-group list-group-flush">
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Orden #:</span>
                  <strong>${receiptId}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Mesa:</span>
                  <strong>${receiptTable}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Mesero:</span>
                  <strong>${receiptWaiter}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Hora:</span>
                  <strong>${receiptTime}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Fecha:</span>
                  <strong>${receiptDate}</strong>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <h5 class="card-title">Resumen</h5>
              <div class="display-4 mb-3">$${total.toFixed(2)}</div>
              <div class="d-flex justify-content-between">
                <span>Subtotal:</span>
                <span>$${subtotal.toFixed(2)}</span>
              </div>
              <div class="d-flex justify-content-between">
                <span>IVA (16%):</span>
                <span>$${tax.toFixed(2)}</span>
              </div>
            </div>
          </div>
        </div>
      </div>
      
      <h5 class="mb-3">Productos Ordenados</h5>
      <div class="table-responsive">
        <table class="table table-striped">
          <thead class="table-primary">
            <tr>
              <th>Producto</th>
              <th class="text-center">Cantidad</th>
              <th class="text-end">Precio</th>
              <th class="text-end">Subtotal</th>
            </tr>
          </thead>
          <tbody>
            ${itemsHtml}
          </tbody>
          <tfoot class="table-light">
            <tr>
              <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
              <td class="text-end"><strong>$${subtotal.toFixed(2)}</strong></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end"><strong>IVA (16%):</strong></td>
              <td class="text-end"><strong>$${tax.toFixed(2)}</strong></td>
            </tr>
            <tr>
              <td colspan="3" class="text-end"><strong>Total:</strong></td>
              <td class="text-end"><strong>$${total.toFixed(2)}</strong></td>
            </tr>
          </tfoot>
        </table>
      </div>
      
      <div class="mt-4 p-3 bg-light rounded">
        <h6>Notas:</h6>
        <p class="mb-0">Orden #${receiptId} para la Mesa ${receiptTable}, atendida por ${receiptWaiter}.</p>
      </div>
    </div>
  `
  }

  // Delete sale buttons
  const deleteSaleButtons = document.querySelectorAll(".delete-sale")
  if (deleteSaleButtons.length > 0) {
    deleteSaleButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const saleId = this.getAttribute("data-id")

        if (confirm("¿Está seguro de que desea eliminar esta nota de venta?")) {
          document.getElementById("delete_sale_id").value = saleId
          document.getElementById("deleteSaleForm").submit()
        }
      })
    })
  }

  // Print receipt button
  const printReceiptBtn = document.getElementById("printReceiptBtn")
  if (printReceiptBtn) {
    printReceiptBtn.addEventListener("click", () => {
      const content = document.getElementById("receiptDetailsContent").innerHTML
      const printWindow = window.open("", "_blank")

      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Imprimir Nota de Venta</title>
          <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
          <style>
            body { padding: 20px; }
            @media print {
              .no-print { display: none; }
            }
          </style>
        </head>
        <body>
          <div class="container">
            <div class="text-center mb-4">
              <h3>RestaurantApp</h3>
              <p>Nota de Venta</p>
            </div>
            ${content}
            <div class="text-center mt-4 no-print">
              <button class="btn btn-primary" onclick="window.print()">Imprimir</button>
            </div>
          </div>
        </body>
        </html>
      `)

      printWindow.document.close()
    })
  }

  // Export sales button
  const exportSalesBtn = document.getElementById("exportSalesBtn")
  if (exportSalesBtn) {
    exportSalesBtn.addEventListener("click", () => {
      // In a real app, you would generate a CSV or PDF file
      // For demo purposes, we'll just show an alert
      alert("Exportando datos de ventas...")
    })
  }

  // Date filter in summary tab
  const applyDateFilter = document.getElementById("applyDateFilter")
  if (applyDateFilter) {
    applyDateFilter.addEventListener("click", () => {
      const startDate = document.getElementById("startDate").value
      const endDate = document.getElementById("endDate").value

      // In a real app, you would filter data based on the date range
      // For demo purposes, we'll just show an alert
      alert(`Filtrando ventas desde ${startDate} hasta ${endDate}`)
    })
  }
})

