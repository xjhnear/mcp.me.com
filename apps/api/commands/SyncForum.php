<?php
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

use Yxd\Services\SyncToV4ForumService;

class SyncForum extends Command 
{
	protected $name = 'command:sync-forum';
	
	protected $description = 'This is ApplePush command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//
		SyncToV4ForumService::syncTopic();
		SyncToV4ForumService::syncReply();
		$this->info('sync forum command is called success');
	}
}
