<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Production Plan</title>
    <link rel="icon" href={{ asset("assets/image/toho-whitebg.png") }} type="image/png">

    <!-- Google Font: Source Sans Pro -->
    <link href={{ asset("css/font-google.css") }} rel="stylesheet">
    {{--
    <link rel="stylesheet"
        href="https://fonts.googleapis.com/css?family=Source+Sans+Pro:300,400,400i,700&display=fallback"> --}}
    <link href={{ asset("css/font-google2.css") }} rel="stylesheet">
    {{--
    <link href="https://fonts.googleapis.com/css2?family=Kalnia:wght@300;400&family=Quicksand:wght@500;700&display=swap"
        rel="stylesheet"> --}}
    <!-- Font Awesome -->
    <link rel="stylesheet" href={{ asset("template/plugins/fontawesome-free/css/all.min.css") }}>
    <!-- Ionicons -->
    {{--
    <link rel="stylesheet" href="https://code.ionicframework.com/ionicons/2.0.1/css/ionicons.min.css"> --}}
    <link href={{ asset("css/ionicons.min.css") }} rel="stylesheet">

    <!-- Theme style -->
    <link rel="stylesheet" href={{ asset("template/dist/css/adminlte.min.css")}}>

    {{-- Select2 Css --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    @yield('styles')
</head>

<body class="hold-transition sidebar-mini layout-fixed sidebar-collapse">
    <!-- Preloader -->
    {{-- <div class="preloader flex-column justify-content-center align-items-center">
        <img class="animation__shake" src={{ asset("template/dist/img/AdminLTELogo.png") }} alt="AdminLTELogo"
            height="60" width="60">
    </div> --}}

    @include('layouts.sidebar')

    <!-- Content Wrapper. Contains page content -->
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        @include('layouts.header')
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            @yield('content')
        </section>
        <!-- /.content -->
    </div>
    <!-- /.content-wrapper -->

    @include('layouts.footer')

    <!-- Control Sidebar -->
    <aside class="control-sidebar control-sidebar-dark">
        <!-- Control sidebar content goes here -->
    </aside>
    <!-- /.control-sidebar -->
    </div>
    <!-- ./wrapper -->

    <!-- jQuery -->
    <script src={{ asset("template/plugins/jquery/jquery.min.js")}}></script>
    <!-- jQuery UI 1.11.4 -->
    <script src={{ asset("template/plugins/jquery-ui/jquery-ui.min.js")}}></script>
    <!-- Resolve conflict in jQuery UI tooltip with Bootstrap tooltip -->
    <script>
        $.widget.bridge('uibutton', $.ui.button)
    </script>
    <!-- Bootstrap 4 -->
    <script src={{ asset("template/plugins/bootstrap/js/bootstrap.bundle.min.js")}}></script>
    <!-- ChartJS -->
    <script src={{ asset("template/plugins/chart.js/Chart.min.js")}}></script>
    <script src={{ asset('js/datefns.js') }}></script>
    <script src={{ asset("js/chartjs-adapter-date-fns.bundle.min.js") }}>
    </script>
    <!-- AdminLTE App -->
    <script src={{ asset("template/dist/js/adminlte.js") }}></script>
    {{-- Data tables --}}
    <script src="{{ asset('js/datatables.min.js') }}"></script>
    {{-- Axios --}}
    <script src="{{ asset('js/axios.js') }}"></script>
    {{-- Select2 --}}
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>

    {{-- <script src="https://cdnjs.cloudflare.com/ajax/libs/axios/1.6.0/axios.min.js"
        integrity="sha512-WrdC3CE9vf1nBf58JHepuWT4x24uTacky9fuzw2g/3L9JkihgwZ6Cfv+JGTtNyosOhEmttMtEZ6H3qJWfI7gIQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script> --}}
    {{-- <script src={{ asset("js/jquery-3.6.0.min.js") }}></script> --}}
    @yield('scripts')


</body>

</html>