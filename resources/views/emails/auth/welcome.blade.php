<x-mail::message>
# Welcome, {{ $name }}  

We’re glad to have you on board. Your account is now active and ready for use.

<x-mail::panel>
You can now explore products, manage your cart, track orders, and update your profile seamlessly.
</x-mail::panel>

<x-mail::button :url="config('app.url')">
Start Browsing
</x-mail::button>

If you didn’t register this account, please contact us.

Regards,  
{{ config('app.name') }}
</x-mail::message>
