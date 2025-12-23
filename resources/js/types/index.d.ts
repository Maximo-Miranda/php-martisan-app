import { InertiaLinkProps } from '@inertiajs/vue3';
import type { LucideIcon } from 'lucide-vue-next';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon;
    isActive?: boolean;
}

export type AppPageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
};

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    current_project_id: number | null;
    created_at: string;
    updated_at: string;
}

export interface Project {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    owner_id: number;
    owner?: User;
    members?: User[];
    members_count?: number;
    invitations?: ProjectInvitation[];
    is_owner?: number | boolean;
    created_at: string;
    updated_at: string;
}

export interface ProjectInvitation {
    id: number;
    project_id: number;
    invited_by: number;
    email: string;
    role: string;
    token: string;
    expires_at: string;
    accepted_at: string | null;
    created_at: string;
    project?: Project;
}

export type BreadcrumbItemType = BreadcrumbItem;
