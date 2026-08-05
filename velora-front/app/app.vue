<script setup>
</script>

<template>
  <NuxtRouteAnnouncer />
  <AppLoading />
  <AppToast />
  <NuxtLayout>
    <NuxtPage />
  </NuxtLayout>
</template>

<style>
html {
  scrollbar-width: none;
}

html::-webkit-scrollbar {
  display: none;
}
</style>