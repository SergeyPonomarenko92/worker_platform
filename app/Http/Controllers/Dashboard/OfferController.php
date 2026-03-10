<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreOfferRequest;
use App\Http\Requests\Dashboard\UpdateOfferRequest;
use App\Models\BusinessProfile;
use App\Models\Category;
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

        $categories = Category::query()
            ->with([
                'parent:id,name,parent_id',
                'parent.parent:id,name,parent_id',
            ])
            ->get(['id', 'name', 'parent_id'])
            ->map(function (Category $category) {
                $names = [];
                $node = $category;
                for ($i = 0; $i < 10 && $node; $i++) {
                    if ($node->name) {
                        array_unshift($names, $node->name);
                    }
                    $node = $node->parent;
                }

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'label' => implode(' → ', array_filter($names)),
                ];
            })
            ->sortBy('label')
            ->values();

        return Inertia::render('Offers/Create', [
            'businessProfile' => $businessProfile,
            'categories' => $categories,
        ]);
    }

    public function store(StoreOfferRequest $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

        $data = $request->normalized();

        $data['business_profile_id'] = $businessProfile->id;
        $data['is_active'] = (bool) ($data['is_active'] ?? true);

        Offer::create($data);

        return redirect()->route('dashboard.offers.index', $businessProfile)->with('success', 'Пропозицію створено.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile, Offer $offer): Response
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $offer);

        $categories = Category::query()
            ->with([
                'parent:id,name,parent_id',
                'parent.parent:id,name,parent_id',
            ])
            ->get(['id', 'name', 'parent_id'])
            ->map(function (Category $category) {
                $names = [];
                $node = $category;
                for ($i = 0; $i < 10 && $node; $i++) {
                    if ($node->name) {
                        array_unshift($names, $node->name);
                    }
                    $node = $node->parent;
                }

                return [
                    'id' => $category->id,
                    'name' => $category->name,
                    'label' => implode(' → ', array_filter($names)),
                ];
            })
            ->sortBy('label')
            ->values();

        return Inertia::render('Offers/Edit', [
            'businessProfile' => $businessProfile,
            'offer' => $offer->load('category'),
            'categories' => $categories,
        ]);
    }

    public function update(UpdateOfferRequest $request, BusinessProfile $businessProfile, Offer $offer): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $offer);

        $data = $request->normalized();

        $offer->update($data);

        return back()->with('success', 'Пропозицію оновлено.');
    }

    public function destroy(Request $request, BusinessProfile $businessProfile, Offer $offer): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('delete', $offer);

        $offer->delete();

        return redirect()->route('dashboard.offers.index', $businessProfile)->with('success', 'Пропозицію видалено.');
    }
}
