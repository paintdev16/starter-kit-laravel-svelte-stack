<script lang="ts">
    import { page } from '@inertiajs/svelte';
    import { onMount } from 'svelte';
    import { toast } from 'svelte-sonner';
    import Echo from '@/echo';

    const canListen = $derived.by(() => {
        const roles = page.props.auth?.roles;
        const realtimeEnabled = page.props.realtime?.enabled;

        if (!Array.isArray(roles) || !realtimeEnabled) {
            return false;
        }

        return roles.includes('root') || roles.includes('super-admin');
    });

    onMount(() => {
        if (!Echo || !canListen) {
            return;
        }

        const channel = Echo.private('admin.users');

        channel.listen(
            '.user.created',
            (e: { name: string; email: string }) => {
                toast.success(`Nuevo usuario: ${e.name}`, {
                    description: e.email,
                    duration: 5000,
                });
            },
        );

        return () => {
            channel.stopListening('.user.created');
            Echo?.leave('admin.users');
        };
    });
</script>
