<template>
    <div>
        <Head title="Transactions" />

        <AppLayout :breadcrumbs="breadcrumbs">
            <div
                class="flex h-full flex-1 flex-col gap-4 overflow-x-auto rounded-xl p-4"
            >
                <div class="mb-2 flex items-center justify-between">
                    <div>
                        <h1 class="text-foreground text-2xl font-semibold">
                            Transactions
                        </h1>

                        <Dialog v-model:open="isDialogOpen">
                            <DialogTrigger as-child>
                                <Button class="mt-2" size="sm">
                                    New Transaction
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <form
                                    class="space-y-6"
                                    @submit.prevent="submit"
                                >
                                    <DialogHeader class="space-y-3">
                                        <DialogTitle>
                                            Send a Transaction
                                        </DialogTitle>
                                        <DialogDescription>
                                            Enter the receiver's user ID and the
                                            amount you wish to send.
                                        </DialogDescription>
                                    </DialogHeader>

                                    <div class="grid gap-2">
                                        <Label for="userId">
                                            Receiver User ID
                                        </Label>
                                        <Input
                                            id="userId"
                                            v-model="form.receiver_id"
                                            type="text"
                                            name="userId"
                                            placeholder="Enter user ID"
                                            :error="form.errors.receiver_id"
                                            :disabled="form.processing"
                                        />

                                        <InputError
                                            :message="form.errors.receiver_id"
                                        />
                                    </div>

                                    <div class="grid gap-2">
                                        <Label for="amount">Amount ($)</Label>
                                        <Input
                                            id="amount"
                                            ref="amountInput"
                                            v-model="form.amount"
                                            type="number"
                                            name="amount"
                                            placeholder="0.00"
                                            step="0.01"
                                            min="0"
                                            :error="form.errors.amount"
                                            :disabled="form.processing"
                                        />

                                        <InputError
                                            :message="form.errors.amount"
                                        />
                                    </div>

                                    <DialogFooter class="gap-2">
                                        <DialogClose as-child>
                                            <Button
                                                type="button"
                                                variant="ghost"
                                                @click="form.reset()"
                                            >
                                                Cancel
                                            </Button>
                                        </DialogClose>

                                        <Button
                                            type="submit"
                                            :disabled="form.processing"
                                            data-test="confirm-send-transaction-button"
                                            :loading="form.processing"
                                        >
                                            Send Transaction
                                        </Button>
                                    </DialogFooter>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>

                    <div
                        v-if="echoState === 'connected'"
                        class="flex items-center gap-2 text-sm text-green-500"
                        title="Live connection active"
                    >
                        <span class="relative flex h-3 w-3">
                            <span
                                class="absolute inline-flex h-full w-full animate-ping rounded-full bg-green-400 opacity-75"
                            ></span>
                            <span
                                class="relative inline-flex h-3 w-3 rounded-full bg-green-500"
                            ></span>
                        </span>
                        <span>Live</span>
                    </div>

                    <div
                        v-else-if="echoState === 'connecting'"
                        class="flex items-center gap-2 text-sm text-yellow-500"
                    >
                        <span class="relative flex h-3 w-3">
                            <span
                                class="inline-flex h-3 w-3 animate-pulse rounded-full bg-yellow-500"
                            ></span>
                        </span>
                        <span>Connecting…</span>
                    </div>

                    <div
                        v-else
                        class="flex items-center gap-2 text-sm text-red-500"
                        title="Connection lost. Data may be outdated."
                    >
                        <span class="relative flex h-3 w-3">
                            <span
                                class="relative inline-flex h-3 w-3 rounded-full bg-red-500"
                            ></span>
                        </span>
                        <span>Offline</span>
                    </div>
                </div>

                <div class="grid auto-rows-min gap-4 md:grid-cols-3">
                    <div
                        class="border-sidebar-border/70 dark:border-sidebar-border relative flex flex-col overflow-hidden rounded-xl border p-4"
                    >
                        <div class="flex min-h-6 items-center justify-between">
                            <span class="text-muted-foreground text-sm">
                                Current Balance
                            </span>
                            <Wallet
                                class="text-muted-foreground size-6 md:hidden lg:block"
                                aria-hidden="true"
                            />
                        </div>
                        <div class="text-3xl font-bold tracking-tight">
                            <NumberFlow
                                :value="Number(userBalance)"
                                :format="{ style: 'currency', currency: 'USD' }"
                                :plugins="[continuous]"
                                :transform-timing="{ duration: 750 }"
                                :spin-timing="{
                                    duration: 900,
                                    easing: 'linear(0, 0.01, 0.04, 0.1, 0.18, 0.28, 0.4, 0.55, 0.73, 0.93, 1, 1.08, 1.11, 1.12, 1.11, 1.08, 1, 0.98, 0.99, 1)',
                                }"
                                :opacity-timing="{
                                    duration: 350,
                                    easing: 'ease-out',
                                }"
                            />
                        </div>
                    </div>

                    <div
                        class="border-sidebar-border/70 dark:border-sidebar-border relative flex flex-col overflow-hidden rounded-xl border p-4"
                    >
                        <div class="flex min-h-6 items-start justify-between">
                            <span class="text-muted-foreground text-sm">
                                All-Time Cash Flow
                            </span>

                            <div
                                class="flex items-center justify-center md:hidden lg:flex"
                                :class="[
                                    'rounded-md px-2 py-1 font-medium',
                                    totalNet >= 0
                                        ? 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300'
                                        : 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                ]"
                                title="Net cash flow (all-time)"
                            >
                                <NumberFlow
                                    :value="totalNet"
                                    :format="{
                                        style: 'currency',
                                        currency: 'USD',
                                        signDisplay: 'always',
                                    }"
                                    :plugins="[continuous]"
                                    class="text-xs"
                                />
                            </div>
                        </div>

                        <div class="flex flex-wrap items-baseline gap-4">
                            <div class="text-3xl font-bold tracking-tight">
                                <NumberFlow
                                    :value="totalCashFlowVolume"
                                    :format="{
                                        style: 'currency',
                                        currency: 'USD',
                                    }"
                                    :plugins="[continuous]"
                                />
                            </div>
                            <div class="flex flex-wrap items-center gap-3">
                                <div
                                    class="flex items-center gap-1.5 text-green-500"
                                    title="Total Incoming (all-time)"
                                >
                                    <ArrowDownCircle class="h-5 w-5" />
                                    <span
                                        class="text-sm font-semibold tracking-tight md:text-lg"
                                    >
                                        <NumberFlow
                                            :value="totalIncoming"
                                            :format="{
                                                style: 'currency',
                                                currency: 'USD',
                                            }"
                                            :plugins="[continuous]"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-red-500"
                                    title="Total Outgoing (all-time)"
                                >
                                    <ArrowUpCircle class="h-5 w-5" />
                                    <span
                                        class="text-sm font-semibold tracking-tight md:text-lg"
                                    >
                                        <NumberFlow
                                            :value="totalOutgoing"
                                            :format="{
                                                style: 'currency',
                                                currency: 'USD',
                                            }"
                                            :plugins="[continuous]"
                                        />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div
                        class="border-sidebar-border/70 dark:border-sidebar-border relative flex flex-col overflow-hidden rounded-xl border p-4"
                    >
                        <div class="flex min-h-6 items-center justify-between">
                            <span class="text-muted-foreground text-sm">
                                Total Transactions
                            </span>
                            <ArrowLeftRight
                                class="text-muted-foreground size-6 md:hidden lg:block"
                                aria-hidden="true"
                            />
                        </div>
                        <div class="flex items-baseline gap-4">
                            <div class="text-3xl font-bold tracking-tight">
                                <NumberFlow
                                    :value="totalTransactionCount"
                                    :format="{ minimumFractionDigits: 0 }"
                                    :plugins="[continuous]"
                                />
                            </div>
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex items-center gap-1.5 text-green-500"
                                    title="Total Incoming Transactions"
                                >
                                    <ArrowDownCircle class="h-5 w-5" />
                                    <span
                                        class="text-sm font-semibold tracking-tight md:text-lg"
                                    >
                                        <NumberFlow
                                            :value="incomingCount"
                                            :format="{
                                                minimumFractionDigits: 0,
                                            }"
                                            :plugins="[continuous]"
                                        />
                                    </span>
                                </div>
                                <div
                                    class="flex items-center gap-1.5 text-red-500"
                                    title="Total Outgoing Transactions"
                                >
                                    <ArrowUpCircle class="h-5 w-5" />
                                    <span
                                        class="text-sm font-semibold tracking-tight md:text-lg"
                                    >
                                        <NumberFlow
                                            :value="outgoingCount"
                                            :format="{
                                                minimumFractionDigits: 0,
                                            }"
                                            :plugins="[continuous]"
                                        />
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div
                    class="border-sidebar-border/70 dark:border-sidebar-border relative flex-1 overflow-x-auto rounded-xl border md:min-h-min"
                >
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>
                                    Nr.
                                </TableHead>
                                <TableHead>
                                    Date
                                </TableHead>
                                <TableHead>
                                    Sender
                                </TableHead>
                                <TableHead>
                                    Receiver
                                </TableHead>
                                <TableHead class="text-right">
                                    Amount
                                </TableHead>
                                <TableHead class="text-right">
                                    Fee
                                </TableHead>
                            </TableRow>
                        </TableHeader>

                        <TableBody>
                            <template v-if="transactions.length">
                                <TableRow
                                    v-for="tx in transactions"
                                    :key="tx.id"
                                    class="hover:bg-muted/50"
                                >
                                    <TableCell>
                                        {{ tx.id }}
                                    </TableCell>
                                    <TableCell>
                                        {{
                                            new Date(
                                                tx.transaction_date,
                                            ).toLocaleString()
                                        }}
                                    </TableCell>
                                    <TableCell>
                                        {{ tx.sender?.name }}
                                        <span
                                            v-if="
                                                tx.sender?.id === currentUser.id
                                            "
                                            class="text-xs text-blue-500"
                                        >
                                            (You)
                                        </span>
                                    </TableCell>
                                    <TableCell>
                                        {{ tx.receiver?.name }}
                                        <span
                                            v-if="
                                                tx.receiver?.id ===
                                                currentUser.id
                                            "
                                            class="text-xs text-green-500"
                                        >
                                            (You)
                                        </span>
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{ Number(tx.amount).toFixed(2) }}
                                    </TableCell>
                                    <TableCell class="text-right">
                                        {{
                                            Number(tx.commission_fee).toFixed(2)
                                        }}
                                    </TableCell>
                                </TableRow>
                            </template>

                            <TableRow v-else>
                                <TableCell colspan="5" class="h-24 text-center">
                                    No results.
                                </TableCell>
                            </TableRow>
                        </TableBody>
                    </Table>
                </div>
            </div>
        </AppLayout>
    </div>
