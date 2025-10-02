<script src="{{ asset('assets/js/vendor.js') }}"></script>
<script src="{{ asset('assets/js/apps.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="{{ asset('assets/js/alert2.min.js') }}"></script>
<script src="{{ asset('assets/js/components/form-fileupload.js') }}"></script>
    <script>
        function showToast(message, type = 'primary') {
            const toastEl = document.getElementById('liveToast');
            toastEl.className = `toast align-items-center text-bg-${type} border-0`;
            toastEl.querySelector('.toast-body').innerText = message;

            const toast = new bootstrap.Toast(toastEl);
            toast.show();
        }

        @if(session('success'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast("{{ session('success') }}", 'success');
        });
        @endif

        @if(session('danger'))
        document.addEventListener('DOMContentLoaded', function () {
            showToast("{{ session('danger') }}", 'danger');
        });
        @endif
    </script>


    <script>
        document.querySelectorAll('.delete-link').forEach(function(link) {
            link.addEventListener('click', function(event) {
                event.preventDefault(); // Prevent default link behavior

                // Show confirmation dialog
                Swal.fire({
                    title: 'Are you sure?',
                    text: "This action cannot be undone!",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Yes, delete it!',
                    cancelButtonText: 'Cancel',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        // If confirmed, redirect to the delete URL
                        window.location.href = link.href;
                    }
                });
            });
        });
    </script>
