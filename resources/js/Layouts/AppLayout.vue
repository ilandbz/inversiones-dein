<template>
    
  <component :is="$route.meta.layout || 'div'">
    <router-view />
  </component>


</template>

<script>


// import { onMounted } from 'vue';
// import { useRouter, useRoute } from 'vue-router';

// export default {
//     setup() {
//         const route = useRoute()
//         const router = useRouter()

//         onMounted(() => {
//             if(!localStorage.getItem('userSession') )
//             {
//                 const redirect = route.query.redirect || '/login'
//                 router.push(redirect)
//             }
//         })
//     },
// }
</script>