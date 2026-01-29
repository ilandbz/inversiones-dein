// src/Composables/useMenuUI.js
import { computed, ref, watch, nextTick, onMounted } from 'vue'
import { useRoute } from 'vue-router'

export default function useMenuUI(menusRef) {
    const route = useRoute()

    const toFeatherIcon = (icono) => icono || 'circle'
    const toUrl = (m) => {
        if (m?.url) return m.url
        if (m?.slug) return `/${String(m.slug).replaceAll('.', '/')}`
        return '#'
    }

    const grouped = computed(() => {
        const menus = menusRef.value ?? []

        if (
            menus.length &&
            (menus[0]?.titulo || menus[0]?.title) &&
            Array.isArray(menus[0]?.menus)
        ) {
            return menus.map((g) => ({
                title: g.titulo ?? g.title,
                items: (g.menus ?? []).slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))
            }))
        }

        const byGroup = new Map()
        for (const m of menus) {
            const groupTitle = m?.grupo_titulo || m?.grupo?.titulo || 'Menú'
            if (!byGroup.has(groupTitle)) byGroup.set(groupTitle, [])
            byGroup.get(groupTitle).push(m)
        }

        return Array.from(byGroup.entries()).map(([title, items]) => ({
            title,
            items: items.slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))
        }))
    })

    const menuUI = computed(() => {
        const out = []

        for (const g of grouped.value) {
            out.push({ isTitle: true, title: g.title, key: `cap-${g.title}` })
            const items = g.items ?? []

            const roots = items
                .filter((m) => m.padre_menu_id == null)
                .slice()
                .sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))

            const childrenByParent = new Map()
            for (const m of items) {
                if (m.padre_menu_id == null) continue
                const pid = String(m.padre_menu_id)
                if (!childrenByParent.has(pid)) childrenByParent.set(pid, [])
                childrenByParent.get(pid).push(m)
            }

            for (const r of roots) {
                const rid = String(r.id)
                const childs = (childrenByParent.get(rid) ?? [])
                    .slice()
                    .sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))

                const node = {
                    key: `m-${rid}`,
                    name: r.nombre ?? r.name,
                    icon: toFeatherIcon(r.icono ?? r.icon),
                    url: toUrl(r),
                    submenu: null
                }

                if (childs.length) {
                    node.url = '#'
                    node.submenu = childs.map((c) => ({
                        key: `s-${c.id}`,
                        name: c.nombre ?? c.name,
                        url: toUrl(c)
                    }))
                }

                out.push(node)
            }
        }

        return out
    })

    // Active
    const currentPath = computed(() => String(route.path ?? '/'))
    const isActive = (url) => {
        if (!url || url === '#') return false
        const cur = currentPath.value
        return cur === url || cur.startsWith(url + '/')
    }

    // Open/Close submenus
    const openMenu = ref(null)
    const submenuRefs = ref({})

    const setSubmenuMaxHeight = async (key) => {
        await nextTick()
        const el = submenuRefs.value[key]
        if (!el) return
        el.style.maxHeight = el.scrollHeight + 'px'
    }

    const closeSubmenuMaxHeight = (key) => {
        const el = submenuRefs.value[key]
        if (!el) return
        el.style.maxHeight = '0px'
    }

    const toggleMenu = async (key) => {
        if (openMenu.value === key) {
            closeSubmenuMaxHeight(key)
            openMenu.value = null
            return
        }
        if (openMenu.value) closeSubmenuMaxHeight(openMenu.value)
        openMenu.value = key
        await setSubmenuMaxHeight(key)
    }

    const syncOpenMenuWithRoute = async () => {
        let keyToOpen = null
        for (const item of menuUI.value) {
            if (!item?.submenu) continue
            if (item.submenu.some((sub) => isActive(sub.url))) {
                keyToOpen = item.key
                break
            }
        }

        if (openMenu.value && openMenu.value !== keyToOpen) {
            closeSubmenuMaxHeight(openMenu.value)
        }

        openMenu.value = keyToOpen
        if (keyToOpen) await setSubmenuMaxHeight(keyToOpen)
    }

    onMounted(syncOpenMenuWithRoute)
    watch(() => route.path, syncOpenMenuWithRoute)
    watch(() => menusRef.value, syncOpenMenuWithRoute, { deep: true })

    return { menuUI, openMenu, submenuRefs, toggleMenu, isActive }
}
