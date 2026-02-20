<!doctype html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Login</title>
    <link rel="icon" href="/img/abosaleh-logo.png">

    <link rel="stylesheet" href="/css/login.css" />
</head>

<body>
    <main class="login-layout">
        <!-- Background image -->
        <div class="login-bg" aria-hidden="true"></div>

        <!-- Left overlay content (logo + quote) -->
        <section class="login-side">
            <img class="login-logo" src="/img/abosaleh-logo.png" alt="Abou Saleh Logo" />

            <blockquote class="login-quote">
                <p>“Ninety percent of all millionaires become so through owning real estate.”</p>
                <footer>— Andrew Carnegie</footer>
            </blockquote>
        </section>

        <!-- Login card -->
        <section class="login-card" aria-label="Login form">
            <h1 class="login-title">Welcome back!</h1>

            <form class="login-form" action="{{ route('login.submit') }}" method="post">
                @csrf
                <div class="login-field">
                    <label for="id">ID</label>
                    <input id="id" name="id" type="text" autocomplete="username" />
                    @error('id') <small>{{ $message }}</small> @enderror
                </div>

                <div class="login-field">
                    <label for="password">Password</label>
                    <input id="password" name="password" type="password" autocomplete="current-password" />
                </div>

                <label class="login-remember">
                    <input type="checkbox" name="remember" />
                    <span>Remember Me</span>
                </label>

                <button class="login-btn" type="submit">Login</button>
            </form>
        </section>
    </main>
</body>

</html>