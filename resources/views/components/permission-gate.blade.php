@props(['permission', 'permissions' => null, 'role' => null, 'roles' => null])

@php
    $show = true;
    
    if ($permission && !user_has_permission($permission)) {
        $show = false;
    }
    
    if ($permissions && !user_has_any_permission($permissions)) {
        $show = false;
    }
    
    if ($role && !user_has_role($role)) {
        $show = false;
    }
    
    if ($roles && !user_has_any_role($roles)) {
        $show = false;
    }
@endphp

@if($show)
    {{ $slot }}
@endif
