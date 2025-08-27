<!DOCTYPE html>
<html lang="en">

<head>
    @include('partials.head-css')
</head>

<body>

<!-- START Wrapper -->
<div class="wrapper">

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


</body>
</html>
