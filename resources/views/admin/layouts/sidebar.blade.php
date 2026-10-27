<aside class="admin-sidebar" id="adminSidebar">

    <div class="sidebar-header">

        <a href="{{ route('admin.dashboard') }}" class="admin-brand">

            <span class="brand-icon">
                <i class="fa-solid fa-user-secret"></i>
            </span>

            <span>ShadowChat</span>

        </a>

        <button
            type="button"
            class="sidebar-close"
            id="sidebarClose">

            <i class="fa-solid fa-xmark"></i>

        </button>

    </div>


    <div class="sidebar-menu">

        <div class="menu-title">
            MAIN MENU
        </div>


        <a
            href="{{ route('admin.dashboard') }}"
            class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
        >

            <i class="fa-solid fa-chart-pie"></i>

            <span>Dashboard</span>

        </a>


        <a
            href="{{ route('admin.rooms') }}"
            class="sidebar-link {{ request()->routeIs('admin.rooms') ? 'active' : '' }}"
        >

            <i class="fa-solid fa-door-open"></i>

            <span>Rooms</span>

        </a>


        <a
            href="{{ route('admin.participants') }}"
            class="sidebar-link {{ request()->routeIs('admin.participants') ? 'active' : '' }}"
        >

            <i class="fa-solid fa-users"></i>

            <span>Participants</span>

        </a>


        <a
            href="{{ route('admin.reports') }}"
            class="sidebar-link {{ request()->routeIs('admin.reports') ? 'active' : '' }}"
        >

            <i class="fa-solid fa-flag"></i>

            <span>Reports</span>

        </a>


        <div class="menu-title system-title">
            SYSTEM
        </div>


        <a
            href="{{ route('admin.settings') }}"
            class="sidebar-link {{ request()->routeIs('admin.settings') ? 'active' : '' }}"
        >

            <i class="fa-solid fa-gear"></i>

            <span>Settings</span>

        </a>

    </div>


    <div class="sidebar-bottom">

        <div class="admin-profile">

            <div class="profile-avatar">

                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}

            </div>


            <div class="profile-info">

                <strong>
                    {{ auth()->user()->name }}
                </strong>

                <small>
                    Administrator
                </small>

            </div>

        </div>


        <form action="{{ route('admin.logout') }}" method="POST">

            @csrf

            <button
                type="submit"
                class="logout-button"
            >

                <i class="fa-solid fa-right-from-bracket"></i>

                <span>Logout</span>

            </button>

        </form>

    </div>

</aside>