const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content || '';

const initStandardIndexColumns = (root) => {
    const resource = root.dataset.indexResource;
    const table = root.querySelector('table');
    const headerRow = table?.querySelector('thead tr');
    const body = table?.querySelector('tbody');
    if (!resource || !headerRow || !body) return;

    const rows = [...body.querySelectorAll(':scope > tr[data-record-id]')];
    if (rows.length === 0) return;

    const insertFirst = (row, element) => row.insertBefore(element, row.firstChild);
    const insertAfterSelection = (row, element) => {
        const selection = row.querySelector(':scope > [data-select-column]');
        row.insertBefore(element, selection?.nextSibling || row.firstChild);
    };

    const bulkFormId = root.dataset.bulkFormId;
    if (bulkFormId && !headerRow.querySelector('[data-check-all]')) {
        const header = document.createElement('th');
        header.className = 'text-center';
        header.style.width = '48px';
        header.dataset.selectColumn = 'true';
        header.innerHTML = '<input type="checkbox" class="form-check-input" data-check-all aria-label="Chọn tất cả">';
        insertFirst(headerRow, header);

        rows.forEach((row) => {
            const cell = document.createElement('td');
            cell.className = 'text-center';
            cell.dataset.selectColumn = 'true';
            cell.innerHTML = `<input form="${bulkFormId}" type="checkbox" name="ids[]" value="${row.dataset.recordId}" class="form-check-input" data-check-item aria-label="Chọn bản ghi">`;
            insertFirst(row, cell);
        });
    }

    const canReorder = root.dataset.reorderable === '1' && root.dataset.reorderEnabled === '1';
    if (!canReorder) return;

    body.dataset.sortableBody = 'true';
    body.dataset.resource = resource;
    body.dataset.reorderUrl = root.dataset.reorderUrl;
    body.dataset.orderStart = root.dataset.orderStart || '1';

    if (!headerRow.querySelector('[data-order-column]')) {
        const header = document.createElement('th');
        header.className = 'text-center';
        header.dataset.orderColumn = 'true';
        header.innerHTML = '<i class="bi bi-arrow-down-up"></i>';
        insertAfterSelection(headerRow, header);

        rows.forEach((row) => {
            const cell = document.createElement('td');
            cell.className = 'text-center';
            cell.dataset.orderColumn = 'true';
            cell.innerHTML = '<button type="button" class="admin-drag-handle" data-drag-handle aria-label="Kéo để sắp xếp"><i class="bi bi-grip-vertical fs-5"></i></button>';
            insertAfterSelection(row, cell);
        });
    }
};

const initBulkSelection = (root) => {
    const form = root.querySelector('[data-admin-bulk-form]');
    const toolbar = root.querySelector('[data-bulk-toolbar]');
    const checkAll = root.querySelector('[data-check-all]');
    const items = [...root.querySelectorAll('[data-check-item]')];
    const count = root.querySelector('[data-selected-count]');
    const action = root.querySelector('[data-bulk-action]');
    const apply = root.querySelector('[data-bulk-apply]');
    if (!form || !toolbar || !checkAll) return;

    const syncSelectedInputs = () => {
        form.querySelectorAll('[data-bulk-selection-proxy]').forEach((input) => input.remove());

        items
            .filter((item) => item.checked && item.form !== form)
            .forEach((item) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = item.name || 'ids[]';
                input.value = item.value;
                input.dataset.bulkSelectionProxy = 'true';
                form.append(input);
            });
    };

    const sync = () => {
        const selected = items.filter((item) => item.checked).length;
        checkAll.checked = items.length > 0 && selected === items.length;
        checkAll.indeterminate = selected > 0 && selected < items.length;
        toolbar.hidden = selected === 0;
        if (count) count.textContent = String(selected);
    };

    checkAll.addEventListener('change', () => {
        items.forEach((item) => { item.checked = checkAll.checked; });
        sync();
    });
    items.forEach((item) => item.addEventListener('change', sync));
    apply?.addEventListener('click', async () => {
        const selected = items.filter((item) => item.checked).length;
        if (!action?.value || selected === 0) {
            await Swal.fire('Chưa đủ thông tin', 'Hãy chọn thao tác và ít nhất một bản ghi.', 'warning');
            return;
        }
        const deleting = action.value === 'delete';
        const result = await Swal.fire({
            title: deleting ? `Xóa ${selected} bản ghi đã chọn?` : `Áp dụng cho ${selected} bản ghi?`,
            text: deleting ? (form.dataset.deleteWarning || 'Dữ liệu đã xóa không thể khôi phục.') : 'Thao tác sẽ áp dụng cho các dòng đang chọn trên trang này.',
            icon: 'warning', showCancelButton: true, confirmButtonText: 'Xác nhận', cancelButtonText: 'Hủy',
            confirmButtonColor: deleting ? '#dc3545' : '#0d6efd',
        });
        if (result.isConfirmed) {
            syncSelectedInputs();
            form.requestSubmit();
        }
    });
    sync();
};

