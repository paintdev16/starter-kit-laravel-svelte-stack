<?php

namespace App\Enums;

enum ActivityAction: string
{
    case Login = 'auth.login';
    case PasswordChanged = 'auth.password_changed';
    case PasswordReset = 'auth.password_reset';
    case UserCreated = 'user.created';
    case UserUpdated = 'user.updated';
    case UserDeleted = 'user.deleted';
    case RoleCreated = 'role.created';
    case RoleUpdated = 'role.updated';
    case RoleDeleted = 'role.deleted';
    case PermissionCreated = 'permission.created';
    case PermissionUpdated = 'permission.updated';
    case PermissionDeleted = 'permission.deleted';
    case RealtimeToggled = 'realtime.toggled';
}
