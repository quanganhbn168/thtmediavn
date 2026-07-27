@push('js')
<script>
document.addEventListener('DOMContentLoaded', () => {
    const category = document.getElementById('product_category_id');
    const groups = [...document.querySelectorAll('[data-product-filter-attribute]')];
    const scopes = @json($categoryFilterScopes);
    if (!category || !groups.length) return;

    const update = () => {
        const allowedCategoryIds = (scopes[category.value] || []).map(Number);
        groups.forEach(group => {
            let categoryIds = [];
            try { categoryIds = JSON.parse(group.dataset.categoryIds || '[]').map(Number); } catch (_) {}
            const visible = categoryIds.length === 0 || categoryIds.some(id => allowedCategoryIds.includes(id));
            group.classList.toggle('d-none', !visible);
            group.querySelectorAll('input[type="checkbox"]').forEach(input => {
                input.disabled = !visible;
                if (!visible) input.checked = false;
            });
        });
    };

    category.addEventListener('change', update);
    update();
});
</script>
@endpush
