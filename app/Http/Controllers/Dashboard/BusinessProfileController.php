<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Http\Requests\Dashboard\StoreBusinessProfileRequest;
use App\Http\Requests\Dashboard\UpdateBusinessProfileRequest;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    private function uniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name);
        if ($base === '') {
            $base = 'profile';
        }

        $slug = $base;

        $i = 2;
        while (
            BusinessProfile::query()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base.'-'.$i;
            $i++;
        }

        return $slug;
    }

    public function index(Request $request): Response
    {
        $profiles = $request->user()
            ->businessProfiles()
            ->latest()
            ->get(['id', 'name', 'slug', 'is_active']);

        return Inertia::render('BusinessProfiles/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BusinessProfile/Create');
    }

    public function store(StoreBusinessProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->normalized();

        $data['user_id'] = $user->id;
        $data['slug'] = $this->uniqueSlug($data['name']);

        $profile = BusinessProfile::create($data);

        return redirect()
            ->route('dashboard.business-profiles.edit', $profile)
            ->with('success', 'Профіль бізнесу створено.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        return Inertia::render('BusinessProfile/Edit', [
            'profile' => $businessProfile,
        ]);
    }

    public function update(UpdateBusinessProfileRequest $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $data = $request->normalized();

        $data['slug'] = $this->uniqueSlug($data['name'], $businessProfile->id);

        $businessProfile->update($data);

        return back()->with('success', 'Профіль бізнесу оновлено.');
    }
}
