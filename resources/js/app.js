import('./bootstrap');
import { createApp } from 'vue/dist/vue.esm-bundler';
import App from './App.vue'

var app=createApp({})
app.component("hello-world" , App)
.mount(".app")
