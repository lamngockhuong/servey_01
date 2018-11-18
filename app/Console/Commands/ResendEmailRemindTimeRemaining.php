<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Invite\InviteInterface;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use App\Models\Survey;

class ResendEmailRemindTimeRemaining extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:resend-email-remind-time-remaining';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send an email inform about survey time remaining is 30 minutes';

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
        $timelineOne = Carbon::now()->addMinutes(29);
        $timelineTwo = Carbon::now()->addMinutes(31);
        $surveys = $this->surveyRepository
            ->with('user')
            ->where('deadline', '>', $timelineOne)
            ->where('deadline', '<', $timelineTwo)
            ->where('status', '<>', config('survey.status.block'))
            ->get();

        foreach ($surveys as $survey) {
            $this->sendMail($survey);
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
