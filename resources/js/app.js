import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

import Swal from 'sweetalert2';

window.Swal = Swal;

import { MaskInput } from 'maska';

new MaskInput("[data-maska]", {
    mask: "!#HHHHHH",
    tokens: {
    'H': { pattern: /[0-9a-fA-F]/ }
    },
})