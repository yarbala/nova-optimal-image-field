Nova.booting((Vue, router, store) => {
  Vue.component('index-optimal-image', require('./components/IndexField'))
  Vue.component('detail-optimal-image', require('./components/DetailField'))
  Vue.component('form-optimal-image', require('./components/FormField'))
})
