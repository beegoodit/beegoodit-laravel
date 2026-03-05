@push('scripts')
<script>
(function() {
    if (window.__feedDropZoneInstalled) return;
    window.__feedDropZoneInstalled = true;

    function handleDragOver(e) {
        var zone = e.target.closest('[data-feed-drop-zone]');
        if (!zone) return;
        e.preventDefault();
        e.stopPropagation();
        e.dataTransfer.dropEffect = 'copy';
    }

    function handleDrop(e) {
        var zone = e.target.closest('[data-feed-drop-zone]');
        if (!zone) return;
        e.preventDefault();
        e.stopPropagation();
        var inputId = zone.getAttribute('data-feed-drop-zone');
        var input = document.getElementById(inputId);
        if (input && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            input.files = e.dataTransfer.files;
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    document.addEventListener('dragover', handleDragOver, true);
    document.addEventListener('drop', handleDrop, true);
})();
</script>
@endpush
