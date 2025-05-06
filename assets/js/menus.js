/**
 * JavaScript for the Menus page
 */

document.addEventListener("DOMContentLoaded", () => {
  // Handle edit menu item button clicks
  const editButtons = document.querySelectorAll(".edit-menu-item")
  editButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.getAttribute("data-id")
      const name = this.getAttribute("data-name")
      const price = this.getAttribute("data-price")
      const category = this.getAttribute("data-category")

      // Set form values
      document.getElementById("item_id").value = id
      document.getElementById("item_name").value = name
      document.getElementById("item_price").value = price
      document.getElementById("item_category").value = category

      // Update modal title
      document.getElementById("addMenuItemModalLabel").textContent = "Editar Ítem del Menú"

      // Show modal
      const modalElement = document.getElementById("addMenuItemModal")
      const modal = new bootstrap.Modal(modalElement)
      modal.show()
    })
  })

  // Handle delete menu item button clicks
  const deleteButtons = document.querySelectorAll(".delete-menu-item")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.getAttribute("data-id")
      const category = this.getAttribute("data-category")

      if (confirm("¿Está seguro de que desea eliminar este ítem del menú?")) {
        document.getElementById("delete_item_id").value = id
        document.getElementById("delete_item_category").value = category
        document.getElementById("deleteMenuItemForm").submit()
      }
    })
  })

  // Reset form when modal is closed
  const menuItemModal = document.getElementById("addMenuItemModal")
  menuItemModal.addEventListener("hidden.bs.modal", () => {
    document.getElementById("menuItemForm").reset()
    document.getElementById("item_id").value = ""
    document.getElementById("addMenuItemModalLabel").textContent = "Agregar Ítem al Menú"
  })
})

