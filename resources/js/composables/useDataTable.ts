import { router } from '@inertiajs/vue3';
import { computed, ref, watch, type Ref } from 'vue';

export type SortDirection = 'asc' | 'desc' | null;

export interface SortState {
    column: string | null;
    direction: SortDirection;
}

export interface FilterState {
    [key: string]: string | undefined;
}

export interface UseDataTableOptions<T extends FilterState> {
    routeName: string;
    routeParams?: Record<string, unknown>;
    filters: T;
    sortColumn?: string;
    sortDirection?: SortDirection;
    perPage?: number;
    debounceMs?: number;
}

export function useDataTable<T extends FilterState>(options: UseDataTableOptions<T>) {
    const {
        routeName,
        routeParams = {},
        filters: initialFilters,
        sortColumn = null,
        sortDirection = null,
        perPage: initialPerPage = 15,
        debounceMs = 300,
    } = options;

    // Initialize filter refs from initial values
    const filters = ref<T>({ ...initialFilters }) as Ref<T>;

    // Sort state
    const sort = ref<SortState>({
        column: sortColumn,
        direction: sortDirection,
    });

    // Per page state
    const perPage = ref<number>(initialPerPage);

    // Debounce timeout
    let searchTimeout: ReturnType<typeof setTimeout>;

    // Build query params
    const queryParams = computed(() => {
        const params: Record<string, string | undefined> = {};

        // Add filters
        Object.entries(filters.value).forEach(([key, value]) => {
            if (value !== undefined && value !== '') {
                params[key] = value;
            }
        });

        // Add sort
        if (sort.value.column && sort.value.direction) {
            params.sort = sort.value.column;
            params.direction = sort.value.direction;
        }

        // Add per page
        if (perPage.value) {
            params.per_page = perPage.value.toString();
        }

        return params;
    });

    // Execute search with debounce
    const executeSearch = (immediate = false) => {
        clearTimeout(searchTimeout);

        const doSearch = () => {
            router.get(route(routeName, routeParams), queryParams.value, {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            });
        };

        if (immediate) {
            doSearch();
        } else {
            searchTimeout = setTimeout(doSearch, debounceMs);
        }
    };

    // Watch filters for changes
    watch(
        [filters, perPage],
        () => {
            executeSearch();
        },
        { deep: true },
    );

    // Update filter value
    const setFilter = <K extends keyof T>(key: K, value: T[K]) => {
        filters.value[key] = value;
    };

    // Clear all filters
    const clearFilters = () => {
        Object.keys(filters.value).forEach((key) => {
            (filters.value as FilterState)[key] = undefined;
        });
        executeSearch(true);
    };

    // Toggle sort on a column
    const toggleSort = (column: string) => {
        if (sort.value.column === column) {
            // Cycle: asc -> desc -> null
            if (sort.value.direction === 'asc') {
                sort.value.direction = 'desc';
            } else if (sort.value.direction === 'desc') {
                sort.value.column = null;
                sort.value.direction = null;
            }
        } else {
            sort.value.column = column;
            sort.value.direction = 'asc';
        }
        executeSearch(true);
    };

    // Get sort direction for a column
    const getSortDirection = (column: string): SortDirection => {
        return sort.value.column === column ? sort.value.direction : null;
    };

    // Check if column is sorted
    const isSorted = (column: string): boolean => {
        return sort.value.column === column && sort.value.direction !== null;
    };

    // Check if any filters are active
    const hasActiveFilters = computed(() => {
        return Object.values(filters.value).some((value) => value !== undefined && value !== '');
    });

    return {
        filters,
        sort,
        perPage,
        queryParams,
        setFilter,
        clearFilters,
        toggleSort,
        getSortDirection,
        isSorted,
        hasActiveFilters,
        executeSearch,
    };
}
