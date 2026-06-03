export default defineNuxtRouteMiddleware((to) => {
  const token = useCookie('auth_token')

  const publicRoutes = ['/login', '/register']
  if (publicRoutes.includes(to.path)) return

  if (!token.value) return navigateTo('/login')
})