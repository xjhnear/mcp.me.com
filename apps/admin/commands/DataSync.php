<?php
use Yxd\Services\DataSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class DataSync extends Command 
{
	protected $name = 'command:data-sync';
	
	protected $description = 'This is DataSync command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//DataSyncService::syncComment();
		//DataSyncService::syncGameDownload();
		//DataSyncService::syncGameAdvDownload();
		//DataSyncService::syncFeedUser();
		//DataSyncService::syncGiftbag();//安卓礼包
		//DataSyncService::outEveryDayGameDown();
                DataSyncService::autoSendMessage();
		$this->info('data-sync command is called success');
	}
}
