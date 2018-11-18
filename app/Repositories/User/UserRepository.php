<?php

namespace  App\Repositories\User;

use DB;
use Storage;
use Exception;
use App\Repositories\BaseRepository;
use App\Models\User;

class UserRepository extends BaseRepository implements UserInterface
{
    public function __construct(User $user)
    {
        parent::__construct($user);
    }

    public function changeStatus($ids, $status)
    {
        DB::beginTransaction();
        try {
            $ids = (is_array($ids) ? $ids : [$ids]);
            $this->multiUpdate('id', $ids, ['status' => $status]);
            DB::commit();

            return true;
        } catch (Exception $e) {
            DB::rollback();

            return false;
        }
    }

    public function uploadAvatar($file)
    {
        if (!$file) {
            return config('users.avatar_default');
        }

        $filePath = $file->store(config('users.avatar_path'));
        $result = explode('/', $filePath);
        $fileName = end($result);

        return $fileName;
    }

    public function findEmail($data, $userId)
    {
        $users = $this->model
            ->where('email', 'like', '%' . $data['keyword'] . '%')
            ->where('id', '!=', $userId);
        if (count($data['emails'])) {
            $users = $users->whereNotIn('email', $data['emails']);
        }

        return $users->paginate(config('survey.get_top_mail_suggestion'));
    }
}
