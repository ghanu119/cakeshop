<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    if ((int) $user->id !== (int) $id) {
        return false;
    }

    return $user->hasAnyRole(['Admin', 'Kitchen']);
});
