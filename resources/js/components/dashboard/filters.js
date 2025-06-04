export class Filters {
    static init() {
        this.initSourceSearch();
        this.initCategorySearch();
    }

    static initSourceSearch() {
        const sourceSearch = document.getElementById('sourceSearch');
        if (!sourceSearch) return;

        const datalist = document.getElementById('sourcesList');
        const hiddenInput = document.getElementById('sourceId');

        sourceSearch.addEventListener('input', function() {
            const option = Array.from(datalist.options)
                .find(opt => opt.value.toLowerCase() === this.value.toLowerCase());
            hiddenInput.value = option ? option.dataset.value : '';
        });
    }

    static initCategorySearch() {
        const categorySearch = document.getElementById('categorySearch');
        if (!categorySearch) return;

        const categoriesList = document.getElementById('categoriesList');

        categorySearch.addEventListener('input', this.debounce(function(e) {
            const query = e.target.value.trim();
            if (query.length < 2) {
                categoriesList.innerHTML = '';
                return;
            }

            fetch(`${window.location.origin}/dashboard/search?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) throw new Error('Network response was not ok');
                    return response.json();
                })
                .then(data => {
                    categoriesList.innerHTML = '';
                    data.forEach(category => {
                        const option = document.createElement('option');
                        option.value = category;
                        categoriesList.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        }, 300));
    }

    static debounce(func, wait) {
        let timeout;
        return function() {
            const context = this, args = arguments;
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(context, args), wait);
        };
    }
}