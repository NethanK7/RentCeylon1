import { BadgeChip } from '@/types/app';
import Icon from './Icon';

/**
 * Global Constraint 01: earned and paid badges must NEVER look alike.
 * Earned  → coloured OUTLINE pill (solid border, tinted bg, category colour).
 * Promoted→ SOLID amber pill with a ribbon/sparkle — a different shape language.
 * They are always rendered in separate, labelled zones by the caller.
 */

const EARNED_COLORS: Record<string, string> = {
    emerald: 'border-emerald-500 text-emerald-700 bg-emerald-50',
    blue: 'border-blue-500 text-blue-700 bg-blue-50',
    teal: 'border-teal-500 text-teal-700 bg-teal-50',
};

export function EarnedBadge({ badge }: { badge: BadgeChip }) {
    const color = EARNED_COLORS[badge.color] ?? 'border-gray-400 text-gray-700 bg-gray-50';
    return (
        <span className={`chip border ${color}`} title={`${badge.label}: ${badge.name}`}>
            <Icon name={badge.icon} className="h-3.5 w-3.5" />
            {badge.name}
        </span>
    );
}

export function PromotedBadge({ badge }: { badge: BadgeChip }) {
    return (
        <span
            className="chip bg-amber-400 text-amber-950 shadow-sm ring-1 ring-amber-500/40"
            title={`${badge.label}: ${badge.name}`}
        >
            <Icon name={badge.icon} className="h-3.5 w-3.5" />
            {badge.label}
        </span>
    );
}

export function EarnedZone({ badges, label = 'Earned' }: { badges: BadgeChip[]; label?: string }) {
    if (!badges?.length) return null;
    return (
        <div>
            <p className="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-gray-500">{label}</p>
            <div className="flex flex-wrap gap-1.5">
                {badges.map((b, i) => <EarnedBadge key={i} badge={b} />)}
            </div>
        </div>
    );
}

export function PromotedZone({ badges }: { badges: BadgeChip[] }) {
    if (!badges?.length) return null;
    return (
        <div>
            <p className="mb-1.5 text-[11px] font-semibold uppercase tracking-wide text-amber-600">Promoted</p>
            <div className="flex flex-wrap gap-1.5">
                {badges.map((b, i) => <PromotedBadge key={i} badge={b} />)}
            </div>
        </div>
    );
}
