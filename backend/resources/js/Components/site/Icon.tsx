import {
    Camera, Plane, Speaker, Gamepad2, Laptop, Car, Truck, Bike, Wrench,
    PartyPopper, Tent, Sofa, Shirt, Music, Building2, Star, ShieldCheck,
    Zap, BadgeDollarSign, Sparkles, Package, LucideProps,
} from 'lucide-react';

const MAP: Record<string, React.ComponentType<LucideProps>> = {
    Camera, Plane, Speaker, Gamepad2, Laptop, Car, Truck, Bike, Wrench,
    PartyPopper, Tent, Sofa, Shirt, Music, Building2, Star, ShieldCheck,
    Zap, BadgeDollarSign, Sparkles,
};

export default function Icon({ name, ...props }: { name: string | null } & Omit<LucideProps, 'name'>) {
    const Cmp = (name && MAP[name]) || Package;
    return <Cmp {...props} />;
}
