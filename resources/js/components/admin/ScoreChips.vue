<script setup lang="ts">
import type { AssessmentResult } from '@/types';

defineProps<{ result: AssessmentResult }>();
</script>

<template>
    <ul class="flex flex-wrap gap-1.5" aria-label="Skor per bidang">
        <li
            v-for="s in result.scores"
            :key="s.slug"
            class="rounded-full px-2.5 py-0.5 text-xs font-medium"
            :class="
                s.slug === result.recommended_category.slug
                    ? 'bg-accent-100 text-accent-700'
                    : 'bg-slate-100 text-slate-600'
            "
        >
            {{ s.title }}: {{ s.score }}
        </li>
    </ul>
</template>
