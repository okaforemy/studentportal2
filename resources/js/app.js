import Vue from 'vue'
import { createInertiaApp } from '@inertiajs/inertia-vue'
import { InertiaProgress } from '@inertiajs/progress'
import Layout from "./Shared/Layout.vue"
import Layout2 from "./Shared/Layout2.vue"
import VueMask from 'v-mask'
import Autocomplete from '@trevoreyre/autocomplete-vue'
import '@trevoreyre/autocomplete-vue/dist/style.css'
import CKEditor from '@ckeditor/ckeditor5-vue2';

//import { library } from '@fortawesome/fontawesome-svg-core'
//import { faHatWizard } from '@fortawesome/free-solid-svg-icons'
//import { FontAwesomeIcon } from '@fortawesome/vue-fontawesome'
 
Vue.use(Autocomplete)
Vue.use( CKEditor )

Vue.use(VueMask);

createInertiaApp({
    resolve: name => {
        const page = require(`./Pages/${name}`).default

        if(window.location.pathname.startsWith('/cbt-exam')){
          page.layout = page.layout || Layout2
        }else{
          page.layout = page.layout || Layout
        }
        return page
      },
  setup({ el, App, props, plugin }) {
    Vue.use(plugin)

    new Vue({
      render: h => h(App, props),
    }).$mount(el)
  },
})


InertiaProgress.init({
    showSpinner: true,
    color: "#29d",
    includeCSS: true,
  })

require('./bootstrap');

