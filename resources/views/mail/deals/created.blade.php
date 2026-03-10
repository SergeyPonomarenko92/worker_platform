@component('mail::message')
# Нова угода створена

Вітаємо{{ $client?->name ? ', ' . $client->name : '' }}!

Провайдер **{{ $businessProfile->name ?? '—' }}** створив(-ла) угоду.

@component('mail::panel')
**Статус:** {{ $deal->status }}  
@if($offer)
**Пропозиція:** {{ $offer->title }}  
@endif
@if(!is_null($deal->agreed_price))
**Узгоджена ціна:** {{ $deal->agreed_price }} {{ $deal->currency }}
@endif
@endcomponent

> Поки що це MVP‑повідомлення. Далі ми додамо зручний перегляд угоди та можливість залишити відгук після завершення.

Дякуємо, що користуєтесь {{ config('app.name') }}.
@endcomponent
