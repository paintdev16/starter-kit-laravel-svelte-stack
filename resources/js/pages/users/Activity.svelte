<script module lang="ts">
    import { index } from '@/routes/users';

    export const layout = {
        breadcrumbs: [
            { title: 'Usuarios', href: index() },
            { title: 'Actividad', href: '' },
        ],
    };
</script>

<script lang="ts">
    import AppHead from '@/components/AppHead.svelte';
    import ActivityManager from '@/components/users/activity/ActivityManager.svelte';

    let { filterUserId = null }: { filterUserId?: number | null } = $props();
</script>

<AppHead title="Actividad de usuarios" />

<ActivityManager {filterUserId} active={true}/>