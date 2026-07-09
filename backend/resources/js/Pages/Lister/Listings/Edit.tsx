import { Head, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { Upload } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

interface AttrDef { key: string; label: string; type: string; options: string[] | null; unit: string | null; required: boolean; }
interface CategoryOption { id: number; name: string; attributes: AttrDef[]; }
interface CategoryGroup { group: string; options: CategoryOption[]; }
interface ExistingListing {
    id: number; title: string; description: string; condition: string;
    daily_rate: number; security_deposit: number; city: string; district: string; category_id: number;
    photos: { id: number; url: string }[]; attrs: Record<string, string>;
}

export default function Edit({ listing, categoryGroups, isIdVerified, cities }: {
    listing: ExistingListing; categoryGroups: CategoryGroup[]; isIdVerified: boolean; cities: string[];
}) {
    const allOptions = useMemo(() => categoryGroups.flatMap((g) => g.options), [categoryGroups]);

    const { data, setData, post, processing, errors } = useForm<{
        category_id: string; title: string; description: string; condition: string;
        daily_rate: string; security_deposit: string; city: string; district: string;
        photos: File[]; attrs: Record<string, string | boolean>;
    }>({
        category_id: String(listing.category_id), title: listing.title, description: listing.description,
        condition: listing.condition, daily_rate: String(listing.daily_rate), security_deposit: String(listing.security_deposit ?? 0),
        city: listing.city, district: listing.district, photos: [], attrs: listing.attrs ?? {},
    });

    const [newPreviews, setNewPreviews] = useState<string[]>([]);
    const selectedCategory = allOptions.find((o) => String(o.id) === data.category_id);

    const onFiles = (files: FileList | null) => {
        if (!files) return;
        const arr = Array.from(files);
        setData('photos', [...data.photos, ...arr]);
        setNewPreviews((p) => [...p, ...arr.map((f) => URL.createObjectURL(f))]);
    };

    const setAttr = (key: string, value: string | boolean) => setData('attrs', { ...data.attrs, [key]: value });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('lister.listings.update', listing.id), { forceFormData: true });
    };

    return (
        <SiteLayout>
            <Head title={`Edit · ${listing.title}`} />
            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-2xl font-bold text-gray-900">Edit listing</h1>

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <section className="card space-y-4 p-5">
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Category</label>
                            <select className="input mt-1" value={data.category_id} onChange={(e) => setData('category_id', e.target.value)}>
                                {categoryGroups.map((g) => (
                                    <optgroup key={g.group} label={g.group}>
                                        {g.options.map((o) => <option key={o.id} value={o.id}>{o.name}</option>)}
                                    </optgroup>
                                ))}
                            </select>
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Title</label>
                            <input className="input mt-1" value={data.title} onChange={(e) => setData('title', e.target.value)} />
                            {errors.title && <p className="mt-1 text-sm text-rose-600">{errors.title}</p>}
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Description</label>
                            <textarea className="input mt-1" rows={4} value={data.description} onChange={(e) => setData('description', e.target.value)} />
                            {errors.description && <p className="mt-1 text-sm text-rose-600">{errors.description}</p>}
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Condition</label>
                            <select className="input mt-1" value={data.condition} onChange={(e) => setData('condition', e.target.value)}>
                                <option value="new">New</option>
                                <option value="like_new">Like new</option>
                                <option value="good">Good</option>
                                <option value="fair">Fair</option>
                            </select>
                        </div>
                    </section>

                    {selectedCategory && selectedCategory.attributes.length > 0 && (
                        <section className="card space-y-4 p-5">
                            <h2 className="font-bold text-gray-900">{selectedCategory.name} details</h2>
                            <div className="grid gap-4 sm:grid-cols-2">
                                {selectedCategory.attributes.map((a) => (
                                    <div key={a.key}>
                                        <label className="text-xs font-semibold uppercase text-gray-500">{a.label}</label>
                                        {a.type === 'select' && a.options ? (
                                            <select className="input mt-1" value={(data.attrs[a.key] as string) ?? ''} onChange={(e) => setAttr(a.key, e.target.value)}>
                                                <option value="">Select…</option>
                                                {a.options.map((o) => <option key={o} value={o}>{o}</option>)}
                                            </select>
                                        ) : a.type === 'boolean' ? (
                                            <label className="mt-2 flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" checked={data.attrs[a.key] === '1' || data.attrs[a.key] === true} onChange={(e) => setAttr(a.key, e.target.checked)} className="rounded border-gray-300 text-brand-700 focus:ring-brand-700" />
                                                Yes
                                            </label>
                                        ) : (
                                            <input className="input mt-1" value={(data.attrs[a.key] as string) ?? ''} onChange={(e) => setAttr(a.key, e.target.value)} />
                                        )}
                                    </div>
                                ))}
                            </div>
                        </section>
                    )}

                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">Pricing & deposit</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">Daily rate (LKR)</label>
                                <input type="number" className="input mt-1" value={data.daily_rate} onChange={(e) => setData('daily_rate', e.target.value)} />
                            </div>
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">Security deposit (LKR)</label>
                                <input type="number" className="input mt-1" value={data.security_deposit} onChange={(e) => setData('security_deposit', e.target.value)} />
                            </div>
                        </div>
                    </section>

                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">Location</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">City</label>
                                <select className="input mt-1" value={data.city} onChange={(e) => setData('city', e.target.value)}>
                                    {cities.map((c) => <option key={c} value={c}>{c}</option>)}
                                </select>
                            </div>
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">District</label>
                                <input className="input mt-1" value={data.district} onChange={(e) => setData('district', e.target.value)} />
                            </div>
                        </div>
                    </section>

                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">Photos</h2>
                        <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                            {listing.photos.map((p) => (
                                <div key={p.id} className="aspect-square overflow-hidden rounded-xl">
                                    <img src={p.url} className="h-full w-full object-cover" alt="" />
                                </div>
                            ))}
                            {newPreviews.map((src, i) => (
                                <div key={i} className="relative aspect-square overflow-hidden rounded-xl ring-2 ring-gold-400">
                                    <img src={src} className="h-full w-full object-cover" alt="" />
                                </div>
                            ))}
                            <label className="flex aspect-square cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-300 text-gray-400 hover:border-gray-400">
                                <Upload className="h-6 w-6" />
                                <span className="text-xs">Add photo</span>
                                <input type="file" accept="image/*" multiple className="hidden" onChange={(e) => onFiles(e.target.files)} />
                            </label>
                        </div>
                        <p className="text-xs text-gray-400">New photos are added alongside existing ones.</p>
                    </section>

                    <button type="submit" disabled={processing} className="btn-primary w-full">
                        {processing ? 'Saving…' : 'Save changes'}
                    </button>
                </form>
            </div>
        </SiteLayout>
    );
}
