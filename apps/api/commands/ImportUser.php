<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Youxiduo\Chat\ConsoleService;
class ImportUser extends Command 
{
	protected $name = 'command:import-user';
	
	protected $description = 'This is ImportUser command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//
		ConsoleService::importUsers();
		$this->info('import user command is called success');
	}
}
