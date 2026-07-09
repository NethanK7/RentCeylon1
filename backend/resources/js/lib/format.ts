export function money(amount: number, currency = 'LKR'): string {
    const formatted = new Intl.NumberFormat('en-LK', {
        maximumFractionDigits: 0,
    }).format(Math.round(amount));
    return `${currency} ${formatted}`;
}

export function moneyShort(amount: number): string {
    if (amount >= 1000) return `${(amount / 1000).toFixed(amount % 1000 === 0 ? 0 : 1)}k`;
    return String(amount);
}
