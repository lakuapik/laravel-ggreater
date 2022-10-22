<x-base-layout>
  <h2 class="text-2xl">
    Register
    <small class="text-base">an account to use the app</small>
  </h2>
  <form class="mt-8" action="{{ route('register') }}" method="POST">
    @csrf
    <x-input-field label="First Name" name="first_name" placeholder="First name" />
    <x-input-field label="Last Name" name="last_name" placeholder="Last name" />
    <x-input-field label="Email" name="email" type="email" placeholder="Email" />
    <x-input-field label="Password" name="password" type="password" placeholder="Password" />
    <x-input-field label="Password Confirmation" name="password_confirmation" type="password" placeholder="Re-type password" />
    <x-input-field label="Birthday" name="birthdate" type="date" placeholder="Your birthday" />
    <x-input-field label="Location" name="location" placeholder="Current city" />
    <x-select-field label="Timezone" name="timezone">
      @foreach (get_all_timezones() as $timezone)
        <option value="{{ $timezone }}" @selected(old('timezone') == $timezone)>
          {{ $timezone }}
        </option>
      @endforeach
    </x-select-field>
    <div class="mt-4">
      <div class="flex items-center justify-between">
        <x-primary-button name="Register" type="submit" />
        <x-secondary-link :href="route('login')" label="Already have an account?" />
      </div>
    </div>
  </form>
</x-base-layout>
