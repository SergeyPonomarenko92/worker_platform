<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class BusinessProfileController extends Controller
{
    public function create(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $existing = $user->businessProfiles()->first();
        if ($existing) {
            return redirect()->route('dashboard.business-profile.edit');
        }

        return Inertia::render('BusinessProfile/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        // For MVP: one business profile per user.
        if ($user->businessProfiles()->exists()) {
            return redirect()->route('dashboard.business-profile.edit');
        }

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
        $data['slug'] = Str::slug($data['name']);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $profile = BusinessProfile::create($data);

        return redirect()->route('dashboard.business-profile.edit')->with('success', 'Business profile created.');
    }

    public function edit(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        $profile = $user->businessProfiles()->first();
        if (! $profile) {
            return redirect()->route('dashboard.business-profile.create');
        }

        $this->authorize('update', $profile);

        return Inertia::render('BusinessProfile/Edit', [
            'profile' => $profile,
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = $request->user();

        $profile = $user->businessProfiles()->firstOrFail();
        $this->authorize('update', $profile);

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

        $data['slug'] = Str::slug($data['name']);
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $profile->update($data);

        return back()->with('success', 'Business profile updated.');
    }
}
