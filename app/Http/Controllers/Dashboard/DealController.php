<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Mail\DealCreatedForClientMail;
use App\Models\BusinessProfile;
use App\Models\Deal;
use App\Models\User;
use App\Support\QueryParamNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DealController extends Controller
{
    public function index(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $deals = $businessProfile->deals()
            ->select([
                'id',
                'business_profile_id',
                'client_user_id',
                'offer_id',
                'status',
                'created_at',
            ])
            ->with(['client:id,name,email', 'offer:id,title'])
            ->latest()
            ->get();

        return Inertia::render('Deals/Index', [
            'businessProfile' => $businessProfile,
            'deals' => $deals,
        ]);
    }

    public function create(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $offers = $businessProfile->offers()->orderBy('title')->get(['id', 'title']);

        return Inertia::render('Deals/Create', [
            'businessProfile' => $businessProfile,
            'offers' => $offers,
        ]);
    }

    public function store(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

        // Normalize optional numeric/select fields from HTML forms ("" -> null)
        $request->merge([
            // Be robust to copy/paste and different casing.
            // (exists:users,email) is usually case-sensitive depending on collation.
            'client_email' => mb_strtolower(QueryParamNormalizer::text((string) $request->input('client_email')), 'UTF-8'),
            'offer_id' => $request->input('offer_id') ?: null,
            'agreed_price' => $request->input('agreed_price') === '' ? null : $request->input('agreed_price'),
            'currency' => strtoupper(QueryParamNormalizer::text((string) $request->input('currency'))),
        ]);

        $data = $request->validate([
            'client_email' => ['required', 'email', 'exists:users,email'],
            'offer_id' => ['nullable', 'integer', 'exists:offers,id'],
            'agreed_price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'size:3', Rule::in(['UAH', 'USD', 'EUR'])],
            'status' => ['required', 'in:draft,in_progress,completed,cancelled'],
        ]);

        $clientId = User::query()->where('email', $data['client_email'])->value('id');

        // Ensure chosen offer belongs to business profile.
        if (! empty($data['offer_id'])) {
            $offerOk = $businessProfile->offers()->whereKey($data['offer_id'])->exists();
            if (! $offerOk) {
                return back()->withErrors([
                    'offer_id' => 'Офер має належати вибраному профілю бізнесу.',
                ]);
            }
        }

        $deal = Deal::create([
            'client_user_id' => $clientId,
            'business_profile_id' => $businessProfile->id,
            'offer_id' => $data['offer_id'] ?? null,
            'status' => $data['status'],
            'agreed_price' => $data['agreed_price'] ?? null,
            'currency' => $data['currency'],
            'completed_at' => $data['status'] === 'completed' ? now() : null,
        ]);

        // MVP: notify client via email that a provider created a deal.
        // Queue it to avoid slowing down the request (sync queue will still send immediately).
        Mail::to($data['client_email'])->queue(new DealCreatedForClientMail($deal));

        return redirect()
            ->route('dashboard.deals.show', [$businessProfile, $deal])
            ->with('success', 'Угоду створено.');
    }

    public function show(Request $request, BusinessProfile $businessProfile, Deal $deal): Response
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('view', $deal);

        return Inertia::render('Deals/Show', [
            'businessProfile' => $businessProfile,
            'deal' => $deal->load(['client:id,name,email', 'offer:id,title']),
        ]);
    }

    public function markInProgress(Request $request, BusinessProfile $businessProfile, Deal $deal): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $deal);

        if (in_array($deal->status, ['completed', 'cancelled'], true)) {
            return back()->with('error', 'Неможливо змінити статус: угода вже завершена або скасована.');
        }

        $deal->update([
            'status' => 'in_progress',
            'completed_at' => null,
        ]);

        return back()->with('success', 'Статус угоди: в процесі.');
    }

    public function markCompleted(Request $request, BusinessProfile $businessProfile, Deal $deal): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $deal);

        if ($deal->status === 'cancelled') {
            return back()->with('error', 'Неможливо завершити скасовану угоду.');
        }

        if ($deal->status === 'completed') {
            return back()->with('success', 'Угоду вже завершено.');
        }

        $deal->update([
            'status' => 'completed',
            'completed_at' => now(),
        ]);

        return back()->with('success', 'Угоду завершено.');
    }

    public function markCancelled(Request $request, BusinessProfile $businessProfile, Deal $deal): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $deal);

        if ($deal->status === 'completed') {
            return back()->with('error', 'Неможливо скасувати завершену угоду.');
        }

        if ($deal->status === 'cancelled') {
            return back()->with('success', 'Угоду вже скасовано.');
        }

        $deal->update([
            'status' => 'cancelled',
        ]);

        return back()->with('success', 'Угоду скасовано.');
    }
}
