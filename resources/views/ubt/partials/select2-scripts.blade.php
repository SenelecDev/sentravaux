@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/i18n/fr.js"></script>
<script>
    $(function () {
        function initSelect2(scope) {
            $(scope).find('.select2-users').not('.select2-hidden-accessible').select2({
                language: 'fr',
                placeholder: '-- Sélectionner --',
                allowClear: true,
                width: '100%'
            });
        }

        initSelect2(document);

        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (node.nodeType === 1) {
                        initSelect2(node);
                    }
                });
            });
        });

        observer.observe(document.body, { childList: true, subtree: true });
    });
</script>
@endpush

