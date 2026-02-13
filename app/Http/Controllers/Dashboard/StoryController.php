<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\Story;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class StoryController extends Controller
{
    public function index(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $stories = $businessProfile->stories()
            ->latest('created_at')
            ->get();

        return Inertia::render('Stories/Index', [
            'businessProfile' => $businessProfile,
            'stories' => $stories,
            'now' => now()->toIso8601String(),
        ]);
    }

    public function create(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        return Inertia::render('Stories/Create', [
            'businessProfile' => $businessProfile,
            'defaultExpiresAt' => now()->addDay()->toDateTimeString(),
        ]);
    }

    public function store(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

        $data = $request->validate([
            'media_path' => ['required', 'string', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        $data['business_profile_id'] = $businessProfile->id;

        Story::create($data);

        return redirect()
            ->route('dashboard.stories.index', $businessProfile)
            ->with('success', 'Історію створено.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile, Story $story): Response
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $story);

        return Inertia::render('Stories/Edit', [
            'businessProfile' => $businessProfile,
            'story' => $story,
        ]);
    }

    public function update(Request $request, BusinessProfile $businessProfile, Story $story): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $story);

        $data = $request->validate([
            'media_path' => ['required', 'string', 'max:2048'],
            'caption' => ['nullable', 'string', 'max:1000'],
            'expires_at' => ['required', 'date', 'after:now'],
        ]);

        $story->update($data);

        return back()->with('success', 'Історію оновлено.');
    }

    public function destroy(Request $request, BusinessProfile $businessProfile, Story $story): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('delete', $story);

        $story->delete();

        return redirect()
            ->route('dashboard.stories.index', $businessProfile)
            ->with('success', 'Історію видалено.');
    }
}
