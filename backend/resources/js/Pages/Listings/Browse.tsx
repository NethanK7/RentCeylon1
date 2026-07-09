import { Head, router } from '@inertiajs/react';
import { useState } from 'react';
import { SlidersHorizontal, X } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';
import ListingCard from '@/Components/site/ListingCard';
import { AttributeFilter, ListingCardData, Paginated } from '@/types/app';

interface Filters {
    q?: string; category?: string; min_price?: string; max_price?: string;
    city?: string; district?: string; min_rating?: string; sort?: string;
    start?: string; end?: string; attrs?: Record<string, string>;
}

export default function Browse({ listings, filters, activeCategory, attributeFilters, cities }: {
    listings: Paginated<ListingCardData>;
    filters: Filters;
    activeCategory: { name: string; slug: string; kind: string } | null;
    attributeFilters: AttributeFilter[];
    cities: string[];
}) {
    const [local, setLocal] = useState<Filters>({ ...filters, attrs: filters.attrs ?? {} });
    const [mobileOpen, setMobileOpen] = useState(false);

    const apply = (patch: Partial<Filters>) => {
        const next = { ...local, ...patch };
        setLocal(next);
        const params: Record<string, any> = {};
        Object.entries(next).forEach(([k, v]) => {
            if (k === 'attrs') {
                Object.entries(v as Record<string, string>).forEach(([ak, av]) => { if (av) params[`attrs[${ak}]`] = av; });
            } else if (v) params[k] = v;
        });
        router.get('/browse', params, { preserveState: true, preserveScroll: true, replace: true });
    };

    const setAttr = (key: string, value: string) => {
        const attrs = { ...(local.attrs ?? {}) };
        if (value) attrs[key] = value; else delete attrs[key];
        apply({ attrs });
    };

    const clearAll = () => router.get('/browse', activeCategory ? { category: activeCategory.slug } : {});

    const Sidebar = (
        <div className="space-y-6">
            <FilterGroup title="Price per day (LKR)">
                <div className="flex items-center gap-2">
                    <input type="number" placeholder="Min" defaultValue={local.min_price} className="input"
                        onBlur={(e) => apply({ min_price: e.target.value })} />
                    <span className="text-gray-400">–</span>
                    <input type="number" placeholder="Max" defaultValue={local.max_price} className="input"
                        onBlur={(e) => apply({ max_price: e.target.value })} />
                </div>
            </FilterGroup>

            <FilterGroup title="City">
                <select className="input" value={local.city ?? ''} onChange={(e) => apply({ city: e.target.value })}>
                    <option value="">Anywhere</option>
                    {cities.map((c) => <option key={c} value={c}>{c}</option>)}
                </select>
            </FilterGroup>

            <FilterGroup title="Dates">
                <div className="space-y-2">
                    <input type="date" className="input" defaultValue={local.start} onChange={(e) => apply({ start: e.target.value })} />
                    <input type="date" className="input" defaultValue={local.end} onChange={(e) => apply({ end: e.target.value })} />
                </div>
            </FilterGroup>

            <FilterGroup title="Minimum rating">
                <div className="flex gap-2">
                    {['', '3', '4', '4.5'].map((r) => (
                        <button key={r} onClick={() => apply({ min_rating: r })}
                            className={`rounded-full border px-3 py-1.5 text-sm ${local.min_rating === r ? 'border-gray-900 bg-gray-900 text-white' : 'border-gray-300 text-gray-700'}`}>
                            {r === '' ? 'Any' : `${r}+`}
                        </button>
                    ))}
                </div>
            </FilterGroup>

            {/* Typed, category-specific filters (e.g. Vehicles) */}
            {attributeFilters.filter((a) => a.type === 'select' && a.options?.length).map((a) => (
                <FilterGroup key={a.key} title={a.label}>
                    <select className="input" value={local.attrs?.[a.key] ?? ''} onChange={(e) => setAttr(a.key, e.target.value)}>
                        <option value="">Any</option>
                        {a.options!.map((o) => <option key={o} value={o}>{o}</option>)}
                    </select>
                </FilterGroup>
            ))}
            {attributeFilters.filter((a) => a.type === 'boolean').map((a) => (
                <label key={a.key} className="flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" className="rounded border-gray-300 text-brand-500 focus:ring-brand-500"
                        checked={local.attrs?.[a.key] === '1'} onChange={(e) => setAttr(a.key, e.target.checked ? '1' : '')} />
                    {a.label}
                </label>
            ))}

            <button onClick={clearAll} className="text-sm font-semibold text-brand-600 hover:underline">Clear all filters</button>
        </div>
    );

    return (
        <SiteLayout showCategories>
            <Head title={activeCategory ? `${activeCategory.name} for rent` : 'Browse rentals'} />
            <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                <div className="mb-5 flex items-center justify-between">
                    <div>
                        <h1 className="font-display text-2xl font-bold text-gray-900">
                            {activeCategory ? activeCategory.name : filters.q ? `Results for "${filters.q}"` : 'All rentals'}
                        </h1>
                        <p className="text-sm text-gray-500">{listings.total} {listings.total === 1 ? 'listing' : 'listings'}</p>
                    </div>
                    <div className="flex items-center gap-2">
                        <select className="input hidden w-auto sm:block" value={local.sort ?? 'recommended'} onChange={(e) => apply({ sort: e.target.value })}>
                            <option value="recommended">Recommended</option>
                            <option value="price_low">Price: low to high</option>
                            <option value="price_high">Price: high to low</option>
                            <option value="rating">Top rated</option>
                            <option value="newest">Newest</option>
                        </select>
                        <button onClick={() => setMobileOpen(true)} className="btn-outline lg:hidden">
                            <SlidersHorizontal className="h-4 w-4" /> Filters
                        </button>
                    </div>
                </div>

                <div className="flex gap-8">
                    <aside className="hidden w-64 flex-shrink-0 lg:block">{Sidebar}</aside>

                    <div className="flex-1">
                        {listings.data.length === 0 ? (
                            <div className="rounded-2xl border border-dashed border-gray-300 py-20 text-center text-gray-500">
                                No listings match your filters. Try widening your search.
                            </div>
                        ) : (
                            <div className="grid grid-cols-1 gap-x-5 gap-y-8 sm:grid-cols-2 xl:grid-cols-3">
                                {listings.data.map((l) => <ListingCard key={l.id} listing={l} />)}
                            </div>
                        )}

                        {listings.last_page > 1 && (
                            <div className="mt-10 flex flex-wrap justify-center gap-1">
                                {listings.links.map((link, i) => (
                                    <button key={i} disabled={!link.url}
                                        onClick={() => link.url && router.visit(link.url, { preserveScroll: true })}
                                        className={`min-w-9 rounded-lg px-3 py-2 text-sm ${link.active ? 'bg-gray-900 text-white' : link.url ? 'text-gray-700 hover:bg-gray-100' : 'text-gray-300'}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }} />
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>

            {/* Mobile filter drawer */}
            {mobileOpen && (
                <div className="fixed inset-0 z-50 lg:hidden">
                    <div className="absolute inset-0 bg-black/40" onClick={() => setMobileOpen(false)} />
                    <div className="absolute inset-y-0 left-0 w-80 max-w-[85%] overflow-y-auto bg-white p-5">
                        <div className="mb-4 flex items-center justify-between">
                            <h2 className="text-lg font-bold">Filters</h2>
                            <button onClick={() => setMobileOpen(false)}><X className="h-5 w-5" /></button>
                        </div>
                        {Sidebar}
                    </div>
                </div>
            )}
        </SiteLayout>
    );
}

function FilterGroup({ title, children }: { title: string; children: React.ReactNode }) {
    return (
        <div>
            <p className="mb-2 text-sm font-semibold text-gray-900">{title}</p>
            {children}
        </div>
    );
}
