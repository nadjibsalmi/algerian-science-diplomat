<?php

namespace App\Modules\Search\Services;

use App\Models\User;
use App\Modules\Analytics\Services\AnalyticsService;
use App\Modules\Offers\Models\Offer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

class SearchService
{
    public function __construct(private readonly AnalyticsService $analytics) {}

    public function offers(array $filters, ?User $user = null): LengthAwarePaginator
    {
        $query = Offer::query()->published();
        $term = trim((string) ($filters['q'] ?? ''));

        if ($term !== '') {
            $like = '%'.addcslashes($term, '%_\\').'%';
            $query->where(function (Builder $q) use ($like): void {
                $q->where('title', 'ilike', $like)->orWhere('description', 'ilike', $like)
                    ->orWhere('research_field', 'ilike', $like)->orWhere('country', 'ilike', $like);
            });
        }

        foreach (['country', 'city', 'offer_type', 'category', 'research_field', 'level'] as $field) {
            if (! empty($filters[$field])) $query->where($field, $filters[$field]);
        }

        match ($filters['sort'] ?? 'relevance') {
            'newest' => $query->latest('published_at'),
            'deadline' => $query->orderByRaw('deadline IS NULL, deadline ASC'),
            default => $query->latest('published_at'),
        };

        $results = $query->with('embassy:id,official_name')->paginate((int) ($filters['per_page'] ?? 12))->withQueryString();
        $this->analytics->record($user, 'offer_search', ['filters' => $filters, 'result_count' => $results->total()]);

        return $results;
    }
}