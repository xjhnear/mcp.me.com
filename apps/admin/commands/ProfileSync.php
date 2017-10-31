<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class ProfileSync extends Command 
{
	protected $name = 'command:profile-sync';
	
	protected $description = 'This is Profile command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//
		ProfileModel::syncFileToDb();
		//ProfileModel::syncOneFileToDb();
		$this->info('profile-sync command is called success');
	}
}
