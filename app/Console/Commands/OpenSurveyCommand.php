<?php

namespace App\Console\Commands;

use App\Models\Survey;
use Illuminate\Console\Command;
use App\Repositories\Survey\SurveyInterface;
use Carbon\Carbon;

class OpenSurveyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:open-survey';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Open the survey when it\'s time';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SurveyInterface $surveyRepository)
    {
        parent::__construct();
        $this->surveyRepository = $surveyRepository;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $surveyIds = $this->surveyRepository
            ->where('start_time', '>', Carbon::now()->subMinutes(1))
            ->where('start_time', '<', Carbon::now()->addMinutes(1))
            ->where('status', '<>', config('survey.status.available'))
            ->lists('id')
            ->all();
        $this->surveyRepository->newQuery(new Survey());
        $this->surveyRepository->multiUpdate('id', $surveyIds, ['status' => config('survey.status.available')]);
    }
}
