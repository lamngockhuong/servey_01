<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use App\Repositories\Survey\SurveyInterface;
use App\Repositories\Setting\SettingInterface;
use App\Models\Survey;

class CloseSurveyCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:auto-change-status';
    protected $surveyRepository;
    protected $settingRepository;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update database when the suvey expired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SurveyInterface $surveyRepository, SettingInterface $settingRepository)
    {
        parent::__construct();
        $this->settingRepository = $settingRepository;
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
            ->where('deadline', '<', Carbon::now())
            ->where('status', '<>', config('survey.status.block'))
            ->lists('id')
            ->all();
        $surveyInSetting = $this->settingRepository
            ->where('key', config('settings.key.limitAnswer'))
            ->where('value', '0')
            ->lists('survey_id')
            ->all();
        $surveyCloses = array_unique(array_merge($surveyIds, $surveyInSetting));
        $this->surveyRepository->newQuery(new Survey());
        $this->surveyRepository->multiUpdate('id', $surveyCloses, ['status' => config('survey.status.block')]);
    }
}
