# Laravel GGreater

## Getting Started

### Requirements

* PHP ^8.1
* Database: SQlite/MySQL
* (optional) PHP XDebug extension for code coverage

### Setup

1. Clone this repository
2. Install php dependencies with `composer install`
3. Copy file `.env.example` to `.env` then suits your environment
4. Migrate and seed the database with `composer fresh`
5. Run the app with `php artisan serve`  
   Also run `php artisan queue:work` to start the worker
   And run `php artisan schedule:work` to start the scheduler
5. Open browser and visit `http://localhost:8000`  
   
## Lint (Code Format)

Run `composer lint` to lint the codebase with laravel-pint.

## Static Analyse (QA)

Run `composer analyse` to analyse the codebase with phpstan.

## Tests (QC)

Run `composer test` to run all pre-checks (lint, analyse, test).

### Coverage

* Run `composer coverage` to see code coverage
* Run `composer coverage-html` to generate html coverage report
* Run `composer coverage-serve` to view the html coverage report

Last time i run, it got *98.77%* coverage

```
❯ composer coverage
> @putenv XDEBUG_MODE=coverage
> pest --coverage

   PASS  Tests\Unit\EmailServiceTest
  ✓ it success getting hello world response
  ✓ it success sending email given valid data
  ✓ it fails sending email given incomplete request data
  ✓ it fails sending email given 10% canche server error
  ✓ it fails sending email given 10% canche server hang

   PASS  Tests\Unit\SendGreetingToEmailServiceJobTest
  ✓ it should send to email service given valid condition
  ✓ it should not send to email service given greeting already sent
  ✓ it should not send to email service given birthday was changed
  ✓ it should retry sending to email service given the server returns an error
  ✓ it should create new greeting for next year on emailServiceJob was sent

   PASS  Tests\Unit\SendGreetingsCommandTest
  ✓ it should queue sendGreetingToEmailServiceJob given birthday is today
  ✓ it should not queue sendGreetingToEmailServiceJob given birthday is yesterday or tomorrow

   PASS  Tests\Feature\AuthenticationWebTest
  ✓ it success showing register form
  ✓ it success register in a user given valid data
  ✓ it fails registering a user given invalid data
  ✓ it success showing login form
  ✓ it success logging in a user given valid credential
  ✓ it fails loggin in a user given invaid credential
  ✓ it success logging out a user given valid session
  ✓ it success logging out a user given invalid session
  ✓ it success redirecting to dashboard if there is a session
  ✓ it fails accessing dashboard without a session

   PASS  Tests\Feature\UserApiTest
  ✓ it success creating a user given valid data
  ✓ it fails creating a user given invaid data
  ✓ it success getting a user given valid id
  ✓ it fails getting a user given invalid id
  ✓ it success updating a user given valid data
  ✓ it fails updating a user given invalid data
  ✓ it success deleting a user given valid id
  ✓ it fails deleting a user given invalid id
  ✓ it success creating new greeting given user update their birthday or timezone

   PASS  Tests\Feature\VersionApiTest
  ✓ it success showing app version

  Tests:  32 passed
  Time:   2.06s

  Cov:    98.19%

  Console/Commands/SendGreetingsCommand  ....................... 100.0 %
  Console/Kernel 14 ............................................. 50.0 %
  Enums/GreetingType 17 ......................................... 83.3 %
  Events/OnEmailServiceJobWasSentGreeting  ..................... 100.0 %
  Exceptions/Handler  .......................................... 100.0 %
  Http/Controllers/Api/UserApiController  ...................... 100.0 %
  Http/Controllers/Controller  ................................. 100.0 %
  Http/Controllers/Web/AuthenticationWebController  ............ 100.0 %
  Http/Integrations/EmailService/Connector  .................... 100.0 %
  Http/Integrations/EmailService/Requests/HelloWorld  .......... 100.0 %
  Http/Integrations/EmailService/Requests/SendEmail  ........... 100.0 %
  Http/Kernel  ................................................. 100.0 %
  Http/Middleware/Authenticate  ................................ 100.0 %
  Http/Middleware/EncryptCookies  .............................. 100.0 %
  Http/Middleware/PreventRequestsDuringMaintenance  ............ 100.0 %
  Http/Middleware/RedirectIfAuthenticated  ..................... 100.0 %
  Http/Middleware/TrimStrings  ................................. 100.0 %
  Http/Middleware/TrustHosts ..................................... 0.0 %
  Http/Middleware/TrustProxies  ................................ 100.0 %
  Http/Middleware/ValidateSignature  ........................... 100.0 %
  Http/Middleware/VerifyCsrfToken  ............................. 100.0 %
  Http/Resources/UserResource  ................................. 100.0 %
  Jobs/SendGreetingToEmailServiceJob  .......................... 100.0 %
  Listeners/CreateNewGreetingForNextBirthday  .................. 100.0 %
  Models/Greeting  ............................................. 100.0 %
  Models/User  ................................................. 100.0 %
  Providers/AppServiceProvider  ................................ 100.0 %
  Providers/AuthServiceProvider  ............................... 100.0 %
  Providers/EventServiceProvider  .............................. 100.0 %
  Providers/RouteServiceProvider  .............................. 100.0 %
  View/Components/BaseLayout  .................................. 100.0 %
  helpers  ..................................................... 100.0 %
```