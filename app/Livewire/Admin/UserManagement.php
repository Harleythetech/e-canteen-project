<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Illuminate\Validation\Rules\Password;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::admin')]
class UserManagement extends Component
{
    // Synced to the URL — filters users by role: 'student', 'staff', 'admin', or '' (all)
    #[Url]
    public string $role = '';

    // Live search input — filters users by name or email
    #[Url]
    public string $search = '';

    // ─── User Form State ─────────────────────────────────────────────────────
    public bool $showModal = false;
    public bool $showDeleteModal = false;
    public ?int $deletingUserId = null;
    public string $deletingUserName = '';
    public ?int $editingUserId = null; // null = creating new, int = editing existing

    public string $userName = '';
    public string $userEmail = '';
    public string $userRole = 'student';
    public string $userPassword = '';
    public bool $userActive = true;

    /**
     * Resets the page when the search input changes.
     * (Livewire pagination would be here if we were using it.)
     */
    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * Resets the page when the role filter changes.
     */
    public function updatedRole(): void
    {
        $this->resetPage();
    }

    /**
     * Placeholder for pagination reset — currently does nothing since we're not paginating.
     * Livewire will automatically re-render when properties change.
     */
    private function resetPage(): void
    {
        // Livewire will automatically re-render
    }

    /**
     * Opens the user modal in "create" mode with a blank form.
     */
    public function createUser(): void
    {
        $this->reset(['editingUserId', 'userName', 'userEmail', 'userRole', 'userPassword', 'userActive']);
        $this->userActive = true;
        $this->userRole = 'student';
        $this->showModal = true;
    }

    /**
     * Opens the user modal in "edit" mode with the existing user's data.
     * Prevents the admin from editing their own account (to avoid accidental lockout).
     */
    public function editUser(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot edit your own account here.');
            return;
        }

        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userActive = $user->is_active;
        $this->userPassword = ''; // Don't pre-fill password — admin can leave blank to keep existing
        $this->showModal = true;
    }

    /**
     * Saves the user (create or update depending on editingUserId).
     * Validates all fields including email DNS check and strong password requirements.
     * When editing, password is optional — only updates if a new one is provided.
     */
    public function saveUser(): void
    {
        $rules = [
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email:rfc,dns', 'max:255'],
            'userRole' => ['required', 'in:student,staff,admin'],
            'userActive' => ['boolean'],
        ];

        if ($this->editingUserId) {
            // Editing — ignore the current user's email in the unique check
            $rules['userEmail'][] = 'unique:users,email,' . $this->editingUserId;
            // Password is optional when editing — only validate if provided
            if ($this->userPassword) {
                $rules['userPassword'] = ['string', Password::defaults()];
            }
        } else {
            // Creating — email must be unique, password is required
            $rules['userEmail'][] = 'unique:users,email';
            $rules['userPassword'] = ['required', 'string', Password::defaults()];
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            // Update existing user
            $user = User::findOrFail($this->editingUserId);
            $user->name = $this->userName;
            $user->email = $this->userEmail;
            $user->role = $this->userRole;
            $user->is_active = $this->userActive;

            // Only update password if a new one was provided
            if ($this->userPassword) {
                $user->password = bcrypt($this->userPassword);
            }

            $user->save();
            $this->dispatch('toast', type: 'success', message: 'User updated successfully!');
        } else {
            // Create new user
            User::create([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'role' => $this->userRole,
                'is_active' => $this->userActive,
                'password' => bcrypt($this->userPassword),
            ]);
            $this->dispatch('toast', type: 'success', message: 'User created successfully!');
        }

        $this->showModal = false;
    }

    /**
     * Deletes a user after confirmation.
     * Prevents the admin from deleting their own account.
     */
    public function deleteUser(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete your own account.');
            return;
        }

        $user = User::findOrFail($id);
        $name = $user->name;
        $user->delete();

        $this->showDeleteModal = false;
        $this->deletingUserId = null;
        $this->deletingUserName = '';

        $this->dispatch('toast', type: 'success', message: "{$name} has been deleted.");
    }

    /**
     * Opens the delete confirmation modal.
     * Prevents the admin from deleting their own account.
     */
    public function confirmDeleteUser(int $id): void
    {
        if ($id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot delete your own account.');
            return;
        }

        $user = User::findOrFail($id);
        $this->deletingUserId = $user->id;
        $this->deletingUserName = $user->name;
        $this->showDeleteModal = true;
    }

    /**
     * Toggles the is_active flag for a user.
     * Prevents the admin from deactivating their own account (to avoid lockout).
     */
    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            $this->dispatch('toast', type: 'error', message: 'You cannot deactivate your own account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();
        $status = $user->is_active ? 'activated' : 'deactivated';
        $this->dispatch('toast', type: 'success', message: "{$user->name} has been {$status}.");
    }

    /**
     * Loads all users filtered by role and/or search term.
     * Also provides summary counts for the role filter tabs.
     */
    public function render()
    {
        $query = User::query()->orderBy('name');

        // Filter by role if selected
        if ($this->role) {
            $query->where('role', $this->role);
        }

        // Filter by search term (name or email)
        if ($this->search) {
            $query->where(function ($q) {
                $q->where('name', 'ilike', '%' . $this->search . '%')
                    ->orWhere('email', 'ilike', '%' . $this->search . '%');
            });
        }

        return view('livewire.admin.user-management', [
            'users' => $query->get(),
            'totalStudents' => User::where('role', 'student')->count(),
            'totalStaff' => User::where('role', 'staff')->count(),
            'totalAdmins' => User::where('role', 'admin')->count(),
        ]);
    }
}
