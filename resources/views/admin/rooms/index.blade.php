@extends('admin.layouts.app')

@section('title', 'Rooms | ShadowChat Admin')

@section('page-title', 'Rooms')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin/rooms.css') }}">
@endpush

@section('content')

<div class="rooms-page">

    <div class="rooms-header">

        <div>
            <h2>Rooms</h2>

            <p>
                Monitor and manage ShadowChat temporary chat rooms.
            </p>
        </div>

        <div class="rooms-count">
            <i class="fa-solid fa-door-open"></i>
            <span>{{ $rooms->count() }} Rooms</span>
        </div>

    </div>


    <div class="rooms-card">

        <div class="rooms-card-header">

            <div>
                <h3>All Rooms</h3>

                <p>
                    List of rooms created in ShadowChat
                </p>
            </div>

        </div>


        <div class="table-responsive">

            <table class="rooms-table">

                <thead>

                    <tr>
                        <th>Room Code</th>
                        <th>Status</th>
                        <th>Participants</th>
                        <th>Created</th>
                        <th>Expires</th>
                        <th>Action</th>
                    </tr>

                </thead>


                <tbody>

                    @forelse($rooms as $room)

                        <tr>

                            <td>

                                <div class="room-code">

                                    <i class="fa-solid fa-hashtag"></i>

                                    <strong>
                                        {{ $room->room_code }}
                                    </strong>

                                </div>

                            </td>


                            <td>

                                @if($room->status === 'active')

                                    <span class="status-badge active">
                                        <i class="fa-solid fa-circle"></i>
                                        Active
                                    </span>

                                @else

                                    <span class="status-badge ended">
                                        <i class="fa-solid fa-circle"></i>
                                        Ended
                                    </span>

                                @endif

                            </td>


                            <td>

                                <div class="participant-count">

                                    <i class="fa-solid fa-users"></i>

                                    {{ $room->participants_count }}

                                </div>

                            </td>


                            <td>

                                <span class="date-text">
                                    {{ $room->created_at->format('d M Y') }}
                                </span>

                                <small>
                                    {{ $room->created_at->format('h:i A') }}
                                </small>

                            </td>


                            <td>

                                @if($room->expires_at)

                                    <span class="date-text">
                                        {{ $room->expires_at->format('d M Y') }}
                                    </span>

                                    <small>
                                        {{ $room->expires_at->format('h:i A') }}
                                    </small>

                                @else

                                    <span class="text-muted">
                                        No expiry
                                    </span>

                                @endif

                            </td>


                            <td>

                                <a
                                    href="#"
                                    class="room-view-btn">

                                    <i class="fa-solid fa-eye"></i>

                                    View

                                </a>

                            </td>

                        </tr>

                    @empty

                        <tr>

                            <td colspan="6">

                                <div class="empty-rooms">

                                    <div class="empty-icon">
                                        <i class="fa-solid fa-door-open"></i>
                                    </div>

                                    <h4>No Rooms Found</h4>

                                    <p>
                                        No ShadowChat rooms have been created yet.
                                    </p>

                                </div>

                            </td>

                        </tr>

                    @endforelse

                </tbody>

            </table>

        </div>

    </div>

</div>

@endsection