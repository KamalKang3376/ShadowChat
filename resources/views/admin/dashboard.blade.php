@extends('admin.layouts.app')

@section('title', 'Dashboard | ShadowChat Admin')

@section('page-title', 'Dashboard')

@push('styles')

    <link
        rel="stylesheet"
        href="{{ asset('css/admin/dashboard.css') }}"
    >

@endpush


@section('content')

<div class="container-fluid px-0">

    <div class="dashboard-header mb-4">

        <div>
            <h2>Dashboard Overview</h2>

            <p>
                Monitor and manage your ShadowChat application.
            </p>
        </div>

        <div class="dashboard-date">

            <i class="fa-regular fa-calendar"></i>

            {{ now()->format('d M Y') }}

        </div>

    </div>


    <div class="row g-4">


        <div class="col-12 col-sm-6 col-xl-3">

            <div class="stat-card h-100">

                <div class="stat-icon rooms-icon">

                    <i class="fa-solid fa-door-open"></i>

                </div>

                <div class="stat-info">

                    <span>Total Rooms</span>

                    <h3>
                        {{ $totalRooms }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-12 col-sm-6 col-xl-3">

            <div class="stat-card h-100">

                <div class="stat-icon active-icon">

                    <i class="fa-solid fa-circle-check"></i>

                </div>

                <div class="stat-info">

                    <span>Active Rooms</span>

                    <h3>
                        {{ $activeRooms }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-12 col-sm-6 col-xl-3">

            <div class="stat-card h-100">

                <div class="stat-icon users-icon">

                    <i class="fa-solid fa-users"></i>

                </div>

                <div class="stat-info">

                    <span>Total Participants</span>

                    <h3>
                        {{ $totalParticipants }}
                    </h3>

                </div>

            </div>

        </div>


        <div class="col-12 col-sm-6 col-xl-3">

            <div class="stat-card h-100">

                <div class="stat-icon reports-icon">

                    <i class="fa-solid fa-flag"></i>

                </div>

                <div class="stat-info">

                    <span>Pending Reports</span>

                    <h3>
                        {{ $pendingReports }}
                    </h3>

                </div>

            </div>

        </div>


    </div>


    <div class="row mt-4">

        <div class="col-12">

            <div class="welcome-card">

                <div class="welcome-content">

                    <div>

                        <span class="welcome-label">
                            SHADOWCHAT ADMIN
                        </span>

                        <h3>
                            Welcome to your Admin Panel
                        </h3>

                        <p>
                            Manage temporary chat rooms, participants
                            and reported content from one place.
                        </p>

                    </div>

                    <div class="welcome-icon">

                        <i class="fa-solid fa-shield-halved"></i>

                    </div>

                </div>

            </div>

        </div>

    </div>


</div>

@endsection