$(document).ready(function () {
    // Apply Select2 to all <select> elements unless they have the class `no-select2`
    try {
        $('select').each(function () {
            if (!$(this).hasClass('no-select2')) {
                // initialize with full width by default
                if (typeof $(this).select2 === 'function') {
                    $(this).select2({ width: '100%' });
                }
            }
        });
    } catch (e) {
        // If Select2 isn't present, fail silently (so pages remain usable)
        console.warn('Select2 not initialized:', e);
    }
});
