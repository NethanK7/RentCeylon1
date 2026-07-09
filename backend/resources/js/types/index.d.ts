// Canonical shared types live in ./app. Re-export here so both
// `@/types` and `@/types/app` resolve to the same definitions, and the
// Inertia core augmentation in global.d.ts picks up the real (nullable) shape.
export * from './app';
export type { AuthUser as User } from './app';
