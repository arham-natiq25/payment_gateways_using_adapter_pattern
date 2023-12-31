import './bootstrap';

import { createApp } from "vue";
import payment from "./components/payment.vue";

const app = createApp({});

app.component('payment-section',payment);
app.mount("#app");










import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();
