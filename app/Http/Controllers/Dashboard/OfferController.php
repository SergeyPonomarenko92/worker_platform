<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\BusinessProfile;
use App\Models\Offer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class OfferController extends Controller
{
    public function index(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $offers = $businessProfile->offers()->with('category')->latest()->get();

        return Inertia::render('Offers/Index', [
            'businessProfile' => $businessProfile,
            'offers' => $offers,
        ]);
    }

    public function create(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Offers/Create', [
            'businessProfile' => $businessProfile,
            'categories' => $categories,
        ]);
    }

    public function store(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

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

        $data['business_profile_id'] = $businessProfile->id;
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        Offer::create($data);

        return redirect()->route('dashboard.offers.index', $businessProfile)->with('success', 'Offer created.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile, Offer $offer): Response
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $offer);

        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Offers/Edit', [
            'businessProfile' => $businessProfile,
            'offer' => $offer->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(Request $request, BusinessProfile $businessProfile, Offer $offer): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
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

    public function destroy(Request $request, BusinessProfile $businessProfile, Offer $offer): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('delete', $offer);

        $offer->delete();

        return redirect()->route('dashboard.offers.index', $businessProfile)->with('success', 'Offer deleted.');
    }
}
