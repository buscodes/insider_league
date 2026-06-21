<?php

declare(strict_types=1);

namespace App\Domains\League\ValueObjects;

final readonly class PaginationMeta
{
    public function __construct(
        public int  $currentPage,
        public int  $perPage,
        public int  $totalEntries,
        public int  $totalPages,
        public bool $hasMorePages,
    ) {}

    public function toArray(): array
    {
        return [
            'current_page'  => $this->currentPage,
            'per_page'      => $this->perPage,
            'total_entries' => $this->totalEntries,
            'total_pages'   => $this->totalPages,
            'has_more_pages' => $this->hasMorePages,
        ];
    }
}
