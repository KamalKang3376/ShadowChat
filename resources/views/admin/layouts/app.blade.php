<!DOCTYPE html>
<html lang="en">
<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>@yield('title', 'ShadowChat Admin')</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/sidebar.css') }}"
    >

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/navbar.css') }}"
    >

    @stack('styles')

</head>

<body>

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    @include('admin.layouts.sidebar')

    <div class="admin-main">

        @include('admin.layouts.navbar')

        <main class="admin-content">

            @yield('content')

        </main>

    </div>


    <script
        src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
    </script>


    <script>

        const sidebar = document.getElementById('adminSidebar');

        const overlay = document.getElementById('sidebarOverlay');

        const menuToggle = document.getElementById('menuToggle');

        const sidebarClose = document.getElementById('sidebarClose');


        menuToggle?.addEventListener('click', function () {

            sidebar.classList.add('show');

            overlay.classList.add('show');

        });


        sidebarClose?.addEventListener('click', function () {

            sidebar.classList.remove('show');

            overlay.classList.remove('show');

        });


        overlay?.addEventListener('click', function () {

            sidebar.classList.remove('show');

            overlay.classList.remove('show');

        });

    </script>


    @stack('scripts')

</body>

</html>