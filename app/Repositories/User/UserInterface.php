<?php

namespace App\Repositories\User;

interface UserInterface
{
    public function changeStatus($input, $status);

    public function uploadAvatar($file);

    public function findEmail($data, $userId);
}
