<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Models\PortfolioPost;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PortfolioPostController extends Controller
{
    public function index(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        $posts = $businessProfile->portfolioPosts()
            ->latest('published_at')
            ->latest()
            ->get();

        return Inertia::render('PortfolioPosts/Index', [
            'businessProfile' => $businessProfile,
            'posts' => $posts,
        ]);
    }

    public function create(Request $request, BusinessProfile $businessProfile): Response
    {
        $this->authorize('update', $businessProfile);

        return Inertia::render('PortfolioPosts/Create', [
            'businessProfile' => $businessProfile,
        ]);
    }

    public function store(Request $request, BusinessProfile $businessProfile): RedirectResponse
    {
        $this->authorize('update', $businessProfile);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'published_at' => ['nullable', 'date'],
        ]);

        $data['business_profile_id'] = $businessProfile->id;

        PortfolioPost::create($data);

        return redirect()
            ->route('dashboard.portfolio-posts.index', $businessProfile)
            ->with('success', 'Пост портфоліо створено.');
    }

    public function edit(Request $request, BusinessProfile $businessProfile, PortfolioPost $portfolioPost): Response
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $portfolioPost);

        return Inertia::render('PortfolioPosts/Edit', [
            'businessProfile' => $businessProfile,
            'post' => $portfolioPost,
        ]);
    }

    public function update(Request $request, BusinessProfile $businessProfile, PortfolioPost $portfolioPost): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('update', $portfolioPost);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:10000'],
            'published_at' => ['nullable', 'date'],
        ]);

        $portfolioPost->update($data);

        return back()->with('success', 'Пост портфоліо оновлено.');
    }

    public function destroy(Request $request, BusinessProfile $businessProfile, PortfolioPost $portfolioPost): RedirectResponse
    {
        $this->authorize('update', $businessProfile);
        $this->authorize('delete', $portfolioPost);

        $portfolioPost->delete();

        return redirect()
            ->route('dashboard.portfolio-posts.index', $businessProfile)
            ->with('success', 'Пост портфоліо видалено.');
    }
}
