<?php

namespace App\Livewire\Admin;

use App\Models\User;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('layouts::admin')]
class UserManagement extends Component
{
    #[Url]
    public string $role = '';

    #[Url]
    public string $search = '';

    public bool $showModal = false;
    public ?int $editingUserId = null;
    public string $userName = '';
    public string $userEmail = '';
    public string $userRole = 'student';
    public string $userPassword = '';
    public bool $userActive = true;

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedRole(): void
    {
        $this->resetPage();
    }

    private function resetPage(): void
    {
        // Livewire will automatically re-render
    }

    public function createUser(): void
    {
        $this->reset(['editingUserId', 'userName', 'userEmail', 'userRole', 'userPassword', 'userActive']);
        $this->userActive = true;
        $this->userRole = 'student';
        $this->showModal = true;
    }

    public function editUser(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingUserId = $user->id;
        $this->userName = $user->name;
        $this->userEmail = $user->email;
        $this->userRole = $user->role;
        $this->userActive = $user->is_active;
        $this->userPassword = '';
        $this->showModal = true;
    }

    public function saveUser(): void
    {
        $rules = [
            'userName' => ['required', 'string', 'max:255'],
            'userEmail' => ['required', 'email', 'max:255'],
            'userRole' => ['required', 'in:student,staff,admin'],
            'userActive' => ['boolean'],
        ];

        if ($this->editingUserId) {
            $rules['userEmail'][] = 'unique:users,email,' . $this->editingUserId;
            if ($this->userPassword) {
                $rules['userPassword'] = ['string', 'min:8'];
            }
        } else {
            $rules['userEmail'][] = 'unique:users,email';
            $rules['userPassword'] = ['required', 'string', 'min:8'];
        }

        $this->validate($rules);

        if ($this->editingUserId) {
            $user = User::findOrFail($this->editingUserId);
            $user->name = $this->userName;
            $user->email = $this->userEmail;
            $user->role = $this->userRole;
            $user->is_active = $this->userActive;

            if ($this->userPassword) {
                $user->password = bcrypt($this->userPassword);
            }

            $user->save();
        } else {
            User::create([
                'name' => $this->userName,
                'email' => $this->userEmail,
                'role' => $this->userRole,
                'is_active' => $this->userActive,
                'password' => bcrypt($this->userPassword),
            ]);
        }

        $this->showModal = false;
    }

    public function toggleActive(int $id): void
    {
        $user = User::findOrFail($id);

        if ($user->id === auth()->id()) {
            session()->flash('error', 'You cannot deactivate your own account.');
            return;
        }

        $user->is_active = !$user->is_active;
        $user->save();
    }

    public function render()
    {
        $query = User::query()->orderBy('name');

        if ($this->role) {
            $query->where('role', $this->role);
        }

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
