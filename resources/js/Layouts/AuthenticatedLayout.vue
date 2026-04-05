<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link, router, usePage } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false)
const showInvitations = ref(false)
const page = usePage()

const currentBusinessId = () => page.props.auth.current_business_id
const isAdmin = () => page.props.auth.is_admin

// Resolve the correct home URL based on role
const homeUrl = () => isAdmin()
    ? route('admin.dashboard')
    : currentBusinessId()
        ? route('dashboard', { business: currentBusinessId() })
        : route('profile.edit')

// Switching business = navigating to that business's dashboard URL
const switchBusiness = (id) => {
    router.visit(route('dashboard', { business: id }))
}

const pendingInvitations = () => page.props.auth.pending_invitations ?? []

function acceptInvitation(token) {
    router.post(route('invitation.accept', token), {}, {
        onSuccess: () => { showInvitations.value = false },
    })
}

function declineInvitation(token) {
    router.delete(route('invitation.decline', token), {}, {
        onSuccess: () => {
            if (!pendingInvitations().length) showInvitations.value = false
        },
    })
}

</script>

<template>
    <div>
        <div class="min-h-screen bg-gray-100">
            <nav
                class="border-b border-gray-100 bg-white"
            >
                <!-- Primary Navigation Menu -->
                <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                    <div class="flex h-16 justify-between">
                        <div class="flex">
                            <!-- Logo -->
                            <div class="flex shrink-0 items-center">
                                <Link :href="homeUrl()">
                                    <ApplicationLogo
                                        class="block h-9 w-auto fill-current text-gray-800"
                                    />
                                </Link>
                            </div>

                            <!-- Navigation Links -->
                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >

                                <select
                                    v-if="page.props.auth.businesses.length"
                                    :value="page.props.auth.current_business_id"
                                    @change="switchBusiness($event.target.value)"
                                    class="border px-2 py-1 rounded mr-4"
                                >
                                    <option disabled value="">Select Business</option>
                                    <option v-for="b in page.props.auth.businesses" :key="b.id" :value="b.id">
                                        {{ b.name }}
                                    </option>
                                </select>

                                <!-- Admin nav -->
                                <template v-if="isAdmin()">
                                    <NavLink
                                        :href="route('admin.dashboard')"
                                        :active="route().current('admin.dashboard')"
                                    >Dashboard</NavLink>
                                </template>

                                <!-- Business nav -->
                                <template v-else-if="currentBusinessId()">
                                    <NavLink
                                        :href="route('dashboard', { business: currentBusinessId() })"
                                        :active="route().current('dashboard')"
                                    >Dashboard</NavLink>

                                    <NavLink
                                        :href="route('team.index', { business: currentBusinessId() })"
                                        :active="route().current('team.index')"
                                    >Team</NavLink>

                                    <NavLink
                                        :href="route('roles.index', { business: currentBusinessId() })"
                                        :active="route().current('roles.index')"
                                    >Roles</NavLink>
                                </template>
                            </div>
                        </div>

                        <div class="hidden sm:ms-6 sm:flex sm:items-center gap-2">

                            <!-- Invitation bell -->
                            <div v-if="!isAdmin()" class="relative">
                                <button
                                    @click="showInvitations = !showInvitations"
                                    class="relative p-2 rounded-full text-gray-500 hover:text-gray-700 hover:bg-gray-100 transition"
                                >
                                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6 6 0 10-12 0v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                    </svg>
                                    <span
                                        v-if="pendingInvitations().length"
                                        class="absolute top-1 right-1 h-2 w-2 rounded-full bg-red-500"
                                    ></span>
                                </button>

                                <!-- Dropdown panel -->
                                <div
                                    v-if="showInvitations"
                                    class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-200 z-50 overflow-hidden"
                                >
                                    <div class="px-4 py-3 border-b border-gray-100">
                                        <p class="text-sm font-semibold text-gray-700">Pending Invitations</p>
                                    </div>

                                    <div v-if="pendingInvitations().length" class="divide-y divide-gray-50">
                                        <div
                                            v-for="inv in pendingInvitations()"
                                            :key="inv.token"
                                            class="px-4 py-3"
                                        >
                                            <p class="text-sm font-medium text-gray-900">{{ inv.business_name }}</p>
                                            <p class="text-xs text-gray-500 mt-0.5 capitalize">Role: {{ inv.role_name }}</p>
                                            <div class="flex gap-2 mt-2">
                                                <button
                                                    @click="acceptInvitation(inv.token)"
                                                    class="rounded-lg bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700 transition"
                                                >Accept</button>
                                                <button
                                                    @click="declineInvitation(inv.token)"
                                                    class="rounded-lg border border-gray-300 px-3 py-1 text-xs font-medium text-gray-600 hover:bg-gray-50 transition"
                                                >Decline</button>
                                            </div>
                                        </div>
                                    </div>

                                    <p v-else class="px-4 py-4 text-sm text-gray-400 text-center">
                                        No pending invitations
                                    </p>
                                </div>
                            </div>

                            <!-- Settings Dropdown -->
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-md border border-transparent bg-white px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out hover:text-gray-700 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <!-- Hamburger -->
                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 transition duration-150 ease-in-out hover:bg-gray-100 hover:text-gray-500 focus:bg-gray-100 focus:text-gray-500 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Responsive Navigation Menu -->
                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="homeUrl()"
                            :active="isAdmin() ? route().current('admin.dashboard') : route().current('dashboard')"
                        >
                            Dashboard
                        </ResponsiveNavLink>
                    </div>

                    <!-- Responsive Settings Options -->
                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Page Heading -->
            <header
                class="bg-white shadow"
                v-if="$slots.header"
            >
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page Content -->
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
