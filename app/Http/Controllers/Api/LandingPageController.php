<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\LandingSetting;
use App\Support\StoredImageForApi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class LandingPageController extends Controller
{
    private function publicImageUrl(?string $u): ?string
    {
        return StoredImageForApi::resolve($u);
    }

    /** @return array<string, mixed> */
    private function toApiArray(LandingSetting $row): array
    {
        return [
            'heroHeadline' => $row->hero_headline,
            'heroBody' => $row->hero_body,
            'heroImageUrl' => $this->publicImageUrl($row->hero_image_url),
            'featureKicker' => $row->feature_kicker,
            'featureTitle' => $row->feature_title,
            'featureCtaLabel' => $row->feature_cta_label,
            'featureCtaHref' => $row->feature_cta_href,
            'featureImageUrl' => $this->publicImageUrl($row->feature_image_url),
            'featureCaptionRight' => $row->feature_caption_right,
        ];
    }

    public function show(): JsonResponse
    {
        $row = LandingSetting::query()->find(1);
        if (! $row) {
            return response()->json([
                'heroHeadline' => 'Welcome to PortuHub — knives & tools you can trust',
                'heroBody' => 'Discover curated pocket knives, EDC gear, and quality blades.',
                'heroImageUrl' => $this->publicImageUrl('/placeholder.svg'),
                'featureKicker' => 'Built for daily use.',
                'featureTitle' => 'Every piece is chosen for solid build quality and real-world use.',
                'featureCtaLabel' => 'View all products',
                'featureCtaHref' => '/products',
                'featureImageUrl' => $this->publicImageUrl('/placeholder.svg'),
                'featureCaptionRight' => 'Sharp selection',
            ]);
        }

        return response()->json($this->toApiArray($row));
    }

    private function adminToken(): ?string
    {
        $token = request()->cookie('admin_session');
        if ($token) {
            return $token;
        }
        $header = request()->header('Authorization');
        if ($header && str_starts_with($header, 'Bearer ')) {
            return substr($header, 7);
        }

        return null;
    }

    private function isAdmin(): bool
    {
        $token = $this->adminToken();
        if (! $token) {
            return false;
        }

        return \App\Models\AdminSession::where('token', $token)
            ->where('expires_at', '>', now())
            ->exists();
    }

    private function ensureLandingRow(): LandingSetting
    {
        $row = LandingSetting::query()->find(1);
        if ($row) {
            return $row;
        }
        $row = new LandingSetting;
        $row->id = 1;
        $row->hero_headline = 'Welcome to PortuHub — knives & tools you can trust';
        $row->hero_body = 'Discover curated pocket knives, EDC gear, and quality blades.';
        $row->hero_image_url = '/placeholder.svg';
        $row->feature_kicker = 'Built for daily use.';
        $row->feature_title = 'Every piece is chosen for solid build quality and real-world use.';
        $row->feature_cta_label = 'View all products';
        $row->feature_cta_href = '/products';
        $row->feature_image_url = '/placeholder.svg';
        $row->feature_caption_right = 'Sharp selection';
        $row->save();

        return $row;
    }

    public function update(Request $request): JsonResponse
    {
        if (! $this->isAdmin()) {
            return response()->json([
                'error' => 'Unauthorized',
                'message' => 'Sign in again to save changes.',
            ], 401);
        }
        $row = $this->ensureLandingRow();

        $validated = $request->validate([
            'heroHeadline' => 'required|string|max:500',
            'heroBody' => 'nullable|string|max:5000',
            'heroImageUrl' => 'nullable|string|max:3000000',
            'featureKicker' => 'nullable|string|max:500',
            'featureTitle' => 'nullable|string|max:5000',
            'featureCtaLabel' => 'nullable|string|max:200',
            'featureCtaHref' => 'nullable|string|max:500',
            'featureImageUrl' => 'nullable|string|max:3000000',
            'featureCaptionRight' => 'nullable|string|max:500',
        ]);

        $row->update([
            'hero_headline' => $validated['heroHeadline'],
            'hero_body' => $validated['heroBody'] ?? '',
            'hero_image_url' => $validated['heroImageUrl'] ?? '',
            'feature_kicker' => $validated['featureKicker'] ?? '',
            'feature_title' => $validated['featureTitle'] ?? '',
            'feature_cta_label' => $validated['featureCtaLabel'] ?? 'View all products',
            'feature_cta_href' => $validated['featureCtaHref'] ?? '/products',
            'feature_image_url' => $validated['featureImageUrl'] ?? '',
            'feature_caption_right' => $validated['featureCaptionRight'] ?? '',
        ]);
        $row->refresh();

        return response()->json($this->toApiArray($row));
    }
}
