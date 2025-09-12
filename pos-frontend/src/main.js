//import './assets/main.css'

import { createApp } from 'vue'
import { createPinia } from 'pinia'

import App from './App.vue'
import router from './router'
import Toast, { POSITION } from 'vue-toastification'
import 'vue-toastification/dist/index.css'

//Vuetify
import 'vuetify/styles'
//import '@mdi/font/css/materialdesignicons.css'
import { createVuetify } from 'vuetify'
import * as components from 'vuetify/components'
import * as directives from 'vuetify/directives'

const vuetify =createVuetify(
    {
        components,
        directives,
        icons: {
            iconfont: 'mdi',
        },
    }
)


const app = createApp(App)

app.use(createPinia())
app.use(router)
app.use(vuetify)
app.use(Toast, {
  position: POSITION.TOP_CENTER,
  timeout: 3000,
})

app.mount('#app')
