export type Role = 'renter' | 'lister' | 'admin' | 'manager';

export interface AuthUser {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string | null;
    role: Role;
    avatar_path: string | null;
    id_verification_status: 'unsubmitted' | 'pending' | 'approved' | 'rejected';
    is_id_verified: boolean;
}

export interface BadgeChip {
    key?: string;
    name: string;
    icon: string;
    color: string;
    label: string;
    class?: 'earned' | 'paid';
}

export interface NavCategory {
    id: number;
    name: string;
    slug: string;
    icon: string | null;
    kind: string;
    children: { name: string; slug: string; icon: string | null }[];
}

export interface ListingCardData {
    id: number;
    title: string;
    slug: string;
    daily_rate: number;
    security_deposit?: number;
    currency: string;
    city: string;
    district?: string;
    rating_avg: number;
    rating_count: number;
    category: string;
    photo: string | null;
    photos?: string[];
    earnedBadges: BadgeChip[];
    promotedBadges: BadgeChip[];
}

export interface AttributeFilter {
    key: string;
    label: string;
    type: 'select' | 'multiselect' | 'number' | 'boolean' | 'text';
    options: string[] | null;
    unit: string | null;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    links: { url: string | null; label: string; active: boolean }[];
    total: number;
    from: number | null;
    to: number | null;
}

export type PageProps<T extends Record<string, unknown> = Record<string, unknown>> = T & {
    auth: { user: AuthUser | null };
    nav: { categories: NavCategory[] };
    wishlist: { ids: number[] };
    flash: { success: string | null; error: string | null };
};
