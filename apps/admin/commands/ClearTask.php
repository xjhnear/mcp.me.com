<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;
use Youxiduo\Android\Control\TaskApi;

class ClearTask extends Command 
{
	protected $name = 'command:clear-task';
	
	protected $description = 'This is ClearTask command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		TaskApi::clearFinishShareTask();
		$this->info('command:clear-task success');
	}
}
