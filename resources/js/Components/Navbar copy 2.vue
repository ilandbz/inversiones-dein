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

// ---------------- Helpers ----------------
const toFeatherIcon = (icono) => icono || 'circle' // tu data trae icono? si no, default
const toUrl = (m) => {
  if (m?.url) return m.url
  if (m?.slug) return `/${String(m.slug).replaceAll('.', '/')}`
  return '#'
}

// ---------------- 1) Agrupar ----------------
const grouped = computed(() => {
  // Ya viene agrupado por GrupoMenu
  if (
    props.menus.length &&
    (props.menus[0]?.titulo || props.menus[0]?.title) &&
    Array.isArray(props.menus[0]?.menus)
  ) {
    return props.menus.map((g) => ({
      title: g.titulo ?? g.title,
      items: (g.menus ?? []).slice().sort((a, b) => (a.orden ?? 0) - (b.orden ?? 0))
    }))
  }

  // Si viniera plano
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

// ---------------- 2) UI por padre_menu_id ----------------
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
        icon: toFeatherIcon(r.icono ?? r.icon), // feather icon name
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

// ---------------- Active ----------------
const currentPath = computed(() => String(route.path ?? '/'))
const isActive = (url) => {
  if (!url || url === '#') return false
  const cur = currentPath.value
  return cur === url || cur.startsWith(url + '/')
}

// ---------------- Open/Close ----------------
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

  if (keyToOpen) {
    await setSubmenuMaxHeight(keyToOpen)
  }
}

onMounted(syncOpenMenuWithRoute)
watch(() => route.path, syncOpenMenuWithRoute)
watch(() => props.menus, syncOpenMenuWithRoute, { deep: true })
</script>

<template>
  <nav class="nxl-navigation">
    <div class="navbar-wrapper">
      <!-- Header -->
      <div class="m-header">
        <RouterLink to="/" class="b-brand">
          <img src="/imagenes/logo_navbar.jpeg" alt="Logo" class="logo logo-lg" />
          <img src="/imagenes/logo_navbar.jpeg" alt="Logo" class="logo logo-sm" />
        </RouterLink>
      </div>

      <div class="navbar-content">
        <ul class="nxl-navbar">
          <template v-for="item in menuUI" :key="item.key">
            <!-- Caption -->
            <li v-if="item.isTitle" class="nxl-item nxl-caption">
              <label>{{ item.title }}</label>
            </li>

            <!-- Item sin submenu -->
            <li
              v-else-if="!item.submenu"
              class="nxl-item"
              :class="{ active: isActive(item.url) }"
            >
              <RouterLink
                v-if="item.url && item.url !== '#'"
                :to="item.url"
                class="nxl-link"
              >
                <span class="nxl-micon">
                  <i :class="`feather-${item.icon}`"></i>
                </span>
                <span class="nxl-mtext">{{ item.name }}</span>
              </RouterLink>

              <a v-else href="javascript:void(0);" class="nxl-link" @click.prevent>
                <span class="nxl-micon">
                  <i :class="`feather-${item.icon}`"></i>
                </span>
                <span class="nxl-mtext">{{ item.name }}</span>
              </a>
            </li>

            <!-- Item con submenu -->
            <li
              v-else
              class="nxl-item nxl-hasmenu"
              :class="{ active: openMenu === item.key }"
            >
              <a href="javascript:void(0);" class="nxl-link" @click.prevent="toggleMenu(item.key)">
                <span class="nxl-micon">
                  <i :class="`feather-${item.icon}`"></i>
                </span>
                <span class="nxl-mtext">{{ item.name }}</span>
                <span class="nxl-arrow">
                  <i class="feather-chevron-right"></i>
                </span>
              </a>

              <ul
                class="nxl-submenu"
                :class="{ active: openMenu === item.key }"
                :ref="el => { if (el) submenuRefs[item.key] = el }"
                style="max-height:0; overflow:hidden; transition:max-height .25s ease;"
              >
                <li
                  v-for="sub in item.submenu"
                  :key="sub.key"
                  class="nxl-item"
                  :class="{ active: isActive(sub.url) }"
                >
                  <RouterLink :to="sub.url" class="nxl-link">
                    {{ sub.name }}
                  </RouterLink>
                </li>
              </ul>
            </li>
          </template>
        </ul>

      </div>
    </div>
  </nav>
</template>

<style scoped>
/* Mantén tu estilo de mayúsculas si lo deseas */
.nxl-caption label {
  text-transform: uppercase;
  letter-spacing: .6px;
}

.nxl-link .nxl-mtext {
  text-transform: uppercase;
  letter-spacing: .3px;
}

/* Submenu */
.nxl-submenu .nxl-link {
  text-transform: uppercase;
  letter-spacing: .25px;
}

/* si el template no resalta active, esto ayuda */
.nxl-item.active > .nxl-link {
  font-weight: 600;
}
</style>
