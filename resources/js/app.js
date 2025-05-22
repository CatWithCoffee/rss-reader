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

import axios from 'axios';

function toggleFavorite(itemId, button) {
    axios.post(`/favorites/${itemId}`)
        .then(response => {
            // Обновляем текст и иконку кнопки
            const favoriteText = document.getElementById(`favorite-text-${itemId}`);
            const favoriteIcon = document.getElementById(`favorite-icon-${itemId}`);

            if (response.data.isFavorited) {
                favoriteText.textContent = 'Удалить из избранного';
                favoriteIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M5 13l4 4L19 7" />`;
            } else {
                favoriteText.textContent = 'Добавить в избранное';
                favoriteIcon.innerHTML = `
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M12 6v6m0 0v6m0-6h6m-6 0H6" />`;
            }
        })
        .catch(error => {
            console.error('Ошибка:', error);
        });
}

window.toggleFavorite = toggleFavorite;