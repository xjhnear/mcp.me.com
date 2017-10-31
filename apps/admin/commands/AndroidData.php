<?php

use Yxd\Services\AndroidService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AndroidData extends Command 
{
	protected $name = 'command:android-data';
	
	protected $description = 'This is AndroidData command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		$cmd = $this->argument('cmd');//crawl-newgame|crawl-gameprice|sync-newgame|sync-gameprice
		AndroidService::sync($cmd);
		$this->info('android-data ' .$cmd. ' command is called success');
	}
	
    protected function getArguments()
	{		
		return array(
			array('cmd', InputArgument::REQUIRED, 'cmd is what'),
		);
	}
}
