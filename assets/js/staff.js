/**
 * JavaScript for the Staff page
 */

document.addEventListener("DOMContentLoaded", () => {
  // Handle staff search
  const staffSearch = document.getElementById("staffSearch")
  if (staffSearch) {
    staffSearch.addEventListener("input", function () {
      const searchTerm = this.value.toLowerCase()
      const staffItems = document.querySelectorAll(".staff-list .list-group-item")

      staffItems.forEach((item) => {
        const staffName = item.querySelector("h5").textContent.toLowerCase()
        const staffRole = item.querySelector("p").textContent.toLowerCase()

        if (staffName.includes(searchTerm) || staffRole.includes(searchTerm)) {
          item.style.display = ""
        } else {
          item.style.display = "none"
        }
      })
    })
  }

  // Handle edit staff button clicks
  const editButtons = document.querySelectorAll(".edit-staff")
  editButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.getAttribute("data-id")
      const name = this.getAttribute("data-name")
      const apPaterno = this.getAttribute("data-appaterno")
      const apMaterno = this.getAttribute("data-apmaterno")
      const role = this.getAttribute("data-role")
      const rfc = this.getAttribute("data-rfc")

      // Set form values
      document.getElementById("staff_id").value = id
      document.getElementById("staff_name").value = name
      document.getElementById("staff_appaterno").value = apPaterno
      document.getElementById("staff_apmaterno").value = apMaterno
      document.getElementById("staff_role").value = role
      document.getElementById("staff_rfc").value = rfc

      // Update modal title
      document.getElementById("addStaffModalLabel").textContent = "Editar Empleado"

      // Show modal
      const modalElement = document.getElementById("addStaffModal")
      const modal = new bootstrap.Modal(modalElement)
      modal.show()
    })
  })

  // Handle delete staff button clicks
  const deleteButtons = document.querySelectorAll(".delete-staff")
  deleteButtons.forEach((button) => {
    button.addEventListener("click", function () {
      const id = this.getAttribute("data-id")
      const category = this.getAttribute("data-category")

      if (confirm("¿Está seguro de que desea eliminar este empleado?")) {
        document.getElementById("delete_staff_id").value = id
        document.getElementById("delete_staff_category").value = category
        document.getElementById("deleteStaffForm").submit()
      }
    })
  })

  // Reset form when modal is closed
  const staffModal = document.getElementById("addStaffModal")
  if (staffModal) {
    staffModal.addEventListener("hidden.bs.modal", () => {
      document.getElementById("staffForm").reset()
      document.getElementById("staff_id").value = ""
      document.getElementById("addStaffModalLabel").textContent = "Agregar Empleado"
    })
  }
})

