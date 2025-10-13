<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head-css')
    @stack('styles')
    @stack('script')
</head>

<body>


<!-- START Wrapper -->
<div class="wrapper">

    <div class="toast-container position-fixed bottom-0 end-0 p-3">
        <div id="liveToast" class="toast align-items-center text-bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">Pesan notifikasi</div>
                <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
            </div>
        </div>
    </div>



    @include('partials.main-nav')
    @include('partials.topbar')

    <div class="page-container">

        <!-- Start Container Fluid -->
        <div class="page-content">
            @yield('content')
        </div>
        <!-- End Container Fluid -->
        @include('partials.footer')

    </div>
    <!-- End Page Content -->

</div>
<!-- END Wrapper -->

@include('partials.vendor-scripts')

@stack('scripts')   {{-- ini tempat inject script tambahan --}}


</body>
</html>
