export default defineNuxtRouteMiddleware(() => {
  useAuth.validate();

  if (useAuth.isLoggedIn()) {
    return navigateTo('/dashboard', { redirectCode: 301 })
  }
})
