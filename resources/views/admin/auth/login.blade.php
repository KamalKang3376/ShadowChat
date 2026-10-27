<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Admin Login | ShadowChat</title>
</head>

<body>

    <h2>ShadowChat Admin Login</h2>

    @if(session('error'))
        <p>{{ session('error') }}</p>
    @endif

    @if($errors->any())
        @foreach($errors->all() as $error)
            <p>{{ $error }}</p>
        @endforeach
    @endif

    <form action="{{ route('admin.login.submit') }}" method="POST">

        @csrf

        <div>
            <label>Email</label>
            <input type="email" name="email" required>
        </div>

        <br>

        <div>
            <label>Password</label>
            <input type="password" name="password" required>
        </div>

        <br>

        <button type="submit">Login</button>

    </form>

</body>
</html>