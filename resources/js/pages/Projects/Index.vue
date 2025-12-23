<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import type { BreadcrumbItem, Project } from '@/types';
import { Head, Link, router } from '@inertiajs/vue3';
import { Plus, Users, ChevronLeft, ChevronRight } from 'lucide-vue-next';

interface PaginatedProjects {
    data: Project[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
}

defineProps<{
    projects: PaginatedProjects;
}>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Projects',
        href: '/projects',
    },
];

function goToPage(page: number) {
    router.get(`/projects?page=${page}`, {}, {
        preserveScroll: true,
        preserveState: true,
    });
}
</script>

<template>
    <Head title="Projects" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Projects</h1>
                    <p class="text-muted-foreground">
                        Manage your projects and team members
                    </p>
                </div>
                <Button as-child>
                    <Link href="/projects/create">
                        <Plus class="mr-2 h-4 w-4" />
                        New Project
                    </Link>
                </Button>
            </div>

            <div
                v-if="projects.data.length === 0"
                class="flex flex-1 items-center justify-center rounded-lg border border-dashed"
            >
                <div class="flex flex-col items-center gap-2 text-center">
                    <h3 class="text-lg font-semibold">No projects yet</h3>
                    <p class="text-muted-foreground">
                        Create your first project to get started.
                    </p>
                    <Button as-child class="mt-4">
                        <Link href="/projects/create">
                            <Plus class="mr-2 h-4 w-4" />
                            Create Project
                        </Link>
                    </Button>
                </div>
            </div>

            <template v-else>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <Card
                        v-for="project in projects.data"
                        :key="project.id"
                        class="hover:border-primary/50 transition-colors"
                    >
                        <CardHeader>
                            <div class="flex items-start justify-between gap-2">
                                <CardTitle class="flex-1">
                                    <Link
                                        :href="`/projects/${project.id}`"
                                        class="hover:underline"
                                    >
                                        {{ project.name }}
                                    </Link>
                                </CardTitle>
                                <Badge v-if="project.is_owner" variant="default" class="shrink-0">
                                    Owner
                                </Badge>
                                <Badge v-else variant="secondary" class="shrink-0">
                                    Member
                                </Badge>
                            </div>
                            <CardDescription v-if="project.description">
                                {{ project.description }}
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex items-center gap-4 text-sm text-muted-foreground">
                                <div class="flex items-center gap-1">
                                    <Users class="h-4 w-4" />
                                    <span>{{ project.members_count }} members</span>
                                </div>
                                <span v-if="project.owner && !project.is_owner" class="truncate">
                                    Owner: {{ project.owner.name }}
                                </span>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Pagination -->
                <div v-if="projects.last_page > 1" class="flex items-center justify-center gap-2 mt-4">
                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="projects.current_page === 1"
                        @click="goToPage(projects.current_page - 1)"
                    >
                        <ChevronLeft class="h-4 w-4 mr-1" />
                        Previous
                    </Button>

                    <span class="text-sm text-muted-foreground">
                        Page {{ projects.current_page }} of {{ projects.last_page }}
                    </span>

                    <Button
                        variant="outline"
                        size="sm"
                        :disabled="projects.current_page === projects.last_page"
                        @click="goToPage(projects.current_page + 1)"
                    >
                        Next
                        <ChevronRight class="h-4 w-4 ml-1" />
                    </Button>
                </div>
            </template>
        </div>
    </AppLayout>
</template>
