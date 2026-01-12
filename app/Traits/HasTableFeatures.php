<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

trait HasTableFeatures
{
    /**
     * Apply search to the query.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array<string>  $searchColumns  Columns to search in
     * @return Builder
     */
    protected function applySearch(Builder $query, Request $request, array $searchColumns): Builder
    {
        if (! $request->filled('search') || empty($searchColumns)) {
            return $query;
        }

        $search = $request->input('search');

        return $query->where(function ($q) use ($search, $searchColumns) {
            foreach ($searchColumns as $index => $column) {
                $method = $index === 0 ? 'where' : 'orWhere';

                // Support for relationship columns (e.g., 'roles.name')
                if (str_contains($column, '.')) {
                    [$relation, $relationColumn] = explode('.', $column, 2);
                    $q->{$method . 'Has'}($relation, function ($subQuery) use ($relationColumn, $search) {
                        $subQuery->where($relationColumn, 'like', "%{$search}%");
                    });
                } else {
                    $q->{$method}($column, 'like', "%{$search}%");
                }
            }
        });
    }

    /**
     * Apply filters to the query.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array<string, callable|array>  $filters  Filter definitions
     * @return Builder
     *
     * Filter can be:
     * - A column name (string): exact match filter
     * - An array with 'column' and optional 'operator': ['column' => 'status', 'operator' => '=']
     * - A callable for custom filter logic: function($query, $value) { ... }
     */
    protected function applyFilters(Builder $query, Request $request, array $filters): Builder
    {
        foreach ($filters as $filterName => $filterConfig) {
            if (! $request->filled($filterName)) {
                continue;
            }

            $value = $request->input($filterName);

            if (is_callable($filterConfig)) {
                // Custom filter callback
                $filterConfig($query, $value);
            } elseif (is_array($filterConfig)) {
                // Array config with column and optional operator
                $column = $filterConfig['column'] ?? $filterName;
                $operator = $filterConfig['operator'] ?? '=';

                if (isset($filterConfig['boolean'])) {
                    // Boolean filter (e.g., status=active -> is_active=true)
                    $query->where($column, $value === $filterConfig['boolean']['true']);
                } elseif (isset($filterConfig['relation'])) {
                    // Relationship filter
                    $query->whereHas($filterConfig['relation'], function ($q) use ($column, $operator, $value) {
                        $q->where($column, $operator, $value);
                    });
                } else {
                    $query->where($column, $operator, $value);
                }
            } else {
                // Simple column name (string)
                $query->where($filterConfig, $value);
            }
        }

        return $query;
    }

    /**
     * Apply sorting to the query.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array<string>  $allowedColumns  Columns that are allowed to be sorted
     * @param  string  $defaultColumn  Default column to sort by
     * @param  string  $defaultDirection  Default sort direction
     * @return Builder
     */
    protected function applySorting(
        Builder $query,
        Request $request,
        array $allowedColumns,
        string $defaultColumn = 'created_at',
        string $defaultDirection = 'desc'
    ): Builder {
        $sortColumn = $request->input('sort', $defaultColumn);
        $sortDirection = $request->input('direction', $defaultDirection);

        // Validate sort column
        if (! in_array($sortColumn, $allowedColumns)) {
            $sortColumn = $defaultColumn;
        }

        // Validate sort direction
        if (! in_array($sortDirection, ['asc', 'desc'])) {
            $sortDirection = $defaultDirection;
        }

        return $query->orderBy($sortColumn, $sortDirection);
    }

    /**
     * Apply all table features (search, filter, sort) to query.
     *
     * @param  Builder  $query
     * @param  Request  $request
     * @param  array  $config  Configuration array with keys: searchColumns, filters, sortColumns, defaultSort, defaultDirection
     * @return Builder
     */
    protected function applyTableQuery(Builder $query, Request $request, array $config): Builder
    {
        // Apply search
        if (isset($config['searchColumns'])) {
            $query = $this->applySearch($query, $request, $config['searchColumns']);
        }

        // Apply filters
        if (isset($config['filters'])) {
            $query = $this->applyFilters($query, $request, $config['filters']);
        }

        // Apply sorting
        if (isset($config['sortColumns'])) {
            $query = $this->applySorting(
                $query,
                $request,
                $config['sortColumns'],
                $config['defaultSort'] ?? 'created_at',
                $config['defaultDirection'] ?? 'desc'
            );
        }

        return $query;
    }

    /**
     * Get per page value.
     *
     * @param  Request  $request
     * @param  int  $default
     * @return int
     */
    protected function getPerPage(Request $request, int $default = 15): int
    {
        $perPage = (int) $request->input('per_page', $default);

        // Ensure per_page is reasonable (1-100)
        if ($perPage < 1 || $perPage > 100) {
            return $default;
        }

        return $perPage;
    }

    /**
     * Get all filter parameters for frontend.
     *
     * @param  Request  $request
     * @param  array<string>  $filterNames  Names of filter parameters to include
     * @return array
     */
    protected function getTableFilters(Request $request, array $filterNames = []): array
    {
        return $request->only(array_merge(['search', 'sort', 'direction', 'per_page'], $filterNames));
    }
}
