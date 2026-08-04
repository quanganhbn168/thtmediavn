@push('js')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const nameInput = document.getElementById('product_category_name');
        const descriptionInput = document.querySelector('[name="description"]');
        const seoTitleInput = document.querySelector('[name="seo_title"]');
        const seoDescriptionInput = document.querySelector('[name="seo_description"]');

        if (!nameInput || !descriptionInput || !seoTitleInput || !seoDescriptionInput) {
            return;
        }

        let seoTitleLocked = seoTitleInput.value.trim() !== '';
        let seoDescriptionLocked = seoDescriptionInput.value.trim() !== '';

        const syncSeoFields = function () {
            if (!seoTitleLocked) {
                seoTitleInput.value = nameInput.value.trim();
            }

            if (!seoDescriptionLocked) {
                seoDescriptionInput.value = descriptionInput.value.trim();
            }
        };

        nameInput.addEventListener('input', syncSeoFields);
        descriptionInput.addEventListener('input', syncSeoFields);
        seoTitleInput.addEventListener('input', function () {
            seoTitleLocked = seoTitleInput.value.trim() !== '';
            syncSeoFields();
        });
        seoDescriptionInput.addEventListener('input', function () {
            seoDescriptionLocked = seoDescriptionInput.value.trim() !== '';
            syncSeoFields();
        });

        syncSeoFields();
    });
</script>
@endpush
