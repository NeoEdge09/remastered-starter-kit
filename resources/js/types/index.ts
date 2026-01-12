import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
    roles: string[];
    permissions: string[];
    isSuperAdmin: boolean;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: string;
    icon?: LucideIcon;
    isActive?: boolean;
}

export interface MenuItem {
    id: number;
    name: string;
    icon: string | null;
    route_name: string | null;
    url: string | null;
    order: number;
    children?: MenuItem[];
}

export interface Flash {
    success: string | null;
    error: string | null;
    warning: string | null;
    info: string | null;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    menus: MenuItem[];
    flash: Flash;
    ziggy: {
        location: string;
        url: string;
        port: null | number;
        defaults: Record<string, unknown>;
        routes: Record<string, string>;
    };
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    is_active: boolean;
    created_at: string;
    updated_at: string;
    roles?: Role[];
    permissions?: Permission[];
}

export interface Role {
    id: number;
    name: string;
    guard_name: string;
    permissions_count?: number;
    permissions?: Permission[];
    created_at: string;
    updated_at: string;
}

export interface Permission {
    id: number;
    name: string;
    description: string | null;
    guard_name: string;
    permission_group_id: number | null;
    group?: PermissionGroup;
    created_at: string;
    updated_at: string;
}

export interface PermissionGroup {
    id: number;
    name: string;
    description: string | null;
    order: number;
    permissions_count?: number;
    permissions?: Permission[];
    created_at: string;
    updated_at: string;
}

export interface Menu {
    id: number;
    name: string;
    route_name: string | null;
    url: string | null;
    icon: string | null;
    parent_id: number | null;
    order: number;
    permission_name: string | null;
    is_active: boolean;
    parent?: Menu;
    children?: Menu[];
    created_at: string;
    updated_at: string;
}

export interface PaginatedData<T> {
    data: T[];
    links: {
        first: string;
        last: string;
        prev: string | null;
        next: string | null;
    };
    meta: {
        current_page: number;
        from: number;
        last_page: number;
        links: PaginationLink[];
        path: string;
        per_page: number;
        to: number;
        total: number;
    };
}

export interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

export interface Activity {
    id: number;
    log_name: string;
    description: string;
    subject_type: string | null;
    subject_id: number | null;
    causer_type: string | null;
    causer_id: number | null;
    event: string | null;
    properties: Record<string, unknown>;
    batch_uuid: string | null;
    created_at: string;
    updated_at: string;
    causer?: User | null;
    subject?: Record<string, unknown> | null;
}

export interface RouteAccess {
    id: number;
    route_name: string;
    route_uri: string | null;
    route_method: string | null;
    permission_name: string | null;
    permission_id: number | null;
    is_active: boolean;
    is_public: boolean;
    description: string | null;
    created_at: string;
    updated_at: string;
    permission?: Permission | null;
}
