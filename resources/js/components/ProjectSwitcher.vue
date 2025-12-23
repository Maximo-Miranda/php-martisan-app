<script setup lang="ts">
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Button } from '@/components/ui/button';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { useSidebar } from '@/components/ui/sidebar';
import { getInitials } from '@/composables/useInitials';
import type { Project } from '@/types';
import { router, usePage } from '@inertiajs/vue3';
import { Check, ChevronsUpDown, Plus } from 'lucide-vue-next';
import { computed, ref } from 'vue';

const page = usePage();
const switching = ref(false);
const { state } = useSidebar();

const projects = computed(() => page.props.projects as Project[] ?? []);
const currentProject = computed(() => page.props.currentProject as Project | null);
const isCollapsed = computed(() => state.value === 'collapsed');

async function switchProject(projectId: number) {
    if (switching.value || currentProject.value?.id === projectId) return;

    switching.value = true;

    router.post('/projects/switch', { project_id: projectId }, {
        preserveScroll: false,
        preserveState: false,
        onFinish: () => {
            switching.value = false;
        },
    });
}
</script>

<template>
    <DropdownMenu v-if="projects.length > 0">
        <DropdownMenuTrigger as-child>
            <Button
                v-if="isCollapsed"
                variant="outline"
                size="icon"
                role="combobox"
                class="h-9 w-9"
                :disabled="switching"
            >
                <Avatar class="h-5 w-5">
                    <AvatarFallback class="text-[10px]">
                        {{ getInitials(currentProject?.name ?? 'P') }}
                    </AvatarFallback>
                </Avatar>
                <span class="sr-only">Switch project</span>
            </Button>
            <Button
                v-else
                variant="outline"
                role="combobox"
                class="w-full justify-between"
                :disabled="switching"
            >
                <div class="flex items-center gap-2 truncate">
                    <Avatar class="h-5 w-5">
                        <AvatarFallback class="text-[10px]">
                            {{ getInitials(currentProject?.name ?? 'P') }}
                        </AvatarFallback>
                    </Avatar>
                    <span class="truncate">
                        {{ currentProject?.name ?? 'Select project' }}
                    </span>
                </div>
                <ChevronsUpDown class="ml-2 h-4 w-4 shrink-0 opacity-50" />
            </Button>
        </DropdownMenuTrigger>
        <DropdownMenuContent class="w-[--radix-dropdown-menu-trigger-width] min-w-56" align="start">
            <DropdownMenuLabel>Projects</DropdownMenuLabel>
            <DropdownMenuSeparator />
            <DropdownMenuItem
                v-for="project in projects"
                :key="project.id"
                class="flex items-center gap-2"
                @click="switchProject(project.id)"
            >
                <Avatar class="h-5 w-5">
                    <AvatarFallback class="text-[10px]">
                        {{ getInitials(project.name) }}
                    </AvatarFallback>
                </Avatar>
                <span class="flex-1 truncate">{{ project.name }}</span>
                <Check
                    v-if="currentProject?.id === project.id"
                    class="h-4 w-4"
                />
            </DropdownMenuItem>
            <DropdownMenuSeparator />
            <DropdownMenuItem as-child>
                <a href="/projects/create" class="flex items-center gap-2">
                    <Plus class="h-4 w-4" />
                    <span>Create project</span>
                </a>
            </DropdownMenuItem>
        </DropdownMenuContent>
    </DropdownMenu>
    <Button v-else variant="outline" as-child :class="isCollapsed ? 'h-9 w-9' : 'w-full justify-start'">
        <a href="/projects/create" class="flex items-center gap-2">
            <Plus class="h-4 w-4" />
            <span v-if="!isCollapsed">Create project</span>
            <span v-else class="sr-only">Create project</span>
        </a>
    </Button>
</template>
