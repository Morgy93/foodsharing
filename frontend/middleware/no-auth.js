export default defineNuxtRouteMiddleware((to, from) => {
  if (useAuth.isLoggedIn()) {
    return navigateTo('/dashboard', { redirectCode: 301 })
  }
})
