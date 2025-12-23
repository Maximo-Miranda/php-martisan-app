<script setup lang="ts">
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { Project } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Calendar, Shield, Users } from 'lucide-vue-next';

const props = defineProps<{
    invitation: {
        token: string;
        email: string;
        role: string;
        project: Project;
        expires_at: string;
    };
    hasExistingAccount: boolean;
}>();

function acceptInvitation() {
    router.post(`/invitations/${props.invitation.token}/accept`);
}
</script>

<template>
    <Head :title="`Join ${invitation.project.name}`" />

    <div class="flex min-h-screen items-center justify-center bg-muted/50 p-4">
        <Card class="w-full max-w-md">
            <CardHeader class="text-center">
                <CardTitle class="text-2xl">You're invited!</CardTitle>
                <CardDescription>
                    You've been invited to join a project
                </CardDescription>
            </CardHeader>
            <CardContent class="space-y-6">
                <div class="rounded-lg bg-muted p-4 text-center">
                    <h3 class="text-lg font-semibold">
                        {{ invitation.project.name }}
                    </h3>
                    <p v-if="invitation.project.description" class="mt-1 text-sm text-muted-foreground">
                        {{ invitation.project.description }}
                    </p>
                </div>

                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-sm">
                        <Shield class="h-4 w-4 text-muted-foreground" />
                        <span>
                            You'll join as <strong>{{ invitation.role }}</strong>
                        </span>
                    </div>
                    <div class="flex items-center gap-3 text-sm">
                        <Calendar class="h-4 w-4 text-muted-foreground" />
                        <span>
                            Invitation expires on {{ new Date(invitation.expires_at).toLocaleDateString() }}
                        </span>
                    </div>
                </div>

                <div v-if="!hasExistingAccount" class="rounded-lg border border-amber-200 bg-amber-50 p-4 text-sm text-amber-800 dark:border-amber-800 dark:bg-amber-950 dark:text-amber-200">
                    <p>
                        You'll need to create an account with the email
                        <strong>{{ invitation.email }}</strong> to accept this invitation.
                    </p>
                </div>
            </CardContent>
            <CardFooter class="flex flex-col gap-3">
                <template v-if="hasExistingAccount">
                    <Button class="w-full" @click="acceptInvitation">
                        Accept & Join Project
                    </Button>
                    <Button variant="outline" class="w-full" as-child>
                        <Link href="/login">
                            Sign in to accept
                        </Link>
                    </Button>
                </template>
                <template v-else>
                    <Button class="w-full" as-child>
                        <Link :href="`/register?invitation=${invitation.token}`">
                            Create Account & Join
                        </Link>
                    </Button>
                    <p class="text-center text-sm text-muted-foreground">
                        Already have an account?
                        <Link href="/login" class="underline hover:text-foreground">
                            Sign in
                        </Link>
                    </p>
                </template>
            </CardFooter>
        </Card>
    </div>
</template>
