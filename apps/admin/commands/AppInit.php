<?php
use Yxd\Services\DataSyncService;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AppInit extends Command 
{
	protected $name = 'command:app-init';
	
	protected $description = 'This is AppInit command';
	
    public function __construct()
	{
		parent::__construct();
	}
	
    public function fire()
	{
		//DataSyncService::appInitRedis();
		//$this->info('init redis success');
		//DataSyncService::appInitUserCache();
		//$this->info('init usercache success');
		//DataSyncService::syncGiftbag();
		//$this->info('sync giftbag success');
		//DataSyncService::appInitFeed();
		Youxiduo\Android\GiftbagService::initBaiduTag();
		$this->info('sync feeds success');
	}
}