</template>

<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { echo, useEcho } from '@laravel/echo-vue';
import NumberFlow, { continuous } from '@number-flow/vue';
import {
    ArrowDownCircle,
    ArrowLeftRight,
    ArrowUpCircle,
    Wallet,
} from 'lucide-vue-next';
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';

import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button/index.js';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog/index.js';
import { Input } from '@/components/ui/input/index.js';
import { Label } from '@/components/ui/label/index.js';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table/index.js';
import AppLayout from '@/layouts/AppLayout.vue';
import { store as storeTransaction } from '@/routes/transactions/index.js';
import { index } from '@/routes/transactions/index.ts';
import { toast } from 'vue-sonner';

const props = defineProps({
    history: {
        type: Object,
        required: true,
    },
    stats: {
        type: Object,
        required: true,
    },
});

const page = usePage();
const currentUser = page.props.auth.user;

const userBalance = ref(currentUser.balance);
const transactions = ref(props.history?.data ? [...props.history.data] : []);
const totalIncoming = ref(props.stats.incoming);
const totalOutgoing = ref(props.stats.outgoing);
const incomingCount = ref(props.stats.incoming_count);
const outgoingCount = ref(props.stats.outgoing_count);
const echoState = ref('connecting');

const totalTransactionCount = computed(
    () => incomingCount.value + outgoingCount.value,
);
const totalNet = computed(() => totalIncoming.value - totalOutgoing.value);
const totalCashFlowVolume = computed(
    () => totalIncoming.value + totalOutgoing.value,
);

