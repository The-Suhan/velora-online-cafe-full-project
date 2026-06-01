export const useAppLoading = () => {
    const visible = useState('app-loading', () => false)  

    function show() { visible.value = true }
    function hide() { visible.value = false }

    return { visible, show, hide }
}