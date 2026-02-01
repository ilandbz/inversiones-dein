import { createApp } from 'vue';
import { createPinia } from 'pinia';
import '@fortawesome/fontawesome-free/css/all.min.css'
import 'sweetalert2/dist/sweetalert2.min.css'
import 'bootstrap-icons/font/bootstrap-icons.css'
// import 'bootstrap/dist/css/bootstrap.min.css'
// import 'bootstrap/dist/js/bootstrap.bundle.min.js'
import SweetAlert2 from 'sweetalert2'
import router from './Router';
import App from './Pages/App.vue';



const app = createApp(App);

app.use(router);
app.use(createPinia())
app.provide('Swal', SweetAlert2);
app.mount('#top');