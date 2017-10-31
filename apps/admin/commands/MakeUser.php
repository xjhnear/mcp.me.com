<?php
use Yxd\Services\DataSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class MakeUser extends Command 
{
	protected $name = 'command:make-user';
	
	protected $description = 'This is MakeUser command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		DataSyncService::syncUser();
		$this->info('make-user command is called success');
	}
}
