<script setup>
import { ref, computed, watch, nextTick, onMounted } from 'vue'
import { RouterLink, useRoute } from 'vue-router'

const route = useRoute()

const props = defineProps({
  isDark: { type: Boolean, default: false },
  menus: { type: Array, default: () => [] }
})

const emit = defineEmits(['update:isDark'])

const darkModel = computed({
  get: () => props.isDark,
  set: (v) => emit('update:isDark', v)
})

// Helpers
const toBiIcon = (icono) => icono || 'circle'
const toUrl = (m) => {
  if (m?.url) return m.url
  if (m?.slug) return `/${String(m.slug).replaceAll('.', '/')}`
  return '#'
}

// Agrupar
const grouped = computed(() => {
  if (
    props.menus.length &&
    (props.menus[0]?.titulo || props.menus[0]?.title) &&
    Array.isArray(props.menus[0]?.menus)
  ) {
    return props.menus.map(g => ({
      title: g.titulo ?? g.title,
      items: (g.menus ?? []).slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))
    }))
  }

  const byGroup = new Map()
  for (const m of props.menus) {
    const groupTitle = m?.grupo_titulo || m?.grupo?.titulo || 'Menú'
    if (!byGroup.has(groupTitle)) byGroup.set(groupTitle, [])
    byGroup.get(groupTitle).push(m)
  }

  return Array.from(byGroup.entries()).map(([title, items]) => ({
    title,
    items: items.slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))
  }))
})

// UI
const menuUI = computed(() => {
  const out = []
  for (const g of grouped.value) {
    out.push({ isTitle: true, title: g.title, key: `title-${g.title}` })

    const items = g.items ?? []

    const roots = items
      .filter(m => m.padre_menu_id == null)
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
        key: `menu-${rid}`,
        name: r.nombre ?? r.name,
        icon: toBiIcon(r.icono ?? r.icon),
        url: toUrl(r),
        submenu: null
      }

      if (childs.length) {
        node.url = '#'
        node.submenu = childs.map(c => ({
          key: `sub-${c.id}`,
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

// Submenu
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
    if (item.submenu.some(sub => isActive(sub.url))) {
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
watch(() => props.menus, syncOpenMenuWithRoute, { deep: true })
</script>

<template>
  <div class="sidebar-wrapper compact">
    <div class="sidebar-header position-relative">
      <div class="d-flex justify-content-between align-items-center">
        <div class="logo">
          <RouterLink to="/" class="logo-link">
            <span class="logo-box">
              <img src="/assets/imagenes/logo.jpeg" alt="Logo" class="app-logo" />
            </span>
          </RouterLink>
        </div>

        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
          <div class="form-check form-switch fs-6">
            <input v-model="darkModel" type="checkbox" class="form-check-input me-0" />
            <label class="form-check-label"></label>
          </div>
        </div>

        <div class="sidebar-toggler x">
          <a href="#" class="sidebar-hide d-xl-none d-block" @click.prevent>
            <i class="bi bi-x bi-middle"></i>
          </a>
        </div>
      </div>
    </div>

    <div class="sidebar-menu">
      <ul class="menu">
        <template v-for="item in menuUI" :key="item.key">
          <li v-if="item.isTitle" class="sidebar-title">
            {{ item.title }}
          </li>

          <li
            v-else-if="!item.submenu"
            class="sidebar-item"
            :class="{ active: isActive(item.url) }"
          >
            <RouterLink
              v-if="item.url && item.url !== '#'"
              :to="item.url"
              class="sidebar-link"
            >
              <i :class="`bi bi-${item.icon}`"></i>
              <span>{{ item.name }}</span>
            </RouterLink>

            <a v-else href="#" class="sidebar-link" @click.prevent>
              <i :class="`bi bi-${item.icon}`"></i>
              <span>{{ item.name }}</span>
            </a>
          </li>

          <li
            v-else
            class="sidebar-item has-sub"
            :class="{ active: openMenu === item.key }"
          >
            <a href="#" class="sidebar-link" @click.prevent="toggleMenu(item.key)">
              <i :class="`bi bi-${item.icon}`"></i>
              <span>{{ item.name }}</span>
            </a>

            <ul
              class="submenu"
              :class="{ active: openMenu === item.key }"
              :ref="el => { if (el) submenuRefs[item.key] = el }"
              style="max-height:0; overflow:hidden; transition:max-height .25s ease;"
            >
              <li
                v-for="sub in item.submenu"
                :key="sub.key"
                class="submenu-item"
                :class="{ active: isActive(sub.url) }"
              >
                <RouterLink :to="sub.url" class="submenu-link">
                  {{ sub.name }}
                </RouterLink>
              </li>
            </ul>
          </li>
        </template>
      </ul>
    </div>
  </div>
</template>

<style scoped>
.sidebar-title{
  text-transform: uppercase;
  font-size: .75rem;
  opacity: .7;
  margin-top: 1rem;
  margin-bottom: .35rem;
  letter-spacing: .9px;
}
.sidebar-link span{
  text-transform: uppercase;
  letter-spacing: .3px;
}
.submenu .submenu-link{
  text-transform: uppercase;
  letter-spacing: .25px;
}
.sidebar-header .logo{
  display: flex;
  align-items: center;
}
.sidebar-header .logo-box{
  width: 130px !important;
  height: 130px !important;
  padding: 4px;
  border-radius: 18px;
  display: grid;
  place-items: center;
  background: rgba(255,255,255,.10);
  border: 1px solid rgba(255,255,255,.18);
  box-shadow: 0 10px 26px rgba(0,0,0,.25);
}
.sidebar-header .logo-box img,
.sidebar-header .logo img.app-logo{
  width: 100% !important;
  height: 100% !important;
  max-width: none !important;
  max-height: none !important;
  object-fit: contain !important;
  border-radius: 12px;
  display: block;
}
</style>
