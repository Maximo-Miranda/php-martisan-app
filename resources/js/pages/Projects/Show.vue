<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    SelectRoot,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import type { BreadcrumbItem, Project, ProjectInvitation, ProjectMember, User } from '@/types';
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import { Crown, Mail, Trash2, UserPlus, Users } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const props = defineProps<{
    project: Project & {
        owner: User;
        invitations: ProjectInvitation[];
    };
    members: ProjectMember[];
}>();

const page = usePage();
const auth = computed(() => page.props.auth as { user: User });

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
];

const inviteDialogOpen = ref(false);

const inviteForm = useForm({
    email: '',
    role: 'Project Viewer',
});

const isOwner = computed(() => auth.value.user.id === props.project.owner_id);

function sendInvitation() {
    inviteForm.post(`/projects/${props.project.id}/invitations`, {
        preserveScroll: true,
        onSuccess: () => {
            inviteForm.reset();
            inviteDialogOpen.value = false;
        },
    });
}

function cancelInvitation(invitation: ProjectInvitation) {
    if (confirm('Are you sure you want to cancel this invitation?')) {
        router.delete(`/projects/${props.project.id}/invitations/${invitation.id}`, {
            preserveScroll: true,
        });
    }
}

function resendInvitation(invitation: ProjectInvitation) {
    router.post(`/projects/${props.project.id}/invitations/${invitation.id}/resend`, {}, {
        preserveScroll: true,
    });
}

function formatRoleName(role: string): string {
    return role.replace('Project ', '');
}

function getRoleBadgeVariant(role: string): 'default' | 'secondary' | 'outline' | 'destructive' {
    const variants: Record<string, 'default' | 'secondary' | 'outline'> = {
        'Project Admin': 'default',
        'Project Editor': 'secondary',
        'Project Viewer': 'outline',
    };
    return variants[role] ?? 'secondary';
}
</script>

