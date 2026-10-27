<header class="admin-navbar">

    <div class="navbar-left">

        <button
            type="button"
            class="menu-toggle"
            id="menuToggle"
        >

            <i class="fa-solid fa-bars"></i>

        </button>


        <div>

            <h1>
                @yield('page-title', 'Dashboard')
            </h1>

            <p>
                Manage your ShadowChat application
            </p>

        </div>

    </div>


    <div class="navbar-user">

        <div class="navbar-avatar">

            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

        </div>


        <div class="navbar-user-info">

            <strong>
                {{ auth()->user()->name }}
            </strong>

            <span>
                Administrator
            </span>

        </div>

    </div>

</header>