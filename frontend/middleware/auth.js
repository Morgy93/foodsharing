export default defineNuxtRouteMiddleware((to, from) => {
  useAuth.validate();

  if (!useAuth.isLoggedIn()) {
    return navigateTo('/login', { redirectCode: 301 })
  }
})
