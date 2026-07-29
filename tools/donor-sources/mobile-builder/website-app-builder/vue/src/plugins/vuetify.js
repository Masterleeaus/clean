/**
 * plugins/vuetify.js
 *
 * Framework documentation: https://vuetifyjs.com`
 */

// Styles
import '@mdi/font/css/materialdesignicons.css'
import 'vuetify/styles'

// Composables
import { createVuetify } from 'vuetify'

// https://vuetifyjs.com/en/introduction/why-vuetify/#feature-guides
export default createVuetify({
  theme: {
    themes: {
      light: {
        dark: false,
        colors: {
          primary: "#184BFC",
        }
      },
      dark: {
        dark: true,
        colors: {
          primary: "#3f66ff",
          surface: "#09090b",
          background: '#09090b',
        }
      },
    },
  },
})
