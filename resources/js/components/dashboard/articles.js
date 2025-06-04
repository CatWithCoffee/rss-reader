export class Articles {
    static init() {
        this.initFavoriteButtons();
    }

    static initFavoriteButtons() {
        document.querySelectorAll('[data-favorite-button]').forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const articleId = button.dataset.articleId;
                this.toggleFavorite(articleId, button);
            });
        });
    }

    static toggleFavorite(articleId, button) {
        const favoriteText = document.getElementById(`favorite-text-${articleId}`);
        const favoriteIcon = document.getElementById(`favorite-icon-${articleId}`);
        
        axios.post(`/favorites/${articleId}`)
            .then(response => {
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
                // Можно добавить уведомление об ошибке
            });
    }
}