const initReordering = (root) => {
    const toggle = root.querySelector('[data-reorder-toggle]');
    const body = root.querySelector('[data-sortable-body]');
    if (!toggle || !body || typeof Sortable === 'undefined') return;
    let enabled = false;
    const sortable = Sortable.create(body, {
        animation: 160, handle: '[data-drag-handle]', draggable: '[data-record-id]',
        ghostClass: 'admin-sortable-ghost', chosenClass: 'admin-sortable-chosen', disabled: true,
        onEnd: async () => {
            const start = Number(body.dataset.orderStart || 1);
            const items = [...body.querySelectorAll('[data-record-id]')].map((row, index) => ({ id: Number(row.dataset.recordId), order: start + index }));
            try {
                const response = await fetch(body.dataset.reorderUrl, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': csrfToken() },
                    body: JSON.stringify({ resource: body.dataset.resource, items }),
                });
                if (!response.ok) throw new Error(`HTTP ${response.status}`);
                const payload = await response.json();
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: payload.message, showConfirmButton: false, timer: 1800 });
            } catch (error) {
                await Swal.fire('Không thể lưu thứ tự', 'Trang sẽ tải lại để khôi phục thứ tự trước đó.', 'error');
                window.location.reload();
            }
        },
    });
    toggle.addEventListener('click', () => {
        enabled = !enabled;
        if (enabled) {
            root.querySelectorAll('[data-check-item], [data-check-all]').forEach((checkbox) => {
                checkbox.checked = false;
                checkbox.indeterminate = false;
                checkbox.dispatchEvent(new Event('change'));
            });
        }
        sortable.option('disabled', !enabled);
        root.classList.toggle('is-reordering', enabled);
        toggle.classList.toggle('btn-primary', enabled);
        toggle.classList.toggle('btn-default', !enabled);
        toggle.setAttribute('aria-pressed', String(enabled));
        toggle.querySelector('[data-reorder-label]').textContent = enabled ? 'Xong' : 'Sắp xếp';
    });
};

const initTomSelect = () => {
    if (typeof TomSelect === 'undefined') return;

    document.querySelectorAll('select[data-tom-select="1"]').forEach((select) => {
        if (select.tomselect) return;
        const allowCreate = select.getAttribute('data-tom-select-create') === '1';
        const treeOrder = select.getAttribute('data-tom-select-sort') === 'tree';

        new TomSelect(select, {
            placeholder: select.getAttribute('data-placeholder') || null,
            allowEmptyOption: true,
            create: allowCreate,
            sortField: treeOrder
                ? [{ field: '$order', direction: 'asc' }]
                : { field: 'text', direction: 'asc' },
        });
    });
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-admin-index]').forEach((root) => {
        initStandardIndexColumns(root);
        initBulkSelection(root);
        initReordering(root);
    });
    initTomSelect();
});

document.addEventListener('submit', async (event) => {
    const form = event.target.closest('[data-admin-delete-form]');
    if (!form || form.dataset.confirmed === 'true') return;
    event.preventDefault();
    const result = await Swal.fire({
        title: form.dataset.deleteTitle || 'Xóa bản ghi này?',
        text: form.dataset.deleteWarning || 'Dữ liệu đã xóa không thể khôi phục.',
        icon: 'warning', showCancelButton: true, confirmButtonText: 'Xóa', cancelButtonText: 'Hủy', confirmButtonColor: '#dc3545',
    });
    if (result.isConfirmed) {
        form.dataset.confirmed = 'true';
        form.requestSubmit();
    }
});
