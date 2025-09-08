<link href="{{ asset('assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
<link href="{{ asset('assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />

<script src="{{ asset('assets/js/config.min.js') }}"></script>

<style>
    .gridjs-sort:before,
    .gridjs-sort:after {
        display: none !important;
    }

    /* Tambahin icon Font Awesome */
    .gridjs-sort-neutral::after {
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        content: "\f0dc"; /* fa-sort */
        margin-left: 5px;
    }

    .gridjs-sort-asc::after {
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        content: "\f0de"; /* fa-sort-up */
        margin-left: 5px;
    }

    .gridjs-sort-desc::after {
        font-family: "Font Awesome 6 Free";
        font-weight: 900;
        content: "\f0dd"; /* fa-sort-down */
        margin-left: 5px;
    }

</style>
