import { ref, watch, computed } from 'vue'
import { useRoute } from 'vue-router'

export default function useMenuUI(menusRef) {
    const route = useRoute()
    const openMenu = ref(null)
    const submenuRefs = {}

    const isActive = (url) => {
        if (!url) return false
        return route.path === url || route.path.startsWith(url + '/')
    }

    // Opcional: abrir automáticamente el padre cuando navegas
    const menuUI = computed(() => {
        // tu mapper actual
        return menusRef.value ?? []
    })

    const toggleMenu = (key) => {
        openMenu.value = (openMenu.value === key) ? null : key
    }

    // ✅ Si cambias de ruta, abre solo el padre del item activo
    watch(
        () => route.fullPath,
        () => {
            const findParentKey = (items) => {
                for (const it of items) {
                    if (it.submenu?.length) {
                        if (it.submenu.some(s => isActive(s.url))) return it.key
                    }
                }
                return null
            }
            openMenu.value = findParentKey(menuUI.value)
        },
        { immediate: true }
    )

    return { menuUI, openMenu, submenuRefs, toggleMenu, isActive }
}