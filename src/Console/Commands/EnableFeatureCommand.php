<?php

namespace AyupCreative\Features\Console\Commands;

use AyupCreative\Features\FeatureManager;
use Illuminate\Console\Command;

class EnableFeatureCommand extends Command
{
    protected $signature = 'features:enable {key?} 
        { --all : Enable all features }';
    protected $description = 'Enable a feature flag';

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
                $this->manager->enable($feature);
            }
            $this->info('All features enabled.');
            return Command::SUCCESS;
        }

        app(FeatureManager::class)->enable($this->argument('key'));
        $this->info('Features enabled.');
        return Command::SUCCESS;
    }
}
