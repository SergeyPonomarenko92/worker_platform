<?php

namespace App\Http\Controllers;

use App\Models\Deal;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ReviewController extends Controller
{
    public function create(Request $request, Deal $deal): Response
    {
        $this->authorize('create', [Review::class, $deal]);

        $deal->load([
            'businessProfile:id,name,slug',
            'offer:id,title',
        ]);

        return Inertia::render('Reviews/Create', [
            'deal' => $deal,
            'businessProfile' => $deal->businessProfile,
        ]);
    }

    public function store(Request $request, Deal $deal): RedirectResponse
    {
        $this->authorize('create', [Review::class, $deal]);

        $data = $request->validate([
            'rating' => ['required', 'integer', 'min:1', 'max:5'],
            'body' => ['nullable', 'string', 'max:2000'],
        ]);

        Review::create([
            'deal_id' => $deal->id,
            'business_profile_id' => $deal->business_profile_id,
            'client_user_id' => $request->user()->id,
            'rating' => $data['rating'],
            'body' => $data['body'] ?? null,
        ]);

        return redirect()
            ->route('providers.show', $deal->businessProfile->slug)
            ->with('success', 'Дякуємо! Відгук додано.');
    }
}
