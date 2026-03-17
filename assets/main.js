document.addEventListener('DOMContentLoaded', function() {
    const searchBtn = document.getElementById('searchBtn');
    const searchInput = document.getElementById('searchInput');
    const searchForm = document.getElementById('searchForm');

    if (searchBtn && searchInput) {
        searchBtn.addEventListener('click', function(e) {
            if (!searchInput.classList.contains('active')) {
           
                e.preventDefault();
                searchInput.classList.add('active');
                searchInput.focus();
            } else {
                
                if (searchInput.value.trim() === "") {
                    e.preventDefault();
                    searchInput.classList.remove('active');
                } else {
                    searchForm.submit();
                }
            }
        });

        document.addEventListener('click', function(e) {
            if (!searchForm.contains(e.target)) {
                searchInput.classList.remove('active');
            }
        });
    }
});