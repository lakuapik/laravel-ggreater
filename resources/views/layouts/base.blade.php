<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ config('app.name') }}</title>
  <script defer type="module" src="https://cdn.skypack.dev/twind/shim"></script>
  <script defer src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js"></script>
</head>

<body class="font-sans antialiased bg-gray-100">
  <div class="min-h-screen max-w-screen-md my-8 mx-auto">
    <header>
      <div class="flex justify-between items-center">
        <span class="text-4xl font-weight-700">
          {{ config('app.name') }}
        </span>
        @auth
          <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button class="inline-block align-baseline font-bold text-sm text-blue-500
              hover:text-blue-800 cursor-pointer" type="submit">
              Logout
            </button>
          </form>
        @endauth
      </div>
      <hr class="mt-4">
    </header>
    <main class="mt-8">
      {{ $slot }}
    </main>
  </div>
</body>

</html>
