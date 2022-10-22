@php
  $user = Auth::user();
  $birthdate = $user->birthdate->setTimezone($user->timezone)->setTime(0, 0);
@endphp

<x-base-layout>
  <h2 class="text-2xl">Dashboard</h2>
  <p class="mt-2">
    Welcome home {{ $user->full_name }}, <br><br>
    You birthday is {{ $birthdate }} <br><br>
    You are <b>{{ $birthdate->age }} years old. </b><br><br>
    @if ($user->nextBirthday()->setTimezone($user->timezone)->isToday())
      Happy Birthday 🎉 <br><br>
    @else
      We will send you Birthday Greeting in
      {{ str_replace(
        'after',
        'later',
        $user->nextBirthdayShouldBeGreetedAt()->diffForHumans(
          today()->setTimezone($user->timezone)
        )),
      }}.
    @endif
  </p>
</x-base-layout>