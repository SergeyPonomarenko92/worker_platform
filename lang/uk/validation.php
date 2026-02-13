<?php

return [
    'accepted' => 'Поле :attribute має бути прийняте.',
    'active_url' => 'Поле :attribute має бути дійсним URL.',
    'after' => 'Поле :attribute має містити дату після :date.',
    'after_or_equal' => 'Поле :attribute має містити дату після або дорівнювати :date.',
    'alpha' => 'Поле :attribute має містити лише літери.',
    'alpha_dash' => 'Поле :attribute має містити лише літери, цифри, дефіси та підкреслення.',
    'alpha_num' => 'Поле :attribute має містити лише літери та цифри.',
    'array' => 'Поле :attribute має бути масивом.',

    'before' => 'Поле :attribute має містити дату до :date.',
    'before_or_equal' => 'Поле :attribute має містити дату до або дорівнювати :date.',
    'between' => [
        'array' => 'Поле :attribute має містити від :min до :max елементів.',
        'file' => 'Поле :attribute має бути між :min та :max кілобайтами.',
        'numeric' => 'Поле :attribute має бути між :min та :max.',
        'string' => 'Поле :attribute має містити від :min до :max символів.',
    ],
    'boolean' => 'Поле :attribute має бути true або false.',

    'confirmed' => 'Підтвердження поля :attribute не збігається.',

    'date' => 'Поле :attribute не є коректною датою.',
    'date_format' => 'Поле :attribute не відповідає формату :format.',

    'email' => 'Поле :attribute має бути коректною електронною адресою.',
    'exists' => 'Обране значення для :attribute некоректне.',

    'gte' => [
        'array' => 'Поле :attribute має містити :value елементів або більше.',
        'file' => 'Поле :attribute має бути не менше :value кілобайт.',
        'numeric' => 'Поле :attribute має бути не менше :value.',
        'string' => 'Поле :attribute має містити не менше :value символів.',
    ],

    'in' => 'Обране значення для :attribute некоректне.',
    'integer' => 'Поле :attribute має бути цілим числом.',

    'max' => [
        'array' => 'Поле :attribute має містити не більше :max елементів.',
        'file' => 'Поле :attribute має бути не більше :max кілобайт.',
        'numeric' => 'Поле :attribute має бути не більше :max.',
        'string' => 'Поле :attribute має містити не більше :max символів.',
    ],
    'min' => [
        'array' => 'Поле :attribute має містити щонайменше :min елементів.',
        'file' => 'Поле :attribute має бути щонайменше :min кілобайт.',
        'numeric' => 'Поле :attribute має бути щонайменше :min.',
        'string' => 'Поле :attribute має містити щонайменше :min символів.',
    ],

    'numeric' => 'Поле :attribute має бути числом.',

    'required' => 'Поле :attribute є обов\'язковим.',

    'size' => [
        'array' => 'Поле :attribute має містити :size елементів.',
        'file' => 'Поле :attribute має бути :size кілобайт.',
        'numeric' => 'Поле :attribute має бути :size.',
        'string' => 'Поле :attribute має містити :size символів.',
    ],

    'string' => 'Поле :attribute має бути рядком.',

    'url' => 'Поле :attribute має бути коректним URL.',

    // Custom messages for specific attributes/rules
    'custom' => [
        'price_to' => [
            'gte' => 'Поле "Ціна до" має бути більшим або дорівнювати полю "Ціна від".',
        ],
        'expires_at' => [
            'after' => 'Дата завершення має бути в майбутньому.',
        ],
    ],

    // Attribute names (for nicer validation errors)
    'attributes' => [
        'name' => 'назва',
        'about' => 'про нас',
        'country_code' => 'код країни',
        'city' => 'місто',
        'address' => 'адреса',
        'phone' => 'телефон',
        'website' => 'сайт',
        'is_active' => 'активність',

        'category_id' => 'категорія',
        'type' => 'тип',
        'title' => 'назва',
        'description' => 'опис',
        'price_from' => 'ціна від',
        'price_to' => 'ціна до',
        'currency' => 'валюта',

        'body' => 'текст',
        'published_at' => 'дата публікації',

        'media_path' => 'шлях до медіа',
        'caption' => 'підпис',
        'expires_at' => 'дата завершення',

        'client_email' => 'email клієнта',
        'offer_id' => 'офер',
        'agreed_price' => 'узгоджена ціна',
        'status' => 'статус',

        'rating' => 'оцінка',
    ],
];
