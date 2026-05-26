<?php

namespace App\Application\Services\Auth;

use App\Models\User;

final class AuthUserPresenter
{
    public function present(User $user): array
    {
        return [
            'id'            => $user->id,
            'name'          => $user->name,
            'email'         => $user->email,
            'role'          => $user->role,
            'student_code'  => $user->student_code,
            'department_id' => $user->department_id,
            'home_url'      => $this->resolveHomeUrl($user->role),
        ];
    }

    public function resolveHomeUrl(string $role): string
    {
        return match ($role) {
            User::ROLE_ADMIN     => '/admin',
            User::ROLE_BO_MON    => '/department',
            User::ROLE_SINH_VIEN => '/student',
            default              => '/',
        };
    }
}