<template>
    <Head :title="project.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4 md:p-6 lg:p-8">
            <div class="w-full max-w-5xl mx-auto space-y-6">
                <!-- Project Header -->
                <div class="flex items-start justify-between">
                    <div>
                        <h1 class="text-3xl font-bold tracking-tight">{{ project.name }}</h1>
                        <p v-if="project.description" class="mt-2 text-muted-foreground">
                            {{ project.description }}
                        </p>
                    </div>
                </div>

                <!-- Members Card -->
                <Card>
                    <CardHeader>
                        <div class="flex items-center justify-between">
                            <div>
                                <CardTitle class="flex items-center gap-2">
                                    <Users class="h-5 w-5" />
                                    Members
                                </CardTitle>
                                <CardDescription>
                                    {{ members.length }} team member{{ members.length === 1 ? '' : 's' }}
                                </CardDescription>
                            </div>
                            <Dialog v-model:open="inviteDialogOpen">
                                <DialogTrigger as-child>
                                    <Button v-if="isOwner" size="sm">
                                        <UserPlus class="mr-2 h-4 w-4" />
                                        Invite
                                    </Button>
                                </DialogTrigger>
                                <DialogContent>
                                    <DialogHeader>
                                        <DialogTitle>Invite team member</DialogTitle>
                                        <DialogDescription>
                                            Send an invitation email to add a new member to this project.
                                        </DialogDescription>
                                    </DialogHeader>
                                    <form @submit.prevent="sendInvitation" class="space-y-4 flex flex-col items-center justify-center">
                                        <div class="space-y-2 w-full">
                                            <Label for="email">Email address</Label>
                                            <Input
                                                id="email"
                                                v-model="inviteForm.email"
                                                type="email"
                                                placeholder="colleague@company.com"
                                                required
                                            />
                                            <p
                                                v-if="inviteForm.errors.email"
                                                class="text-sm text-destructive"
                                            >
                                                {{ inviteForm.errors.email }}
                                            </p>
                                        </div>
                                        <div class="space-y-2 w-full">
                                            <Label for="role">Role</Label>
                                            <SelectRoot v-model="inviteForm.role">
                                                <SelectTrigger>
                                                    <SelectValue placeholder="Select a role" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    <SelectItem value="Project Admin">
                                                        Project Admin
                                                    </SelectItem>
                                                    <SelectItem value="Project Editor">
                                                        Project Editor
                                                    </SelectItem>
                                                    <SelectItem value="Project Viewer">
                                                        Project Viewer
                                                    </SelectItem>
                                                </SelectContent>
                                            </SelectRoot>
                                        </div>
                                        <DialogFooter class="w-full flex justify-center">
                                            <Button
                                                type="submit"
                                                :disabled="inviteForm.processing"
                                            >
                                                {{ inviteForm.processing ? 'Sending...' : 'Send Invitation' }}
                                            </Button>
                                        </DialogFooter>
                                    </form>
                                </DialogContent>
                            </Dialog>
                        </div>
                    </CardHeader>
                    <CardContent class="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="pl-6">Member</TableHead>
                                    <TableHead class="pr-6 text-right">Role</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="member in members" :key="member.id">
                                    <TableCell class="pl-6 py-4">
                                        <div class="flex items-center gap-3">
                                            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-primary/10 text-primary font-medium">
                                                {{ member.name.charAt(0).toUpperCase() }}
                                            </div>
                                            <div>
                                                <div class="flex items-center gap-2">
                                                    <p class="font-medium text-base">{{ member.name }}</p>
                                                    <Crown v-if="member.is_owner" class="h-4 w-4 text-amber-500" title="Project Owner" />
                                                </div>
                                                <p class="text-sm text-muted-foreground">
                                                    {{ member.email }}
                                                </p>
                                            </div>
                                        </div>
                                    </TableCell>
                                    <TableCell class="pr-6 py-4 text-right">
                                        <Badge v-if="member.is_owner" variant="default" class="bg-amber-500 hover:bg-amber-600">
                                            Owner
                                        </Badge>
                                        <Badge v-else :variant="getRoleBadgeVariant(member.role)">
                                            {{ formatRoleName(member.role) }}
                                        </Badge>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="members.length === 0">
                                    <TableCell colspan="2" class="h-24 text-center text-muted-foreground">
                                        No members yet. Invite someone to get started.
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <!-- Pending Invitations Section -->
                <div v-if="isOwner && project.invitations.length > 0" class="space-y-4">
                    <div>
                        <h3 class="flex items-center gap-2 text-lg font-semibold">
                            <Mail class="h-5 w-5" />
                            Pending Invitations
                        </h3>
                        <p class="text-sm text-muted-foreground">
                            {{ project.invitations.length }} pending invitation{{ project.invitations.length === 1 ? '' : 's' }}
                        </p>
                    </div>

                    <div class="space-y-3">
                        <Card
                            v-for="invitation in project.invitations"
                            :key="invitation.id"
                            class="hover:border-primary/50 transition-colors"
                        >
                            <CardContent class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 p-5">
                                <div class="flex flex-col gap-1.5 flex-1">
                                    <p class="font-medium text-base">{{ invitation.email }}</p>
                                    <div class="flex items-center gap-3">
                                        <Badge variant="outline" class="text-xs">
                                            {{ invitation.role }}
                                        </Badge>
                                        <span class="text-xs text-muted-foreground">
                                            Invited {{ new Date(invitation.created_at).toLocaleDateString() }}
                                        </span>
                                    </div>
                                </div>
                                <div class="flex gap-2 sm:gap-1">
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Resend invitation"
                                        @click="resendInvitation(invitation)"
                                    >
                                        <Mail class="h-4 w-4" />
                                    </Button>
                                    <Button
                                        variant="ghost"
                                        size="icon"
                                        title="Cancel invitation"
                                        @click="cancelInvitation(invitation)"
                                    >
                                        <Trash2 class="h-4 w-4 text-destructive" />
                                    </Button>
                                </div>
                            </CardContent>
                        </Card>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
