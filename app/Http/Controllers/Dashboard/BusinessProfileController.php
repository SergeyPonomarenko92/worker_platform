<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Support\ContactFieldNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    private function normalizeWebsite(?string $raw): ?string
    {
        return ContactFieldNormalizer::website($raw);
    }

    private function normalizePhone(?string $raw): ?string
    {
        return ContactFieldNormalizer::phone($raw);
    }

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
            ->get();

        return Inertia::render('BusinessProfiles/Index', [
            'profiles' => $profiles,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BusinessProfile/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['user_id'] = $user->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = (bool)($data['is_active'] ?? true);
        $data['website'] = $this->normalizeWebsite($data['website'] ?? null);
        $data['phone'] = $this->normalizePhone($data['phone'] ?? null);

        $profile = BusinessProfile::create($data);

        return redirect()->route('dashboard.business-profiles.edit', $profile)->with('success', 'Профіль бізнесу створено.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        return Inertia::render('BusinessProfile/Edit', [
            'profile' => $businessProfile,
        ]);
    }

    public function update(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            'country_code' => ['nullable', 'string', 'max:2'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ]);

        $data['slug'] = $this->uniqueSlug($data['name'], $businessProfile->id);
        $data['website'] = $this->normalizeWebsite($data['website'] ?? null);
        $data['phone'] = $this->normalizePhone($data['phone'] ?? null);
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $businessProfile->update($data);

        return back()->with('success', 'Профіль бізнесу оновлено.');
    }
}
