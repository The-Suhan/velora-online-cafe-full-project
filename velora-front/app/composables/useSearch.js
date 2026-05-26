import { ref } from 'vue'

const searchQuery = ref('')
const searchOpen = ref(false)

export function useSearch() {
    return { searchQuery, searchOpen }
}