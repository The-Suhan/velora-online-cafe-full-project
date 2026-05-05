<template>
    <header class="admin-header">
        <div class="header-left">
            <p class="greeting">
                {{ greeting }}, <span class="user-name">{{ userName }}</span>
                <span class="greeting-suffix">{{ suffix }}</span>
            </p>
        </div>
        <div class="header-right">
            <LanguageSwitcher />
            <div class="header-avatar">
                {{ initials }}
            </div>
        </div>
    </header>
</template>

<script setup lang="ts">
const props = defineProps<{ userName: string }>()
const { locale, t } = useI18n()

const greetingKeys = [
  { prefix: 'admin.header.greetings.g1', suffix: 'admin.header.suffixes.s1' },
  { prefix: 'admin.header.greetings.g2', suffix: 'admin.header.suffixes.s2' },
  { prefix: 'admin.header.greetings.g3', suffix: 'admin.header.suffixes.s3' },
  { prefix: 'admin.header.greetings.g4', suffix: 'admin.header.suffixes.s4' },
  { prefix: 'admin.header.greetings.g5', suffix: 'admin.header.suffixes.s5' },
  { prefix: 'admin.header.greetings.g6', suffix: 'admin.header.suffixes.s6' },
]

const pick = ref(
  greetingKeys[Math.floor(Math.random() * greetingKeys.length)]!
)

const greeting = computed(() => t(pick.value.prefix))
const suffix = computed(() => t(pick.value.suffix))

const initials = computed(() =>
    props.userName
        .split(' ')
        .map(w => w[0])
        .slice(0, 2)
        .join('')
        .toUpperCase()
)
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
    display: flex;
    align-items: baseline;
    gap: 5px;
    flex-wrap: wrap;
}

.user-name {
    color: #4A6741;
    font-weight: 600;
}

.greeting-suffix {
    color: #b0967a;
    font-style: italic;
    font-weight: 400;
    font-size: 1rem;
}

.header-right {
    display: flex;
    align-items: center;
    gap: 12px;
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

    .greeting-suffix {
        display: none;
    }
}
</style>