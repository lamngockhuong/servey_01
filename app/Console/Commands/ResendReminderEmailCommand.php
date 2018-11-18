<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Invite\InviteInterface;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Survey;

class ResendReminderEmailCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:resend-reminder-email';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Resend reminder email by week, month or quarter';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        SurveyInterface $surveyRepository,
        InviteInterface $inviteRepository
    ) {
        parent::__construct();
        $this->surveyRepository = $surveyRepository;
        $this->inviteRepository = $inviteRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $surveys = $this->surveyRepository
            ->with('user', 'settings')
            ->whereHas('settings', function ($query) {
                return $query->where('key', config('settings.key.reminder'))
                    ->whereIn('value', array_values(config('settings.reminder')));
            })
            ->where('status', '<>', config('survey.status.block'))
            ->get();

        foreach ($surveys as $survey) {
            if (!empty($survey->next_reminder_time) &&
                $survey->next_reminder_time > Carbon::now()->subMinutes(1) &&
                $survey->next_reminder_time < Carbon::now()->addMinutes(1)
            ) {
                $type = $survey->settings->whereIn('key', config('settings.key.reminder'))->toArray()[1]['value'];
                $nextTime = Carbon::now();

                switch ($type) {
                    case config('settings.reminder.week'):
                        $nextTime->addWeek();
                        break;
                    case config('settings.reminder.month'):
                        $nextTime->addMonth();
                        break;
                    case config('settings.reminder.quarter'):
                        $nextTime->addQuarter();
                        break;
                    default:
                        break;
                }

                // remind: sendMail use queue, have to run "php artisan queue:work"
                $this->sendMail($survey);
                $survey->update(['next_reminder_time' => $nextTime]);
            }
        }
    }

    public function sendMail($survey)
    {
        $emails = $this->inviteRepository->where('survey_id', $survey->id)->pluck('mail')->toArray();
        if (count($emails)) {
            $data = [
                'name' => empty($survey->user_id) ? $survey->user_name : $survey->user->name,
                'title' => $survey->title,
                'description' => $survey->description,
                'link' => action($survey->feature
                    ? 'AnswerController@answerPublic'
                    : 'AnswerController@answerPrivate', [
                        'token' => $survey->token,
                    ]),
                'emailSender' => empty($survey->user_id) ? $survey->mail : $survey->user->email,
            ];
            $view = 'emails.email_invite';
            $subject = trans('survey.invite');

            Mail::queue($view, $data, function ($message) use ($emails, $subject) {
                $message->from(config('mail.from.address'), trans('survey.title_web'));
                $message->to($emails)->subject($subject);
            });
        }
    }
}
