<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Yxd\Modules\Message\PromptService;

class ReserveGiftbag extends Command 
{
	protected $name = 'command:reserve';
	
	protected $description = 'This is reserve command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//
		PromptService::distributeReserve();
		$this->info('reserve command is called success');
	}
}