const breadcrumbs = [
    {
        title: 'Transactions',
        href: index().url,
    },
];

const amountInput = ref(null);

const form = useForm({
    receiver_id: '',
    amount: null,
});

const isDialogOpen = ref(false);

const submit = () => {
    form.post(storeTransaction().url, {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();

            isDialogOpen.value = false;

            toast('Success', {
                type: 'success',
                description: usePage().props.flash.success,
            });
        },
        onError: () => {
            toast('Error', {
                type: 'error',
                description: usePage().props.flash.error,
            });
        },
    });
};

const handleIncomingTransaction = (e) => {
    const tx = e.transaction;
    if (!tx) return;

    transactions.value.unshift(tx);
    if (typeof e.balance !== 'undefined') {
        userBalance.value = e.balance;
    }

    const amount = Number(tx.amount);

    if (tx.sender.id === currentUser.id) {
        totalOutgoing.value += amount;
        outgoingCount.value += 1;
    } else if (tx.receiver.id === currentUser.id) {
        totalIncoming.value += amount;
        incomingCount.value += 1;
    }
};

useEcho(`user.${currentUser.id}`, '.transaction.completed', (e) => {
    handleIncomingTransaction(e);
});

onMounted(() => {
    const echoInstance = echo();

    if (echoInstance.connector && echoInstance.connector.pusher) {
        const pusher = echoInstance.connector.pusher;

        echoState.value = pusher.connection.state;

        const handleStateChange = (states) => {
            echoState.value = states.current;
        };

        pusher.connection.bind('state_change', handleStateChange);
        pusher.connection.bind('unavailable', () => {
            echoState.value = 'unavailable';
        });

        onBeforeUnmount(() => {
            pusher.connection.unbind('state_change', handleStateChange);
            pusher.connection.unbind('unavailable');
        });
    }
});
</script>
