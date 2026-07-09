import { Link } from '@inertiajs/react';

/**
 * RentCeylon lotus mark, recreated as SVG from the brand logo (navy/blue
 * petals fanning out from a gold ring). Kept as vector so it stays crisp
 * at any size and the header can recolor it for dark backgrounds if needed.
 */
export function LogoMark({ className = 'h-9 w-9' }: { className?: string }) {
    return (
        <svg viewBox="0 0 100 100" className={className} fill="none" xmlns="http://www.w3.org/2000/svg">
            {/* outer petals — light blue */}
            <path d="M28,32 C14,42 10,60 28,76 C30,62 30,48 28,32 Z" fill="#AFCBEF" />
            <path d="M72,32 C86,42 90,60 72,76 C70,62 70,48 72,32 Z" fill="#AFCBEF" />
            {/* mid petals — medium blue */}
            <path d="M37,14 C23,28 19,55 35,78 C40,60 42,35 37,14 Z" fill="#3868AE" />
            <path d="M63,14 C77,28 81,55 65,78 C60,60 58,35 63,14 Z" fill="#3868AE" />
            {/* centre petal — navy */}
            <path d="M50,7 C39,25 37,55 50,80 C63,55 61,25 50,7 Z" fill="#123063" />
            {/* gold ring */}
            <circle cx="50" cy="80" r="16" fill="#C6900F" />
            <circle cx="50" cy="80" r="10.5" fill="#EFC468" />
            <circle cx="50" cy="80" r="4" fill="#123063" />
        </svg>
    );
}

export function LogoWordmark({ className = 'text-xl' }: { className?: string }) {
    return (
        <span className={`font-serif font-bold tracking-tight ${className}`}>
            <span style={{ color: '#123063' }}>Rent</span>{' '}
            <span style={{ color: '#C6900F' }}>Ceylon</span>
        </span>
    );
}

export default function Logo({ markClassName = 'h-9 w-9', wordmarkClassName = 'text-xl', asLink = true }: {
    markClassName?: string; wordmarkClassName?: string; asLink?: boolean;
}) {
    const inner = (
        <span className="flex items-center gap-2">
            <LogoMark className={markClassName} />
            <LogoWordmark className={wordmarkClassName} />
        </span>
    );
    return asLink ? <Link href="/">{inner}</Link> : inner;
}
