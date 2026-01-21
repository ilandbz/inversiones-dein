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
const toBiIcon = (icono) => icono || 'circle'
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
    return props.menus.map(g => ({
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
        node.url = '#' // el padre no navega si tiene hijos
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

// ---------------- Active ----------------
const currentPath = computed(() => String(route.path ?? '/'))

const isActive = (url) => {
  if (!url || url === '#') return false
  const cur = currentPath.value
  return cur === url || cur.startsWith(url + '/')
}

// ---------------- Open/Close con altura ----------------
const openMenu = ref(null)
const submenuRefs = ref({})

const setSubmenuMaxHeight = async (key) => {
  await nextTick()
  const el = submenuRefs.value[key]
  if (!el) return
  // setea max-height para animación
  el.style.maxHeight = el.scrollHeight + 'px'
}

const closeSubmenuMaxHeight = (key) => {
  const el = submenuRefs.value[key]
  if (!el) return
  el.style.maxHeight = '0px'
}

const toggleMenu = async (key) => {
  // console.log('toggle', key)

  if (openMenu.value === key) {
    closeSubmenuMaxHeight(key)
    openMenu.value = null
    return
  }

  // cerrar anterior si hay
  if (openMenu.value) closeSubmenuMaxHeight(openMenu.value)

  openMenu.value = key
  await setSubmenuMaxHeight(key)
}

// abrir automáticamente si una ruta hija está activa
const syncOpenMenuWithRoute = async () => {
  let keyToOpen = null

  for (const item of menuUI.value) {
    if (!item?.submenu) continue
    if (item.submenu.some(sub => isActive(sub.url))) {
      keyToOpen = item.key
      break
    }
  }

  // cerrar el actual si cambia
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
  <div class="sidebar-wrapper active compact">
    <!-- HEADER -->
    <div class="sidebar-header position-relative">
      <div class="d-flex justify-content-between align-items-center">
        <div class="logo">
          <RouterLink to="/">
            <img src="/assets/imagenes/logo.png" alt="Logo" />
          </RouterLink>
        </div>

        <div class="theme-toggle d-flex gap-2 align-items-center mt-2">
          <div class="form-check form-switch fs-6">
            <input v-model="darkModel" type="checkbox" class="form-check-input me-0" />
            <label class="form-check-label"></label>
          </div>
        </div>

        <div class="sidebar-toggler x">
          <a href="#" class="sidebar-hide d-xl-none d-block">
            <i class="bi bi-x bi-middle"></i>
          </a>
        </div>
      </div>
    </div>

    <!-- MENU -->
    <div class="sidebar-menu">
      <ul class="menu">
        <template v-for="item in menuUI" :key="item.key">
          <!-- TITLE -->
          <li v-if="item.isTitle" class="sidebar-title">
            {{ item.title }}
          </li>

          <!-- ITEM SIN SUBMENU -->
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

          <!-- ITEM CON SUBMENU -->
          <li
            v-else
            class="sidebar-item has-sub"
            :class="{ active: openMenu === item.key }"
          >
            <!-- Mazer espera <a.sidebar-link> -->
            <a
              href="#"
              class="sidebar-link"
              @click.prevent="toggleMenu(item.key)"
            >
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

/* Títulos de sección */
.sidebar-title{
  text-transform: uppercase;
  letter-spacing: .6px;
}

/* Items principales */
.sidebar-link span{
  text-transform: uppercase;
  letter-spacing: .3px;
}

/* Submenús */
.submenu .submenu-link{
  text-transform: uppercase;
  letter-spacing: .25px;
}
.sidebar-title{
  text-transform: uppercase;
  font-size: .75rem;
  opacity: .7;
  margin-top: 1rem;
  margin-bottom: .35rem;
  letter-spacing: .9px;
}
</style>