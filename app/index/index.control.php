<?php
class IndexControl extends Control
{	
	public function __construct()
	{
		
		$this->model = new IndexModel();
		$this->view = new IndexView();
		
		parent::__construct();
		
	}
	
	public function index()
	{
		addScript('/js/slippry/slippry.min.js');
		addCss('/js/slippry/slippry.css');
		
		addTitle('Restlos glücklich!');
		
		$this->setContentWidth(9, 9);
		
		$articles = array();
		
		/*
		if(!S::may())
		{
			$articles[] = $this->view->joinIndex();
		}*/
		
		if($news = $this->view->newsSlider($this->model->latestNews()))
		{
			$articles = array_merge($articles,$news);
		}
		addContent($this->view->printSlider($articles),CNT_OVERTOP);
		
		$ftcount = 5;
		if(!S::may())
		{
			addContent($this->view->login('/?page=dashboard'),CNT_LEFT);
			$ftcount = 2;
		}
		
		/*
		 * display some some nice fairteiler posts with images if user locationis not defined
		*/
		if($posts = $this->model->getNewestFairteilerPosts($ftcount))
		{
			addContent($this->view->fairteiler($posts),CNT_LEFT);
		}
		
		/*
		 * display some newest foodbaskets if user location is not defined
		 */
		if($baskets = $this->model->getNewestFoodbaskets(5))
		{
			addContent($this->view->baskets($baskets));
		}
		
		//addContent('Hallo Foodsharing-Welt');
	}
}