export default defineNuxtRouteMiddleware(() => {
  if (useAuth.isSecretUser()) {
    throw showError({ statusCode: 403, message: "not allowed" })
  }
})
