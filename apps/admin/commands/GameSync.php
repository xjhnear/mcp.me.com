<?php
use Yxd\Services\DataSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class GameSync extends Command 
{
	protected $name = 'command:game-sync';
	
	protected $description = 'This is GameSync command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		$cmd = $this->argument('cmd');//crawl-newgame|crawl-gameprice|sync-newgame|sync-gameprice
		DataSyncService::syncGame($cmd);		
		$this->info('game-sync command is called success');
	}
	
    protected function getArguments()
	{		
		return array(
			array('cmd', InputArgument::REQUIRED, 'cmd is what'),
		);
	}
}
