<?php

namespace App\Http\Controllers\Admin\Concerns;

use Illuminate\Http\Request;

trait ResolvesTableQuery
{
    /**
     * Membersihkan keyword pencarian dari karakter kontrol dan membatasi panjangnya.
     */
    protected function cleanSearch(mixed $search): ?string
    {
        if (! is_string($search)) {
            return null;
        }

        $search = trim(preg_replace('/[\x00-\x1F\x7F]/u', '', $search) ?? '');

        return $search === '' ? null : mb_substr($search, 0, 100);
    }

    /**
     * Menentukan kolom & arah sorting menggunakan whitelist.
     *
     * Whiltelist mencegah ORDER BY injection karena nama kolom tidak pernah
     * diambil mentah-mentah dari input pengguna.
     *
     * @param  list<string>  $allowed
     * @return array{0: string, 1: string}
     */
    protected function resolveSort(Request $request, array $allowed, string $defaultColumn = 'created_at', string $defaultDirection = 'desc'): array
    {
        $column = (string) $request->string('sort', $defaultColumn);
        $direction = strtolower((string) $request->string('direction', $defaultDirection));

        if (! in_array($column, $allowed, true)) {
            $column = $defaultColumn;
        }

        return [$column, $direction === 'asc' ? 'asc' : 'desc'];
    }
}
