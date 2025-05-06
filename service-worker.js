// Nombre de la caché
const CACHE_NAME = "restaurant-app-v1"

// Archivos a cachear
const urlsToCache = [
  "/",
  "/index.php",
  "/offline.html",
  "/assets/css/styles.css",
  "/assets/js/main.js",
  "/assets/js/menus.js",
  "/assets/js/staff.js",
  "/assets/js/orders.js",
  "/assets/js/sales.js",
  "/assets/img/placeholder.jpg",
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css",
  "https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js",
  "https://code.jquery.com/jquery-3.6.0.min.js",
  "https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css",
]

// Instalación del service worker
self.addEventListener("fetch", (event) => {
  event.respondWith(
    caches.match(event.request).then((response) => {
      if (response) {
        return response
      }

      return fetch(event.request)
        .then((networkResponse) => {
          // Verifica que la respuesta sea válida y que sea HTTP
          if (
            !networkResponse ||
            networkResponse.status !== 200 ||
            networkResponse.type !== "basic" ||
            !event.request.url.startsWith("http")
          ) {
            return networkResponse
          }

          // Clona y guarda en caché solo si es http/https
          const responseToCache = networkResponse.clone()

          caches.open(CACHE_NAME).then((cache) => {
            cache.put(event.request, responseToCache).catch((err) => {
              console.warn("No se pudo cachear:", event.request.url, err)
            })
          })

          return networkResponse
        })
        .catch((error) => {
          if (event.request.mode === "navigate") {
            return caches.match("/offline.html")
          }

          return Promise.reject(error)
        })
    }),
  )
})

