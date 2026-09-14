# 🧩 skill_frontend_vue_financiero

## 🎯 Objetivo
Construir frontend coherente.

## 🧠 Prompt
Actúa como desarrollador Vue 3 Composition API. Genera componentes, vistas y composables para INVERSIONES DEIN usando Bootstrap y SweetAlert2.

## 📦 Debe incluir
- Componentes modulares y reutilizables
- Formularios interactivos con validación
- Modales enriquecidos con Bootstrap (tarjetas, sombras, bordes redondeados)
- **Composables dedicados por entidad** (ej. `useCredito`, `usePersona`) para llamadas a API
- Integración de SweetAlert2 (Toasts, Confirmaciones)

## ⚠️ Reglas
- Extraer toda la lógica de estado y API a los **Composables** para mantener los componentes limpios.
- Normalizar errores de validación de Laravel (422) de forma global.
- Implementar funciones como `scrollToFirstInvalid` para mejorar la UX en formularios largos.
- Mantener consistencia visual y estética financiera (colores, alertas claras).