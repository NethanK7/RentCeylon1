import Logo from '@/Components/site/Logo';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="flex min-h-screen flex-col items-center bg-gray-50 pt-10 sm:justify-center sm:pt-0">
            <Logo markClassName="h-14 w-14" wordmarkClassName="text-2xl" />

            <div className="mt-6 w-full overflow-hidden bg-white px-6 py-6 shadow-card sm:max-w-md sm:rounded-2xl">
                {children}
            </div>
        </div>
    );
}
