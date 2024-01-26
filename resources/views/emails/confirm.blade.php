@component('mail::message')
# Hello {{$user->name}}

You changed your email, so you need to verify this new address. Please click the button below: 

@component('mail::button', ['url' => route('verify', $user->verification_token)])
Verify Email
@endcomponent

Thanks,<br>
{{ config('app.name') }}
@endcomponent
