$(document).ready(function() {
    const searchInput = $('#search-input');
    const searchResults = $('#search-results');
    let searchTimeout;
    
    // Handle search input
    searchInput.on('input', function() {
        const term = $(this).val().trim();
        
        // Clear previous timeout
        clearTimeout(searchTimeout);
        
        // If search term is empty, hide results
        if (term.length < 2) {
            searchResults.hide().empty();
            return;
        }
        
        // Set timeout to prevent too many requests
        searchTimeout = setTimeout(function() {
            // Make AJAX request
            $.ajax({
                url: 'ajax/search_results.php',
                type: 'GET',
                data: { term: term },
                dataType: 'json',
                success: function(data) {
                    // Clear previous results
                    searchResults.empty();
                    
                    // If no results found
                    if (data.length === 0) {
                        searchResults.append('<div class="search-no-results">Không tìm thấy kết quả</div>');
                    } else {
                        // Add each result to the dropdown
                        $.each(data, function(index, item) {
                            const resultItem = $('<a>')
                                .addClass('search-result-item')
                                .attr('href', item.url);
                            
                            const resultType = $('<span>')
                                .addClass('search-result-type')
                                .text(item.type);
                            
                            const resultName = $('<span>')
                                .addClass('search-result-name')
                                .text(item.name);
                            
                            resultItem.append(resultType, resultName);
                            searchResults.append(resultItem);
                        });
                    }
                    
                    // Show results
                    searchResults.show();
                },
                error: function() {
                    searchResults.empty().append('<div class="search-no-results">Lỗi khi tìm kiếm</div>').show();
                }
            });
        }, 300);
    });
    
    // Close search results when clicking outside
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.top-search').length) {
            searchResults.hide();
        }
    });
    
    // Handle search icon click
    $('.search').on('click', function() {
        setTimeout(function() {
            searchInput.focus();
        }, 100);
    });
    
    // Handle close search
    $('.close-search').on('click', function() {
        searchResults.hide().empty();
        searchInput.val('');
    });
});