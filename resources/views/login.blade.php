<x-base-layout>
  <h2 class="text-2xl">
    Login
    <small class="text-base">to start your session</small>
  </h2>
  <form class="mt-8" action="{{ route('login') }}" method="POST">
    @csrf
    <x-input-field label="Email" name="email" type="email" placeholder="Email" />
    <x-input-field label="Password" name="password" type="password" placeholder="********" />
    <div class="mt-4">
      <div class="flex items-center justify-between">
        <x-primary-button name="Login" type="submit" />
        <x-secondary-link :href="route('register')" label="Does not have an account?" />
      </div>
    </div>
  </form>
</x-base-layout>
