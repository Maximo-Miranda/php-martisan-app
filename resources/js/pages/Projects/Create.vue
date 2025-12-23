<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import type { BreadcrumbItem } from '@/types';
import { Head, useForm } from '@inertiajs/vue3';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
    {
        title: 'Create',
        href: '/projects/create',
    },
];

const form = useForm({
    name: '',
    description: '',
});

function submit() {
    form.post('/projects');
}
</script>

<template>
    <Head title="Create Project" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 items-center justify-center">
            <Card class="w-full max-w-xl">
                <CardHeader>
                    <CardTitle>Project Details</CardTitle>
                    <CardDescription>
                        Enter the details for your new project
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
                                placeholder="My Awesome Project"
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
                            <Label for="description">Description (optional)</Label>
                            <Textarea
                                id="description"
                                v-model="form.description"
                                placeholder="A brief description of your project..."
                                rows="3"
                            />
                            <p
                                v-if="form.errors.description"
                                class="text-sm text-destructive"
                            >
                                {{ form.errors.description }}
                            </p>
                        </div>

                        <div class="flex gap-4">
                            <Button type="submit" :disabled="form.processing">
                                {{ form.processing ? 'Creating...' : 'Create Project' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                as-child
                            >
                                <a href="/projects">Cancel</a>
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
