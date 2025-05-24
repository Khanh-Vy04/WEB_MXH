// Dropdown actions
$(document).on('click', '.action-btn', function(e) {
    e.stopPropagation();
    $(this).next('.dropdown-menu').toggleClass('show');
});

$(document).on('click', function(e) {
    if (!$(e.target).closest('.action-dropdown').length) {
        $('.dropdown-menu').removeClass('show');
    }
});

// Select all
$('.select-all').on('change', function() {
    $('input[name="selected_products[]"]').prop('checked', this.checked);
});



