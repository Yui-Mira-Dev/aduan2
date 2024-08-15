$(document).ready(function() {
    $('#search').on('input', function() {
        let searchTerm = $(this).val();

        if (searchTerm.length < 3) {
            $('#suggestions').empty();
            $('#loading').hide();
            return;
        }

        $('#loading').show();

        $.ajax({
            url: 'SearchSuggestions',
            method: 'GET',
            data: {
                term: searchTerm
            },
            success: function(data) {
                $('#suggestions').empty().html(data);
                $('#loading').hide();
            },
            error: function(jqXHR, textStatus, errorThrown) {
                console.error('AJAX request failed:', textStatus, errorThrown);
                $('#loading').hide();
            }
        });
    });

    $(document).on('click', '.suggestion-item', function() {
        let selectedValue = $(this).text().split(' - ')[0]; 
        $('#search').val(selectedValue);
        $('#suggestions').empty(); 
    });
});