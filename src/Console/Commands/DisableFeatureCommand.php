<?php

namespace AyupCreative\Features\Console\Commands;

use AyupCreative\Features\FeatureManager;
use Illuminate\Console\Command;

class DisableFeatureCommand extends Command
{
    protected $signature = 'features:disable {key?} 
        { --all : Disable all features}';
    protected $description = 'Disable a feature flag';

    public function __construct(
        protected FeatureManager $manager
    )
    {
        parent::__construct();
    }

    public function handle(): int
    {
        if(!$this->option('all') && !$this->argument('key')) {
            $this->error('You must specify a feature key or use the --all flag.');
            return Command::FAILURE;
        }

        if($this->option('all')) {
            foreach($this->manager->all() as $feature => $enabled) {
                $this->manager->disable($feature);
            }
            $this->info('All features disabled.');
            return Command::SUCCESS;
        }

        app(FeatureManager::class)->disable($this->argument('key'));
        $this->info('Features disabled.');

        return Command::SUCCESS;
    }
}
