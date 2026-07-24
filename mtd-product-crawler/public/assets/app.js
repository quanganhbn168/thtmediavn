(() => {
    const searchInput = document.querySelector('#searchInput');
    const brandFilter = document.querySelector('#brandFilter');
    const stockFilter = document.querySelector('#stockFilter');
    const cards = [...document.querySelectorAll('.product-card')];
    const resultCount = document.querySelector('#resultCount');
    const noResults = document.querySelector('#noResults');

    if (!searchInput || !brandFilter || !stockFilter || cards.length === 0) {
        return;
    }

    const normalize = (value) => (value || '')
        .normalize('NFD')
        .replace(/[\u0300-\u036f]/g, '')
        .toLowerCase()
        .trim();

    const brands = [...new Set(
        cards
            .map(card => card.dataset.brand?.trim())
            .filter(Boolean)
    )].sort((a, b) => a.localeCompare(b, 'vi'));

    brands.forEach(brand => {
        const option = document.createElement('option');
        option.value = brand;
        option.textContent = brand;
        brandFilter.appendChild(option);
    });

    const filter = () => {
        const keyword = normalize(searchInput.value);
        const brand = brandFilter.value;
        const stock = stockFilter.value;
        let visible = 0;

        cards.forEach(card => {
            const matchesKeyword = !keyword || normalize(card.dataset.search).includes(keyword);
            const matchesBrand = !brand || card.dataset.brand === brand;
            const matchesStock = !stock || card.dataset.stock === stock;
            const show = matchesKeyword && matchesBrand && matchesStock;

            card.classList.toggle('hidden', !show);

            if (show) {
                visible++;
            }
        });

        resultCount.textContent = String(visible);

        if (noResults) {
            noResults.classList.toggle('hidden', visible !== 0);
        }
    };

    searchInput.addEventListener('input', filter);
    brandFilter.addEventListener('change', filter);
    stockFilter.addEventListener('change', filter);
})();
