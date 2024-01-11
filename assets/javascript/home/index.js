$(document).ready(function() {
    $('#search').on('input', function() {
        let searchTerm = $(this).val().toLowerCase();

        // Filtrer les items en fonction du terme de recherche
        $('.home__items__item').each(function() {
            let itemName = $(this).data('item-name');

            if (itemName.indexOf(searchTerm) === -1) {
                $(this).hide();
            } else {
                $(this).show();
            }
        });
    });
});