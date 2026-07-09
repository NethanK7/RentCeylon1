import { Head, useForm } from '@inertiajs/react';
import { useMemo, useState } from 'react';
import { AlertTriangle, Upload, X } from 'lucide-react';
import SiteLayout from '@/Layouts/SiteLayout';

interface AttrDef { key: string; label: string; type: string; options: string[] | null; unit: string | null; required: boolean; }
interface CategoryOption { id: number; name: string; attributes: AttrDef[]; }
interface CategoryGroup { group: string; options: CategoryOption[]; }

export default function Create({ categoryGroups, isIdVerified, cities }: {
    categoryGroups: CategoryGroup[];
    isIdVerified: boolean;
    cities: string[];
}) {
    const allOptions = useMemo(() => categoryGroups.flatMap((g) => g.options), [categoryGroups]);

    const { data, setData, post, processing, errors } = useForm<{
        category_id: string; title: string; description: string; condition: string;
        daily_rate: string; security_deposit: string; city: string; district: string;
        photos: File[]; attrs: Record<string, string | boolean>;
    }>({
        category_id: '', title: '', description: '', condition: 'good',
        daily_rate: '', security_deposit: '', city: '', district: '',
        photos: [], attrs: {},
    });

    const [previews, setPreviews] = useState<string[]>([]);
    const selectedCategory = allOptions.find((o) => String(o.id) === data.category_id);

    const onFiles = (files: FileList | null) => {
        if (!files) return;
        const arr = Array.from(files);
        setData('photos', [...data.photos, ...arr]);
        setPreviews((p) => [...p, ...arr.map((f) => URL.createObjectURL(f))]);
    };

    const removePhoto = (i: number) => {
        setData('photos', data.photos.filter((_, idx) => idx !== i));
        setPreviews((p) => p.filter((_, idx) => idx !== i));
    };

    const setAttr = (key: string, value: string | boolean) => setData('attrs', { ...data.attrs, [key]: value });

    const submit = (e: React.FormEvent) => {
        e.preventDefault();
        post(route('lister.listings.store'), { forceFormData: true });
    };

    return (
        <SiteLayout>
            <Head title="Create listing" />
            <div className="mx-auto max-w-3xl px-4 py-8 sm:px-6 lg:px-8">
                <h1 className="font-display text-2xl font-bold text-gray-900">Create a listing</h1>
                <p className="mt-1 text-gray-500">Add details, photos and pricing. Minimum 3 photos required.</p>

                {!isIdVerified && (
                    <div className="mt-4 flex items-center gap-3 rounded-2xl border border-amber-300 bg-amber-50 p-4 text-amber-800">
                        <AlertTriangle className="h-6 w-6 flex-shrink-0" />
                        <p className="text-sm">You're not ID-verified yet — this listing will be saved but hidden from renters until your ID is approved.</p>
                    </div>
                )}

                <form onSubmit={submit} className="mt-6 space-y-6">
                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">What are you listing?</h2>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Category</label>
                            <select className="input mt-1" value={data.category_id} onChange={(e) => { setData('category_id', e.target.value); setData('attrs', {}); }}>
                                <option value="">Select a category…</option>
                                {categoryGroups.map((g) => (
                                    <optgroup key={g.group} label={g.group}>
                                        {g.options.map((o) => <option key={o.id} value={o.id}>{o.name}</option>)}
                                    </optgroup>
                                ))}
                            </select>
                            {errors.category_id && <p className="mt-1 text-sm text-rose-600">{errors.category_id}</p>}
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Title</label>
                            <input className="input mt-1" value={data.title} onChange={(e) => setData('title', e.target.value)} placeholder="e.g. Sony A7 IV Mirrorless Camera Kit" />
                            {errors.title && <p className="mt-1 text-sm text-rose-600">{errors.title}</p>}
                        </div>
                        <div>
                            <label className="text-xs font-semibold uppercase text-gray-500">Description</label>
                            <textarea className="input mt-1" rows={4} value={data.description} onChange={(e) => setData('description', e.target.value)} placeholder="Condition, what's included, pickup notes…" />
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
                                        <label className="text-xs font-semibold uppercase text-gray-500">{a.label}{a.required && ' *'}</label>
                                        {a.type === 'select' && a.options ? (
                                            <select className="input mt-1" value={(data.attrs[a.key] as string) ?? ''} onChange={(e) => setAttr(a.key, e.target.value)}>
                                                <option value="">Select…</option>
                                                {a.options.map((o) => <option key={o} value={o}>{o}</option>)}
                                            </select>
                                        ) : a.type === 'boolean' ? (
                                            <label className="mt-2 flex items-center gap-2 text-sm text-gray-700">
                                                <input type="checkbox" checked={!!data.attrs[a.key]} onChange={(e) => setAttr(a.key, e.target.checked)} className="rounded border-gray-300 text-brand-700 focus:ring-brand-700" />
                                                Yes
                                            </label>
                                        ) : a.type === 'number' ? (
                                            <input type="number" className="input mt-1" value={(data.attrs[a.key] as string) ?? ''} onChange={(e) => setAttr(a.key, e.target.value)} placeholder={a.unit ?? ''} />
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
                                {errors.daily_rate && <p className="mt-1 text-sm text-rose-600">{errors.daily_rate}</p>}
                            </div>
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">Security deposit (LKR)</label>
                                <input type="number" className="input mt-1" value={data.security_deposit} onChange={(e) => setData('security_deposit', e.target.value)} />
                            </div>
                        </div>
                        <p className="text-xs text-gray-500">Platform fee is tiered automatically at checkout: 10% (≤10k), 7% (10–50k), 5% (50k+).</p>
                    </section>

                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">Location</h2>
                        <div className="grid gap-4 sm:grid-cols-2">
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">City</label>
                                <select className="input mt-1" value={data.city} onChange={(e) => setData('city', e.target.value)}>
                                    <option value="">Select…</option>
                                    {cities.map((c) => <option key={c} value={c}>{c}</option>)}
                                </select>
                                {errors.city && <p className="mt-1 text-sm text-rose-600">{errors.city}</p>}
                            </div>
                            <div>
                                <label className="text-xs font-semibold uppercase text-gray-500">District</label>
                                <input className="input mt-1" value={data.district} onChange={(e) => setData('district', e.target.value)} placeholder="e.g. Western" />
                                {errors.district && <p className="mt-1 text-sm text-rose-600">{errors.district}</p>}
                            </div>
                        </div>
                        <p className="text-xs text-gray-400">Full address stays private — only city/district shown publicly.</p>
                    </section>

                    <section className="card space-y-4 p-5">
                        <h2 className="font-bold text-gray-900">Photos <span className="text-sm font-normal text-gray-500">(minimum 3)</span></h2>
                        <div className="grid grid-cols-3 gap-3 sm:grid-cols-4">
                            {previews.map((src, i) => (
                                <div key={i} className="group relative aspect-square overflow-hidden rounded-xl">
                                    <img src={src} className="h-full w-full object-cover" alt="" />
                                    <button type="button" onClick={() => removePhoto(i)} className="absolute right-1 top-1 rounded-full bg-black/60 p-1 text-white opacity-0 transition group-hover:opacity-100">
                                        <X className="h-3.5 w-3.5" />
                                    </button>
                                </div>
                            ))}
                            <label className="flex aspect-square cursor-pointer flex-col items-center justify-center gap-1 rounded-xl border-2 border-dashed border-gray-300 text-gray-400 hover:border-gray-400">
                                <Upload className="h-6 w-6" />
                                <span className="text-xs">Add photo</span>
                                <input type="file" accept="image/*" multiple className="hidden" onChange={(e) => onFiles(e.target.files)} />
                            </label>
                        </div>
                        {errors.photos && <p className="text-sm text-rose-600">{errors.photos}</p>}
                    </section>

                    <button type="submit" disabled={processing} className="btn-primary w-full">
                        {processing ? 'Publishing…' : isIdVerified ? 'Publish listing' : 'Save listing'}
                    </button>
                </form>
            </div>
        </SiteLayout>
    );
}
