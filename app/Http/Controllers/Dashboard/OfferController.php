<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(Request $request): Response
    {
        $profile = $request->user()->businessProfiles()->first();

        $offers = collect();
        if ($profile) {
            $offers = $profile->offers()->with('category')->latest()->get();
        }

        return Inertia::render('Offers/Index', [
            'hasBusinessProfile' => (bool)$profile,
            'offers' => $offers,
        ]);
    }

    public function create(Request $request): Response|RedirectResponse
    {
        $profile = $request->user()->businessProfiles()->first();
        if (! $profile) {
            return redirect()->route('dashboard.business-profile.create');
        }

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Offers/Create', [
            'categories' => $categories,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $profile = $request->user()->businessProfiles()->firstOrFail();

        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:service,product'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['business_profile_id'] = $profile->id;
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $offer = Offer::create($data);

        $this->authorize('update', $offer);

        return redirect()->route('dashboard.offers.index')->with('success', 'Offer created.');
    }

    public function edit(Request $request, Offer $offer): Response
    {
        $this->authorize('update', $offer);

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Offers/Edit', [
            'offer' => $offer->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorize('update', $offer);

        $data = $request->validate([
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'type' => ['required', 'in:service,product'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'price_from' => ['nullable', 'numeric', 'min:0'],
            'price_to' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['required', 'string', 'max:3'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $offer->update($data);

        return back()->with('success', 'Offer updated.');
    }

    public function destroy(Request $request, Offer $offer): RedirectResponse
    {
        $this->authorize('delete', $offer);

        $offer->delete();

        return redirect()->route('dashboard.offers.index')->with('success', 'Offer deleted.');
    }
}
