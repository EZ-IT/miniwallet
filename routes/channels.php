<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{userId}', static function ($user, $userId) {
    return (int) $user->id === (int) $userId;
});
