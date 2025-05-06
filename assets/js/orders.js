/**
 * JavaScript for the Orders page
 */

document.addEventListener("DOMContentLoaded", () => {
  // Import bootstrap (if not already globally available)
  let bootstrap // Declare bootstrap variable
  if (typeof bootstrap === "undefined") {
    try {
      window.bootstrap = require("bootstrap") // For environments like Node.js with browserify
      bootstrap = window.bootstrap // Assign to the declared variable
    } catch (e) {
      console.error("Bootstrap is not available. Ensure it is included in your project.")
    }
  }

  // Asegurarse de que bootstrap esté disponible globalmente
  if (typeof bootstrap === "undefined" && window.bootstrap) {
    bootstrap = window.bootstrap
  }

  // Solución para el problema del modal
  const orderDetailsModal = document.getElementById("orderDetailsModal")
  if (orderDetailsModal) {
    // Crear una instancia del modal
    const modalInstance = new bootstrap.Modal(orderDetailsModal, {
      backdrop: "static", // Evita que se cierre al hacer clic fuera
      keyboard: true, // Permite cerrar con ESC
    })

    // Manejar el evento hidden.bs.modal para limpiar recursos
    orderDetailsModal.addEventListener("hidden.bs.modal", () => {
      console.log("Modal cerrado - limpiando recursos")
      // Limpiar el contenido del modal para evitar problemas
      document.getElementById("orderDetailsContent").innerHTML = ""

      // Eliminar cualquier backdrop que pueda haber quedado
      const backdrops = document.querySelectorAll(".modal-backdrop")
      backdrops.forEach((backdrop) => {
        backdrop.remove()
      })

      // Asegurarse de que el body no tenga la clase modal-open
      document.body.classList.remove("modal-open")
      document.body.style.overflow = ""
      document.body.style.paddingRight = ""
    })

    // Manejar el evento de cierre del botón
    const closeButtons = orderDetailsModal.querySelectorAll('[data-bs-dismiss="modal"]')
    closeButtons.forEach((button) => {
      button.addEventListener("click", () => {
        console.log("Botón de cierre presionado")
        modalInstance.hide()
      })
    })
  }

  // Filter orders by status
  const filterButtons = document.querySelectorAll("[data-filter]")
  if (filterButtons.length > 0) {
    filterButtons.forEach((button) => {
      button.addEventListener("click", function () {
        // Update active button
        filterButtons.forEach((btn) => btn.classList.remove("active"))
        this.classList.add("active")

        const filter = this.getAttribute("data-filter")
        const orderCards = document.querySelectorAll(".order-card")

        orderCards.forEach((card) => {
          if (filter === "all" || card.getAttribute("data-status") === filter) {
            card.style.display = ""
          } else {
            card.style.display = "none"
          }
        })
      })
    })
  }

  // View order details
  const viewOrderButtons = document.querySelectorAll(".view-order-details")
  if (viewOrderButtons.length > 0) {
    viewOrderButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        // Prevenir comportamiento predeterminado para mayor control
        event.preventDefault()

        // Obtener los datos directamente del botón que se hizo clic
        const orderId = this.getAttribute("data-id")
        const orderTable = this.getAttribute("data-table")
        const orderWaiter = this.getAttribute("data-waiter")
        const orderTime = this.getAttribute("data-time")
        const orderTotal = Number.parseFloat(this.getAttribute("data-total"))

        console.log("Mostrando detalles de la orden:", {
          id: orderId,
          table: orderTable,
          waiter: orderWaiter,
          time: orderTime,
          total: orderTotal,
        })

        // Show loading state
        const orderDetailsContent = document.getElementById("orderDetailsContent")
        if (!orderDetailsContent) {
          console.error("No se encontró el elemento orderDetailsContent")
          return
        }

        orderDetailsContent.innerHTML = `
          <div class="text-center p-3">
            <h4>Orden #${orderId}</h4>
            <p>Cargando detalles de la orden...</p>
            <div class="spinner-border text-primary" role="status">
              <span class="visually-hidden">Cargando...</span>
            </div>
          </div>
        `

        // Encontrar la tarjeta de orden correspondiente usando el ID exacto
        const orderCard = document.querySelector(`.order-card[data-id="${orderId}"]`)

        if (!orderCard) {
          console.error(`No se encontró la tarjeta de orden con ID ${orderId}`)
          // Intentar cargar desde la API directamente
          loadOrderDetailsFromAPI(orderId, orderTable, orderWaiter, orderTime, orderTotal)
          return
        }

        // Extraer los productos de la tabla en la tarjeta de orden
        const orderItems = []
        const itemRows = orderCard.querySelectorAll(".order-items-table tbody tr")

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

            orderItems.push({
              name: name,
              quantity: quantity,
              price: price,
              subtotal: subtotal,
            })
          } else {
            console.warn(`La fila ${index + 1} no tiene suficientes celdas`)
          }
        })

        console.log(`Total de productos extraídos: ${orderItems.length}`)

        // Si no se encontraron productos en la tabla, intentar cargarlos desde el servidor
        if (orderItems.length === 0) {
          loadOrderDetailsFromAPI(orderId, orderTable, orderWaiter, orderTime, orderTotal)
        } else {
          // Si se encontraron productos en la tabla, actualizar el modal directamente
          updateOrderDetailsModal(orderId, orderTable, orderWaiter, orderTime, orderTotal, orderItems)
        }

        // Mostrar el modal manualmente
        if (orderDetailsModal && typeof bootstrap !== "undefined") {
          const modalInstance = bootstrap.Modal.getInstance(orderDetailsModal)
          if (modalInstance) {
            modalInstance.show()
          } else {
            const newModalInstance = new bootstrap.Modal(orderDetailsModal)
            newModalInstance.show()
          }
        }
      })
    })
  }

  // Función para actualizar el modal con los detalles de la orden
  function updateOrderDetailsModal(orderId, orderTable, orderWaiter, orderTime, orderTotal, orderItems) {
    const orderDetailsContent = document.getElementById("orderDetailsContent")
    if (!orderDetailsContent) {
      console.error("No se encontró el elemento orderDetailsContent")
      return
    }

    // Calculate subtotal and tax
    const subtotal = orderTotal
    const tax = subtotal * 0.16
    const total = subtotal + tax

    // Obtener la fecha actual formateada
    const currentDate = new Date().toLocaleDateString("es-MX", {
      day: "2-digit",
      month: "2-digit",
      year: "numeric",
    })

    // Generate items HTML
    let itemsHtml = ""
    if (orderItems.length > 0) {
      orderItems.forEach((item) => {
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
          <td colspan="4" class="text-center">No se encontraron productos para esta orden</td>
        </tr>
      `
    }

    // Update modal content with order details
    orderDetailsContent.innerHTML = `
    <div class="p-3">
      <div class="row mb-4">
        <div class="col-md-6">
          <div class="card bg-light">
            <div class="card-body">
              <h5 class="card-title"><i class="fas fa-info-circle me-2"></i>Información de la Orden</h5>
              <ul class="list-group list-group-flush">
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Orden #:</span>
                  <strong>${orderId}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Mesa:</span>
                  <strong>${orderTable}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Mesero:</span>
                  <strong>${orderWaiter}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Hora:</span>
                  <strong>${orderTime}</strong>
                </li>
                <li class="list-group-item bg-transparent d-flex justify-content-between">
                  <span>Fecha:</span>
                  <strong>${currentDate}</strong>
                </li>
              </ul>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card bg-primary text-white">
            <div class="card-body text-center">
              <h5 class="card-title"><i class="fas fa-receipt me-2"></i>Resumen</h5>
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
      
      <h5 class="mb-3"><i class="fas fa-utensils me-2"></i>Productos Ordenados</h5>
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
        <h6><i class="fas fa-sticky-note me-2"></i>Notas:</h6>
        <p class="mb-0">Orden #${orderId} para la Mesa ${orderTable}, atendida por ${orderWaiter}.</p>
      </div>
    </div>
  `
  }

  // Complete order buttons - MODIFICADO PARA MOSTRAR MODAL DE PAGO
  const completeOrderButtons = document.querySelectorAll(".complete-order")
  if (completeOrderButtons.length > 0) {
    completeOrderButtons.forEach((button) => {
      button.addEventListener("click", function (event) {
        // Prevenir comportamiento predeterminado
        event.preventDefault()

        const orderId = this.getAttribute("data-id")

        // Encontrar la tarjeta de orden correspondiente
        const orderCard = document.querySelector(`.order-card[data-id="${orderId}"]`)
        if (!orderCard) {
          console.error(`No se encontró la tarjeta de orden con ID ${orderId}`)
          return
        }

        // Extraer información de la orden
        const orderTableMatch = orderCard.querySelector(".card-header h5").textContent.match(/Mesa (\d+)/)
        const orderTable = orderTableMatch ? orderTableMatch[1] : "N/A"

        // Extraer el mesero correctamente - MEJORADO
        let orderWaiter = "N/A"

        // Método 1: Buscar directamente en el span del encabezado
        const headerSpans = orderCard.querySelectorAll(".card-header span")
        headerSpans.forEach((span) => {
          if (span.innerHTML.includes('<i class="fas fa-user me-1"></i>')) {
            orderWaiter = span.textContent.trim()
          }
        })

        // Método 2: Si no se encontró, buscar en cualquier elemento con el icono de usuario
        if (orderWaiter === "N/A") {
          const userIcons = orderCard.querySelectorAll(".fa-user, .fas.fa-user")
          for (const icon of userIcons) {
            if (icon.parentElement) {
              orderWaiter = icon.parentElement.textContent.trim()
              break
            }
          }
        }

        // Método 3: Buscar en cualquier elemento que contenga "Mesero:"
        if (orderWaiter === "N/A") {
          const allElements = orderCard.querySelectorAll("*")
          for (const element of allElements) {
            if (element.textContent.includes("Mesero:")) {
              orderWaiter = element.textContent.replace("Mesero:", "").trim()
              break
            }
          }
        }

        console.log("Mesero extraído:", orderWaiter)

        // Extraer la hora completa (HH:MM) correctamente - MEJORADO
        let orderTime = ""

        // Método 1: Buscar directamente en el span del encabezado
        headerSpans.forEach((span) => {
          if (span.innerHTML.includes('<i class="far fa-clock me-1"></i>')) {
            orderTime = span.textContent.trim()
          }
        })

        // Método 2: Si no se encontró, buscar en cualquier elemento con el icono de reloj
        if (!orderTime) {
          const clockIcons = orderCard.querySelectorAll(".fa-clock, .far.fa-clock")
          for (const icon of clockIcons) {
            if (icon.parentElement) {
              orderTime = icon.parentElement.textContent.trim()
              break
            }
          }
        }

        // Método 3: Buscar en cualquier elemento que contenga "Hora:"
        if (!orderTime) {
          const allElements = orderCard.querySelectorAll("*")
          for (const element of allElements) {
            if (element.textContent.includes("Hora:")) {
              orderTime = element.textContent.replace("Hora:", "").trim()
              break
            }
          }
        }

        // Si aún no se encuentra, usar la hora actual
        if (!orderTime) {
          orderTime = new Date().toLocaleTimeString("es-MX", { hour: "2-digit", minute: "2-digit" })
        }

        console.log("Hora extraída:", orderTime)

        // Extraer la fecha actual formateada
        const currentDate = new Date().toLocaleDateString("es-MX", {
          day: "2-digit",
          month: "2-digit",
          year: "numeric",
        })

        console.log("Fecha actual:", currentDate)

        // Extraer los productos de la tabla
        const orderItems = []
        const itemRows = orderCard.querySelectorAll(".order-items-table tbody tr")

        itemRows.forEach((row) => {
          if (row.cells && row.cells.length >= 4) {
            const name = row.cells[0].textContent.trim()
            const quantity = Number.parseInt(row.cells[1].textContent.trim())
            const priceText = row.cells[2].textContent.trim()
            const subtotalText = row.cells[3].textContent.trim()

            // Extraer solo los números de los textos de precio
            const price = Number.parseFloat(priceText.replace(/[^\d.,]/g, "").replace(",", "."))
            const subtotal = Number.parseFloat(subtotalText.replace(/[^\d.,]/g, "").replace(",", "."))

            orderItems.push({
              name: name,
              quantity: quantity,
              price: price,
              subtotal: subtotal,
            })
          }
        })

        // Obtener el total de la orden directamente de la tabla de totales
        let subtotal = 0
        const totalRow = orderCard.querySelector(".order-items-table tfoot tr:last-child td:last-child")
        if (totalRow) {
          const totalText = totalRow.textContent.trim()
          subtotal = Number.parseFloat(totalText.replace(/[^\d.,]/g, "").replace(",", "."))

          // Si aún es NaN, calcular el total sumando los subtotales de los items
          if (isNaN(subtotal)) {
            subtotal = orderItems.reduce((sum, item) => sum + item.subtotal, 0)
          }
        } else {
          // Si no se encuentra el total en la tabla, calcularlo sumando los subtotales
          subtotal = orderItems.reduce((sum, item) => sum + item.subtotal, 0)
        }

        // Verificar que el total sea un número válido
        if (isNaN(subtotal) || subtotal === 0) {
          // Último intento: buscar el total en el texto de la tarjeta
          const totalTexts = orderCard.querySelectorAll("strong")
          totalTexts.forEach((element) => {
            if (element.textContent.startsWith("$")) {
              const possibleTotal = Number.parseFloat(element.textContent.replace(/[^\d.,]/g, "").replace(",", "."))
              if (!isNaN(possibleTotal) && possibleTotal > 0) {
                subtotal = possibleTotal
              }
            }
          })
        }

        const tax = subtotal * 0.16
        const total = subtotal + tax

        console.log("Datos de la orden para el pago:", {
          id: orderId,
          table: orderTable,
          waiter: orderWaiter,
          time: orderTime,
          date: currentDate,
          subtotal: subtotal,
          tax: tax,
          total: total,
        })

        // Actualizar el modal de pago
        document.getElementById("paymentOrderId").textContent = orderId
        document.getElementById("paymentTable").textContent = orderTable
        document.getElementById("paymentWaiter").textContent = orderWaiter
        document.getElementById("paymentSubtotal").textContent = `$${subtotal.toFixed(2)}`
        document.getElementById("paymentTax").textContent = `$${tax.toFixed(2)}`
        document.getElementById("paymentTotal").textContent = `$${total.toFixed(2)}`
        document.getElementById("paymentTotalLarge").textContent = `$${total.toFixed(2)}`
        document.getElementById("paymentTime").textContent = orderTime

        // Generar HTML para los items
        let itemsHtml = ""
        if (orderItems.length > 0) {
          orderItems.forEach((item) => {
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
              <td colspan="4" class="text-center">No se encontraron productos para esta orden</td>
            </tr>
          `
        }

        // Actualizar la tabla de items
        const paymentItemsTable = document.getElementById("paymentItemsTable")
        if (paymentItemsTable && paymentItemsTable.querySelector("tbody")) {
          paymentItemsTable.querySelector("tbody").innerHTML = itemsHtml
        }

        // Mostrar el modal de pago
        const paymentModal = new bootstrap.Modal(document.getElementById("paymentModal"))
        paymentModal.show()

        // Configurar el botón de confirmar pago
        const confirmPaymentBtn = document.getElementById("confirmPaymentBtn")
        if (confirmPaymentBtn) {
          // Eliminar cualquier evento anterior para evitar duplicados
          const newConfirmBtn = confirmPaymentBtn.cloneNode(true)
          confirmPaymentBtn.parentNode.replaceChild(newConfirmBtn, confirmPaymentBtn)

          newConfirmBtn.addEventListener("click", () => {
            // Ocultar el modal de pago
            paymentModal.hide()

            // Enviar el formulario para completar la orden
            document.getElementById("complete_order_id").value = orderId
            document.getElementById("completeOrderForm").submit()
          })
        }

        // Print payment receipt button
        const printPaymentBtn = document.getElementById("printPaymentBtn")
        if (printPaymentBtn) {
          // Eliminar cualquier evento anterior para evitar duplicados
          const newPrintBtn = printPaymentBtn.cloneNode(true)
          printPaymentBtn.parentNode.replaceChild(newPrintBtn, printPaymentBtn)

          newPrintBtn.addEventListener("click", () => {
            // Obtener los datos del modal de pago
            const orderId = document.getElementById("paymentOrderId").textContent
            const orderTable = document.getElementById("paymentTable").textContent
            const orderWaiter = document.getElementById("paymentWaiter").textContent
            const orderTime = document.getElementById("paymentTime").textContent
            const orderDate = currentDate
            const subtotal = document.getElementById("paymentSubtotal").textContent
            const tax = document.getElementById("paymentTax").textContent
            const total = document.getElementById("paymentTotal").textContent

            // Obtener los items de la tabla
            const itemRows = document.getElementById("paymentItemsTable").querySelectorAll("tbody tr")
            let itemsHtml = ""

            itemRows.forEach((row) => {
              if (row.cells && row.cells.length >= 4) {
                itemsHtml += `
                  <tr>
                    <td>${row.cells[0].textContent}</td>
                    <td class="text-center">${row.cells[1].textContent}</td>
                    <td class="text-end">${row.cells[2].textContent}</td>
                    <td class="text-end">${row.cells[3].textContent}</td>
                  </tr>
                `
              }
            })

            // Obtener el método de pago seleccionado
            let paymentMethod = "Efectivo"
            if (document.getElementById("paymentMethodCard").checked) {
              paymentMethod = "Tarjeta de Crédito/Débito"
            } else if (document.getElementById("paymentMethodTransfer").checked) {
              paymentMethod = "Transferencia"
            }

            // Crear la ventana de impresión
            const printWindow = window.open("", "_blank")

            printWindow.document.write(`
              <!DOCTYPE html>
              <html>
              <head>
                <title>Recibo de Pago - Orden #${orderId}</title>
                <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
                <style>
                  body { padding: 20px; font-family: 'Courier New', monospace; }
                  .receipt { max-width: 80mm; margin: 0 auto; }
                  .receipt-header { text-align: center; margin-bottom: 20px; }
                  .receipt-footer { text-align: center; margin-top: 20px; border-top: 1px dashed #ccc; padding-top: 10px; }
                  @media print {
                    .no-print { display: none; }
                    body { padding: 0; }
                    .receipt { max-width: 100%; }
                  }
                </style>
              </head>
              <body>
                <div class="receipt">
                  <div class="receipt-header">
                    <h3>RestaurantApp</h3>
                    <p>Recibo de Pago</p>
                    <p>Orden #${orderId}</p>
                    <p>${orderDate} - ${orderTime}</p>
                  </div>
                  
                  <div>
                    <p><strong>Mesa:</strong> ${orderTable}</p>
                    <p><strong>Mesero:</strong> ${orderWaiter}</p>
                    <p><strong>Método de Pago:</strong> ${paymentMethod}</p>
                  </div>
                  
                  <div class="mt-3">
                    <table class="table table-sm">
                      <thead>
                        <tr>
                          <th>Producto</th>
                          <th class="text-center">Cant</th>
                          <th class="text-end">Precio</th>
                          <th class="text-end">Total</th>
                        </tr>
                      </thead>
                      <tbody>
                        ${itemsHtml}
                      </tbody>
                      <tfoot>
                        <tr>
                          <td colspan="3" class="text-end"><strong>Subtotal:</strong></td>
                          <td class="text-end">${subtotal}</td>
                        </tr>
                        <tr>
                          <td colspan="3" class="text-end"><strong>IVA (16%):</strong></td>
                          <td class="text-end">${tax}</td>
                        </tr>
                        <tr>
                          <td colspan="3" class="text-end"><strong>Total:</strong></td>
                          <td class="text-end"><strong>${total}</strong></td>
                        </tr>
                      </tfoot>
                    </table>
                  </div>
                  
                  <div class="receipt-footer">
                    <p>¡Gracias por su preferencia!</p>
                    <p>Vuelva pronto</p>
                  </div>
                  
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
      })
    })
  }

  // Delete order buttons
  const deleteOrderButtons = document.querySelectorAll(".delete-order")
  if (deleteOrderButtons.length > 0) {
    deleteOrderButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const orderId = this.getAttribute("data-id")

        if (confirm("¿Está seguro de que desea eliminar esta orden?")) {
          document.getElementById("delete_order_id").value = orderId
          document.getElementById("deleteOrderForm").submit()
        }
      })
    })
  }

  // Add to order buttons
  const addToOrderButtons = document.querySelectorAll(".add-to-order")
  if (addToOrderButtons.length > 0) {
    addToOrderButtons.forEach((button) => {
      button.addEventListener("click", function () {
        const orderId = this.getAttribute("data-id")
        document.getElementById("add_to_order_id").value = orderId

        // Reset form
        const additionalItemsContainer = document.getElementById("additionalItemsContainer")
        const firstRow = additionalItemsContainer.querySelector(".additional-item-row")

        // Keep only the first row and reset its values
        while (additionalItemsContainer.children.length > 1) {
          additionalItemsContainer.removeChild(additionalItemsContainer.lastChild)
        }

        const selectElement = firstRow.querySelector(".menu-item-select")
        const quantityInput = firstRow.querySelector(".item-quantity")
        const priceInput = firstRow.querySelector(".item-price")

        selectElement.selectedIndex = 0
        quantityInput.value = 1
        priceInput.value = "0.00"

        // Inicializar los eventos para los elementos del modal
        setupAdditionalItemRowEvents(firstRow)
      })
    })
  }

  // Add additional item button
  const addAdditionalItemBtn = document.getElementById("addAdditionalItemBtn")
  if (addAdditionalItemBtn) {
    addAdditionalItemBtn.addEventListener("click", () => {
      addAdditionalItemRow()
    })
  }

  // Setup initial additional item row events
  const initialAdditionalRow = document.querySelector(".additional-item-row")
  if (initialAdditionalRow) {
    setupAdditionalItemRowEvents(initialAdditionalRow)
  }

  // Print order button
  const printOrderBtn = document.getElementById("printOrderBtn")
  if (printOrderBtn) {
    printOrderBtn.addEventListener("click", () => {
      const content = document.getElementById("orderDetailsContent").innerHTML
      const printWindow = window.open("", "_blank")

      printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
          <title>Imprimir Orden</title>
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
              <p>Detalle de Orden</p>
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

  // New Order Page functionality
  if (window.location.href.includes("page=new-order")) {
    initNewOrderPage()
  }
})

// Agregar esta nueva función para cargar detalles desde la API
function loadOrderDetailsFromAPI(orderId, orderTable, orderWaiter, orderTime, orderTotal) {
  console.log("Cargando detalles de la orden desde la API...")

  // Mostrar estado de carga en el modal
  const orderDetailsContent = document.getElementById("orderDetailsContent")
  if (orderDetailsContent) {
    orderDetailsContent.innerHTML = `
      <div class="text-center p-5">
        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
          <span class="visually-hidden">Cargando...</span>
        </div>
        <h5>Cargando detalles de la orden #${orderId}...</h5>
      </div>
    `
  }

  // Cargar los detalles de la orden desde el servidor
  fetch(`api/get-order-details.php?id=${orderId}`)
    .then((response) => {
      if (!response.ok) {
        throw new Error(`Error al cargar los detalles de la orden: ${response.status}`)
      }
      return response.json()
    })
    .then((data) => {
      console.log("Datos recibidos del servidor:", data)

      if (data.items && Array.isArray(data.items)) {
        // Crear la lista de productos con los datos del servidor
        const orderItems = data.items.map((item) => ({
          name: item.name,
          quantity: item.quantity,
          price: item.price,
          subtotal: item.price * item.quantity,
        }))

        // Actualizar el modal con los productos cargados
        updateOrderDetailsModal(orderId, orderTable, orderWaiter, orderTime, orderTotal, orderItems)
      } else {
        console.error("Los datos recibidos no contienen una lista de productos válida")
        // Mostrar mensaje de error en el modal
        showErrorInModal("Los datos recibidos no contienen una lista de productos válida")
      }

      // Actualizar la sección de notas en el modal para mostrar las notas reales si están disponibles
      const notesSection = orderDetailsContent.querySelector(".mt-4.p-3.bg-light.rounded")
      if (notesSection && data.notes) {
        notesSection.innerHTML = `
          <h6><i class="fas fa-sticky-note me-2"></i>Notas:</h6>
          <p class="mb-0">${data.notes || "Sin notas adicionales."}</p>
        `
      }
    })
    .catch((error) => {
      console.error("Error al cargar los detalles de la orden:", error)
      // Mostrar mensaje de error en el modal
      showErrorInModal(`Error al cargar los detalles: ${error.message}`)
    })
}

// Función para mostrar errores en el modal
function showErrorInModal(errorMessage) {
  const orderDetailsContent = document.getElementById("orderDetailsContent")
  if (orderDetailsContent) {
    orderDetailsContent.innerHTML = `
      <div class="alert alert-danger m-3" role="alert">
        <h4 class="alert-heading"><i class="fas fa-exclamation-triangle me-2"></i>Error</h4>
        <p>${errorMessage}</p>
        <hr>
        <p class="mb-0">Intente nuevamente o contacte al administrador del sistema.</p>
      </div>
    `
  }
}

/**
 * Add a new additional item row
 */
function addAdditionalItemRow() {
  const container = document.getElementById("additionalItemsContainer")
  const newRow = document.createElement("div")
  newRow.className = "additional-item-row mb-3 p-3 border rounded bg-light"

  // Clone the first row to get all the options
  const firstRow = container.querySelector(".additional-item-row")
  newRow.innerHTML = firstRow.innerHTML

  // Reset the values in the new row
  const selectElement = newRow.querySelector(".menu-item-select")
  const quantityInput = newRow.querySelector(".item-quantity")
  const priceInput = newRow.querySelector(".item-price")

  selectElement.selectedIndex = 0
  quantityInput.value = 1
  priceInput.value = "0.00"

  container.appendChild(newRow)
  setupAdditionalItemRowEvents(newRow)
}

/**
 * Setup event listeners for an additional item row
 *
 * @param {HTMLElement} row The additional item row element
 */
function setupAdditionalItemRowEvents(row) {
  const selectElement = row.querySelector(".menu-item-select")
  const quantityInput = row.querySelector(".item-quantity")
  const priceInput = row.querySelector(".item-price")
  const removeButton = row.querySelector(".remove-item")

  // Update price when item is selected
  selectElement.addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex]
    const price = selectedOption.getAttribute("data-price") || 0
    const quantity = quantityInput.value

    priceInput.value = (price * quantity).toFixed(2)
  })

  // Update price when quantity changes
  quantityInput.addEventListener("change", function () {
    const selectedOption = selectElement.options[selectElement.selectedIndex]
    const price = selectedOption.getAttribute("data-price") || 0
    const quantity = this.value

    priceInput.value = (price * quantity).toFixed(2)
  })

  // Remove item row
  removeButton.addEventListener("click", () => {
    // Don't remove if it's the only row
    const container = document.getElementById("additionalItemsContainer")
    if (container.children.length > 1) {
      row.remove()
    } else {
      // Reset values instead
      selectElement.selectedIndex = 0
      quantityInput.value = 1
      priceInput.value = "0.00"
    }
  })
}

/**
 * Initialize the New Order page functionality
 */
function initNewOrderPage() {
  // Add order item button
  const addOrderItemBtn = document.getElementById("addOrderItemBtn")
  if (addOrderItemBtn) {
    addOrderItemBtn.addEventListener("click", () => {
      addOrderItemRow()
    })
  }

  // Initial order item row event listeners
  const initialOrderRow = document.querySelector(".order-item-row")
  if (initialOrderRow) {
    setupOrderItemRowEvents(initialOrderRow)
  }

  // Update order summary when form fields change
  const tableSelect = document.getElementById("table_number")
  const waiterSelect = document.getElementById("waiter_id")

  if (tableSelect) {
    tableSelect.addEventListener("change", updateOrderSummary)
  }

  if (waiterSelect) {
    waiterSelect.addEventListener("change", updateOrderSummary)
  }

  // Initial summary update
  updateOrderSummary()
}

/**
 * Add a new order item row
 */
function addOrderItemRow() {
  const container = document.getElementById("orderItemsContainer")
  const newRow = document.createElement("div")
  newRow.className = "order-item-row mb-3 p-3 border rounded bg-light"

  // Clone the first row to get all the options
  const firstRow = container.querySelector(".order-item-row")
  newRow.innerHTML = firstRow.innerHTML

  // Reset the values in the new row
  const selectElement = newRow.querySelector(".menu-item-select")
  const quantityInput = newRow.querySelector(".item-quantity")
  const priceInput = newRow.querySelector(".item-price")

  selectElement.selectedIndex = 0
  quantityInput.value = 1
  priceInput.value = "0.00"

  container.appendChild(newRow)
  setupOrderItemRowEvents(newRow)
}

/**
 * Setup event listeners for an order item row
 *
 * @param {HTMLElement} row The order item row element
 */
function setupOrderItemRowEvents(row) {
  const selectElement = row.querySelector(".menu-item-select")
  const quantityInput = row.querySelector(".item-quantity")
  const priceInput = row.querySelector(".item-price")
  const removeButton = row.querySelector(".remove-item")

  // Update price when item is selected
  selectElement.addEventListener("change", function () {
    const selectedOption = this.options[this.selectedIndex]
    const price = selectedOption.getAttribute("data-price") || 0
    const quantity = quantityInput.value

    priceInput.value = (price * quantity).toFixed(2)
    updateOrderSummary()
  })

  // Update price when quantity changes
  quantityInput.addEventListener("change", function () {
    const selectedOption = selectElement.options[selectElement.selectedIndex]
    const price = selectedOption.getAttribute("data-price") || 0
    const quantity = this.value

    priceInput.value = (price * quantity).toFixed(2)
    updateOrderSummary()
  })

  // Remove item row
  removeButton.addEventListener("click", () => {
    // Don't remove if it's the only row
    const container = document.getElementById("orderItemsContainer")
    if (container.children.length > 1) {
      row.remove()
      updateOrderSummary()
    } else {
      // Reset values instead
      selectElement.selectedIndex = 0
      quantityInput.value = 1
      priceInput.value = "0.00"
      updateOrderSummary()
    }
  })
}

/**
 * Update the order summary
 */
function updateOrderSummary() {
  const tableSelect = document.getElementById("table_number")
  const waiterSelect = document.getElementById("waiter_id")
  const summaryTable = document.getElementById("summaryTable")
  const summaryWaiter = document.getElementById("summaryWaiter")
  const summaryItems = document.getElementById("summaryItems")
  const summarySubtotal = document.getElementById("summarySubtotal")
  const summaryTax = document.getElementById("summaryTax")
  const summaryTotal = document.getElementById("summaryTotal")

  // Update table and waiter info
  if (tableSelect && tableSelect.value) {
    summaryTable.innerHTML = `Mesa: <span>${tableSelect.value}</span>`
  } else {
    summaryTable.innerHTML = `Mesa: <span>-</span>`
  }

  if (waiterSelect && waiterSelect.value) {
    const waiterName = waiterSelect.options[waiterSelect.selectedIndex].text
    summaryWaiter.innerHTML = `Mesero: <span>${waiterName}</span>`
  } else {
    summaryWaiter.innerHTML = `Mesero: <span>-</span>`
  }

  // Calculate totals and update items list
  let subtotal = 0
  let itemsHtml = ""

  const orderItems = document.querySelectorAll(".order-item-row")
  if (orderItems.length > 0) {
    orderItems.forEach((item) => {
      const select = item.querySelector(".menu-item-select")
      const quantity = item.querySelector(".item-quantity").value
      const price = item.querySelector(".item-price").value

      if (select.value) {
        const itemName = select.options[select.selectedIndex].text.split(" - ")[0]
        itemsHtml += `
          <li class="list-group-item px-0 d-flex justify-content-between">
            <span>${quantity}x ${itemName}</span>
            <span>$${price}</span>
          </li>
        `
        subtotal += Number.parseFloat(price)
      }
    })
  }

  if (itemsHtml) {
    summaryItems.innerHTML = itemsHtml
  } else {
    summaryItems.innerHTML = `<li class="list-group-item px-0 text-muted">No hay items seleccionados</li>`
  }

  // Update totals
  const tax = subtotal * 0.16
  const total = subtotal + tax

  summarySubtotal.textContent = `$${subtotal.toFixed(2)}`
  summaryTax.textContent = `$${tax.toFixed(2)}`
  summaryTotal.textContent = `$${total.toFixed(2)}`
}

// Función de depuración para verificar los datos de las órdenes
function debugOrderData() {
  // Obtener todos los botones de ver detalles
  const viewButtons = document.querySelectorAll(".view-order-details")

  console.log("Botones de ver detalles encontrados:", viewButtons.length)

  // Mostrar los datos de cada botón
  viewButtons.forEach((button, index) => {
    console.log(`Botón ${index + 1}:`, {
      id: button.getAttribute("data-id"),
      table: button.getAttribute("data-table"),
      waiter: button.getAttribute("data-waiter"),
      time: button.getAttribute("data-time"),
      total: button.getAttribute("data-total"),
    })
  })

  // Verificar las tarjetas de órdenes
  const orderCards = document.querySelectorAll(".order-card")
  console.log("Tarjetas de órdenes encontradas:", orderCards.length)

  orderCards.forEach((card, index) => {
    console.log(`Tarjeta ${index + 1}:`, {
      id: card.getAttribute("data-id"),
      status: card.getAttribute("data-status"),
    })
  })
}

// Ejecutar la función de depuración cuando se cargue la página
document.addEventListener("DOMContentLoaded", () => {
  // Ejecutar depuración si estamos en la página de órdenes
  if (window.location.href.includes("page=orders")) {
    debugOrderData()
  }
})

