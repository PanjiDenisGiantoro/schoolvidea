<script src="{{ asset('assets/js/vendor.js') }}"></script>
<script src="{{ asset('assets/js/apps.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        // Replace all sort buttons after render
        document.querySelectorAll(".gridjs-sort").forEach(btn => {
            btn.innerHTML = '<i class="fa-solid fa-sort"></i>';
        });

        // Event listener untuk toggle icon saat klik
        document.addEventListener("click", function(e) {
            if (e.target.closest(".gridjs-sort")) {
                document.querySelectorAll(".gridjs-sort").forEach(b => {
                    b.innerHTML = '<i class="fa-solid fa-sort"></i>';
                });

                if (e.target.closest(".gridjs-sort-asc")) {
                    e.target.closest(".gridjs-sort").innerHTML = '<i class="fa-solid fa-sort-up"></i>';
                } else if (e.target.closest(".gridjs-sort-desc")) {
                    e.target.closest(".gridjs-sort").innerHTML = '<i class="fa-solid fa-sort-down"></i>';
                }
            }
        });
    });
</script>
