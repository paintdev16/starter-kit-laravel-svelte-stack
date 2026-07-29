<script lang="ts">
    import { onMount } from 'svelte';
    import { toast } from 'svelte-sonner';
    import Echo from '@/echo';

    onMount(() => {
        if (!Echo) {
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
            Echo?.leaveChannel('admin.users');
        };
    });
</script>
