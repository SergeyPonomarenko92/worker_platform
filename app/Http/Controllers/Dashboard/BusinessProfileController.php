<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BusinessProfile;
use App\Support\ContactFieldNormalizer;
use App\Support\QueryParamNormalizer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
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

    private function normalizeNullableText(?string $raw): ?string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return null;
        }

        return $v;
    }

    private function normalizeCountryCode(?string $raw): string
    {
        $v = QueryParamNormalizer::text($raw);

        if ($v === '') {
            return 'UA';
        }

        // Robustness: accept inputs like "u a", "UA!", "USA" etc.
        // Keep only ASCII letters and take the first two characters.
        $v = preg_replace('/[^A-Za-z]/', '', $v) ?? '';
        $v = strtoupper($v);

        if (strlen($v) < 2) {
            return 'UA';
        }

        return substr($v, 0, 2);
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

    private function validateWebsiteOrFail(?string $website): void
    {
        if ($website === null) {
            return;
        }

        // Extra safety: do not persist non-URL / non-http(s) values (e.g. "javascript:...").
        if (filter_var($website, FILTER_VALIDATE_URL) === false) {
            throw ValidationException::withMessages([
                'website' => 'Некоректний URL вебсайту.',
            ]);
        }

        $scheme = parse_url($website, PHP_URL_SCHEME);
        if (! in_array(strtolower((string) $scheme), ['http', 'https'], true)) {
            throw ValidationException::withMessages([
                'website' => 'URL вебсайту має починатися з http:// або https://',
            ]);
        }
    }

    private function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'about' => ['nullable', 'string', 'max:5000'],
            // Allow a bit more input length, because we normalize to ISO-3166 alpha-2 anyway (e.g. "u a", "USA").
            'country_code' => ['nullable', 'string', 'max:20'],
            'city' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'website' => ['nullable', 'string', 'max:255'],
            'is_active' => ['nullable', 'boolean'],
        ];
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

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();

        $data = $request->validate($this->rules());

        $data['user_id'] = $user->id;
        $data['slug'] = $this->uniqueSlug($data['name']);
        $data['is_active'] = (bool)($data['is_active'] ?? true);

        $data['about'] = $this->normalizeNullableText($data['about'] ?? null);
        $data['country_code'] = $this->normalizeCountryCode($data['country_code'] ?? null);
        $data['city'] = $this->normalizeNullableText($data['city'] ?? null);
        $data['address'] = $this->normalizeNullableText($data['address'] ?? null);

        $data['website'] = $this->normalizeWebsite($data['website'] ?? null);
        $this->validateWebsiteOrFail($data['website']);

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

        $data = $request->validate($this->rules());

        $data['slug'] = $this->uniqueSlug($data['name'], $businessProfile->id);

        $data['about'] = $this->normalizeNullableText($data['about'] ?? null);
        $data['country_code'] = $this->normalizeCountryCode($data['country_code'] ?? null);
        $data['city'] = $this->normalizeNullableText($data['city'] ?? null);
        $data['address'] = $this->normalizeNullableText($data['address'] ?? null);

        $data['website'] = $this->normalizeWebsite($data['website'] ?? null);
        $this->validateWebsiteOrFail($data['website']);

        $data['phone'] = $this->normalizePhone($data['phone'] ?? null);
        if (array_key_exists('is_active', $data)) {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $businessProfile->update($data);

        return back()->with('success', 'Профіль бізнесу оновлено.');
    }
}
