<template>
    <header class="admin-header">
        <div class="header-left">
            <p class="greeting">{{ greeting }}: <span class="user-name">{{ userName }}</span></p>
        </div>
        <div class="header-right">
            <div class="header-avatar">
                {{ initials }}
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
const props = defineProps<{ userName: string }>()

const greetings = [
    'Welcome back',
    'Good to see you',
    'Hello again',
    'Great to have you',
    'Ready to manage',
]

const greeting = ref<string>('')

onMounted(() => {
    greeting.value = greetings[Math.floor(Math.random() * greetings.length)] ?? 'Welcome back'
})

const initials = computed(() => {
    return props.userName
        .split(' ')
        .map(w => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
})
</script>

<style scoped>
.admin-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 16px 24px;
    background: #fff;
    border-bottom: 1px solid #E8E2D9;
    position: sticky;
    top: 0;
    z-index: 10;
}

.greeting {
    font-family: 'Cormorant Garamond', serif;
    font-size: 1.15rem;
    font-weight: 500;
    color: #2C1810;
    margin: 0;
}

.user-name {
    color: #4A6741;
    font-weight: 600;
}

.header-avatar {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #2C1810;
    color: #C8A96E;
    font-size: 0.78rem;
    font-weight: 600;
    font-family: 'Jost', sans-serif;
    display: flex;
    align-items: center;
    justify-content: center;
    letter-spacing: 0.05em;
}

@media (max-width: 768px) {
    .admin-header {
        padding: 12px 16px;
    }

    .greeting {
        font-size: 1rem;
    }
}
</style>