<flux:main>
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">User Management</flux:heading>
        <flux:button wire:click="createUser" variant="primary" icon="plus">Add User</flux:button>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle" class="mb-4">{{ session('error') }}</flux:callout>
    @endif

    {{-- Stats --}}
    <div class="mb-6 grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalStudents }}</p>
            <p class="text-xs text-zinc-500">Students</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalStaff }}</p>
            <p class="text-xs text-zinc-500">Staff</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-800">
            <p class="text-2xl font-bold text-zinc-900 dark:text-zinc-100">{{ $totalAdmins }}</p>
            <p class="text-xs text-zinc-500">Admins</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="mb-4 flex flex-wrap items-center gap-3">
        <div class="flex gap-2">
            @foreach (['' => 'All', 'student' => 'Students', 'staff' => 'Staff', 'admin' => 'Admins'] as $value => $label)
                <button wire:click="$set('role', '{{ $value }}')" @class([
                    'rounded-full px-3 py-1.5 text-xs font-medium transition',
                    'bg-orange-500 text-white' => $role === $value,
                    'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-700 dark:text-zinc-300' => $role !== $value,
                ])>{{ $label }}</button>
            @endforeach
        </div>
        <flux:input wire:model.live.debounce.300ms="search" placeholder="Search by name or email..."
            icon="magnifying-glass" class="max-w-xs" />
    </div>

    {{-- Table --}}
    <div class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-800">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700">
                        <th class="px-4 py-3 font-medium text-zinc-500">Name</th>
                        <th class="px-4 py-3 font-medium text-zinc-500">Email</th>
                        <th class="px-4 py-3 font-medium text-zinc-500">Role</th>
                        <th class="px-4 py-3 font-medium text-zinc-500">Status</th>
                        <th class="px-4 py-3 text-end font-medium text-zinc-500">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-100 dark:divide-zinc-700">
                    @forelse ($users as $user)
                        <tr>
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-zinc-600 dark:text-zinc-400">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                                <flux:badge
                                    :color="match($user->role) { 'admin' => 'purple', 'staff' => 'blue', default => 'zinc' }"
                                    size="sm">
                                    {{ ucfirst($user->role) }}
                                </flux:badge>
                            </td>
                            <td class="px-4 py-3">
                                <button wire:click="toggleActive({{ $user->id }})" class="cursor-pointer">
                                    <flux:badge :color="$user->is_active ? 'green' : 'red'" size="sm">
                                        {{ $user->is_active ? 'Active' : 'Inactive' }}
                                    </flux:badge>
                                </button>
                            </td>
                            <td class="px-4 py-3 text-end">
                                <flux:button wire:click="editUser({{ $user->id }})" size="sm" variant="ghost" icon="pencil"
                                    aria-label="Edit {{ $user->name }}" />
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-4 py-8 text-center text-zinc-500">No users found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Modal --}}
    <flux:modal wire:model="showModal" class="max-w-md md:min-w-md">
        <form wire:submit="saveUser" class="space-y-4">
            <flux:heading size="lg">{{ $editingUserId ? 'Edit User' : 'Add User' }}</flux:heading>

            <flux:input wire:model="userName" :label="__('Name')" required />
            <flux:input wire:model="userEmail" :label="__('Email')" type="email" required />

            <flux:select wire:model="userRole" :label="__('Role')">
                <flux:select.option value="student">Student</flux:select.option>
                <flux:select.option value="staff">Staff</flux:select.option>
                <flux:select.option value="admin">Admin</flux:select.option>
            </flux:select>

            <flux:input wire:model="userPassword"
                :label="$editingUserId ? __('New Password (leave blank to keep)') : __('Password')" type="password"
                :required="!$editingUserId" viewable />
            <p class="mt-1 text-xs text-zinc-500 dark:text-zinc-400">
                {{ __('Must be at least 8 characters and include uppercase, lowercase, a number, and a symbol.') }}
            </p>

            <flux:checkbox wire:model="userActive" :label="__('Active')" />

            <div class="flex justify-end gap-2">
                <flux:button wire:click="$set('showModal', false)" variant="ghost">Cancel</flux:button>
                <flux:button type="submit" variant="primary">{{ $editingUserId ? 'Update' : 'Create' }}</flux:button>
            </div>
        </form>
    </flux:modal>
</flux:main>