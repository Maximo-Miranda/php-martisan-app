<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardFooter,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem, Project } from '@/types';
import { Head, router, useForm } from '@inertiajs/vue3';

const props = defineProps<{
    project: Project;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: props.project.name,
        href: `/projects/${props.project.id}`,
    },
    {
        title: 'Edit',
        href: `/projects/${props.project.id}/edit`,
    },
];

const form = useForm({
    name: props.project.name,
    description: props.project.description ?? '',
});

function submit() {
    form.put(`/projects/${props.project.id}`);
}

function deleteProject() {
    if (confirm('Are you sure you want to delete this project? This action cannot be undone.')) {
        router.delete(`/projects/${props.project.id}`);
    }
}
</script>

<template>
    <Head :title="`Edit ${project.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-4">
            <div>
                <h1 class="text-2xl font-bold tracking-tight">Project Settings</h1>
                <p class="text-muted-foreground">
                    Manage your project settings and preferences
                </p>
            </div>

            <div class="grid gap-6 max-w-xl">
                <!-- General Settings -->
                <Card>
                    <CardHeader>
                        <CardTitle>General</CardTitle>
                        <CardDescription>
                            Update your project's basic information
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form @submit.prevent="submit" class="space-y-4">
                            <div class="space-y-2">
                                <Label for="name">Project Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    required
                                    :class="{ 'border-destructive': form.errors.name }"
                                />
                                <p
                                    v-if="form.errors.name"
                                    class="text-sm text-destructive"
                                >
                                    {{ form.errors.name }}
                                </p>
                            </div>

                            <div class="space-y-2">
                                <Label for="description">Description</Label>
                                <Textarea
                                    id="description"
                                    v-model="form.description"
                                    rows="3"
                                />
                            </div>

                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Saving...' : 'Save Changes' }}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <!-- Danger Zone -->
                <Card class="border-destructive">
                    <CardHeader>
                        <CardTitle class="text-destructive">Danger Zone</CardTitle>
                        <CardDescription>
                            Irreversible and destructive actions
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <p class="text-sm text-muted-foreground">
                            Once you delete a project, there is no going back. Please be certain.
                        </p>
                    </CardContent>
                    <CardFooter>
                        <Button variant="destructive" @click="deleteProject">
                            Delete Project
                        </Button>
                    </CardFooter>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
