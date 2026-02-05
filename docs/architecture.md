# Архитектура (черновик)

## Технологии
- Backend: **Laravel** (монолит на старте)
- Frontend: **Vue 3** через **Inertia** (Breeze scaffold)
- Assets: Vite + Tailwind
- DB (production): Postgres (рекомендовано). Локально можно MySQL.

## Почему монолит + Inertia
- Быстрое MVP без отдельного SPA/API деплоя.
- Можно эволюционировать в API-first позже (Sanctum уже подключён через Breeze).

## Модули домена (план)
- Accounts: User
- Provider: BusinessProfile
- Catalog: Category, Offer (service|product)
- Content: PortfolioPost, Story
- Commerce: Deal
- Reputation: Review

## Правила
- Отзыв только по завершённой сделке.
- Мультиязычность: по умолчанию **uk**.

## Структура
- app/Domain/* (когда появится логика)
- app/Models/* (Eloquent)
- database/migrations/*
- resources/js/Pages/* (Inertia pages)
