<?php

namespace Foodsharing\Modules\Dashboard;

use Foodsharing\Modules\Core\View;

class DashboardView extends View
{
	public function newBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . '</span>
					<span class="t">veröffentlicht am ' . $this->timeHelper->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('new_foodbaskets'));
	}

	public function updates()
	{
		$this->pageHelper->addContent('
	<div class="head ui-widget-header ui-corner-top">
		Updates-Übersicht<span class="option"><a id="activity-option" href="#activity-listings" class="fas fa-cog"></a></span>
	</div>
	<div id="activity">
		<div class="loader" style="padding:40px;background-image:url(/img/469.gif);background-repeat:no-repeat;background-position:center;"></div>
		<div style="display:none" id="activity-info">' . $this->v_utils->v_info('Es gibt gerade nichts Neues') . '</div>
	</div>');
	}

	public function foodsharerMenu()
	{
		return $this->menu(array(
			array('name' => $this->translationHelper->s('new_basket'), 'click' => "ajreq('newBasket',{app:'basket'});return false;"),
			array('name' => $this->translationHelper->s('all_baskets'), 'href' => '/karte?load=baskets')
		));
	}

	public function closeBaskets($baskets)
	{
		$out = '<ul class="linklist baskets">';
		foreach ($baskets as $b) {
			$out .= '
			<li>
				<a onclick="ajreq(\'bubble\',{app:\'basket\',id:' . (int)$b['id'] . '});return false;" href="#" class="corner-all">
					<span class="i">' . $this->img($b) . '</span>
					<span class="n">Essenskorb von ' . $b['fs_name'] . ' (' . $this->distance($b['distance']) . ')</span>
					<span class="t">' . $this->timeHelper->niceDate($b['time_ts']) . '</span>
					<span class="d">' . $b['description'] . '</span>
					<span class="c"></span>
				</a>
	
			</li>';
		}

		$out .= '
				</ul>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('close_foodbaskets'));
	}

	private function img($basket)
	{
		if ($basket['picture'] != '' && file_exists(ROOT_DIR . 'images/basket/50x50-' . $basket['picture'])) {
			return '<img src="/images/basket/thumb-' . $basket['picture'] . '" height="50" />';
		}

		return '<img src="/img/basket50x50.png" height="50" />';
	}

	public function becomeFoodsaver()
	{
		return '
	   <div class="msg-inside info">
			   <i class="fas fa-info-circle"></i> <strong><a href="/?page=settings&sub=upgrade/up_fs">Möchtest Du auch Lebensmittel bei Betrieben retten und fair-teilen?<br />Werde Foodsaver!</a></strong>
	   </div>';
	}

	public function u_nextDates($dates)
	{
		$out = '
		<div class="ui-padding">
			<ul class="datelist linklist">';
		foreach ($dates as $d) {
			$confirmSymbol = $d['confirmed'] == 1 ? '✓ ' : '? ';
			$out .= '
				<li>
					<a href="/?page=fsbetrieb&id=' . $d['betrieb_id'] . '" class="ui-corner-all">
						<span class="title">' . $confirmSymbol . $this->timeHelper->niceDate($d['date_ts']) . '</span>
						<span>' . $d['betrieb_name'] . '</span>
					</a>
				</li>';
		}
		$out .= '
			</ul>
		</div>';

		return $this->v_utils->v_field($out, $this->translationHelper->s('next_dates'));
	}

	public function u_myBetriebe($betriebe)
	{
		$out = '';
		if (!empty($betriebe['verantwortlich'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['verantwortlich'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out = $this->v_utils->v_field($list, 'Du bist verantwortlich für', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['team'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['team'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Du holst Lebensmittel ab bei', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['waitspringer'])) {
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['waitspringer'] as $b) {
				$list .= '
				<li>
					<a class="ui-corner-all" href="/?page=fsbetrieb&id=' . $b['id'] . '">' . $b['name'] . '</a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Du bist auf der Springerliste bei', array('class' => 'ui-padding'));
		}

		if (!empty($betriebe['anfrage'])) {
			$this->pageHelper->addJsFunc('
				function u_anfrage_action(key,el)
				{
					val = $(el).children("input:first").val().split(":::");
					
					if(key == "deny")
					{
						u_sign_out(val[0],val[1],el);
					}
					else if(key == "map")
					{
						u_gotoMap(val[0],val[1],el);
					}
				}
	
				function u_sign_out(fsid,bid,el)
					{
						var item = $(el);
						showLoader();
						$.ajax({
							dataType:"json",
							data: "fsid="+fsid+"&bid="+bid,
							url:"/xhr.php?f=denyRequest",
							success : function(data){
								if(data.status == 1)
								{
									pulseSuccess(data.msg);
									window.setTimeout(function(){reload()},1500);
								}else{
									pulseError(data.msg);
									window.setTimeout(function(){reload()},1500);
								}
							},
							complete:function(){hideLoader();}
						});	
					}	
	
				function u_gotoMap(fsid,betriebid,el)
					{
						var item = $(el);
						showLoader();
						var baseUrl = "?page=map&bid=";
						window.location.href = baseUrl+betriebid;
						
					}	
			');
			$this->pageHelper->addJs('
				function createSignoutMenu() {
					return {
						callback: function(key, options) {
							u_anfrage_action(key,this);
						},
						items: {
							"deny": {name: "Anfrage beenden",icon:"fas fa-trash-alt fa-fw"},
							"map": {name: "Auf Karte anschauen",icon:"fas fa-map-marked-alt fa-fw"}
						}
					};
				}
			
				$("#store-request").on("click", function(e){
					var $this = $(this);
					$this.data("runCallbackThingie", createSignoutMenu);
					var _offset = $this.offset(),
						position = {
							x: _offset.left - 30, 
							y: _offset.top - 97
						}
					$this.contextMenu(position);
				});
	
				$.contextMenu({
					selector: "#store-request",
					trigger: "none",
					build: function($trigger, e) {
						return $trigger.data("runCallbackThingie")();
					}
				});		
				
				
			');
			$list = '
			<ul class="linklist">';
			foreach ($betriebe['anfrage'] as $b) {
				//<a id="anfrage-betrieb" class="ui-corner-all" href="/?page=fsbetrieb&id='.$b['id'].'">'.$b['name'].'</a>
				$list .= '
				<li>
					<a id="store-request" class="ui-corner-all" href="#" onclick="return false;">' . $b['name'] . '<input type="hidden" name="anfrage" value="' . $this->session->id() . ':::' . $b['id'] . '" /></a>
				</li>';
			}
			$list .= '
			</ul>';
			$out .= $this->v_utils->v_field($list, 'Anfragen gestellt bei', array('class' => 'ui-padding'));
		}

		if (empty($out)) {
			$out = $this->v_utils->v_info('Du bist bis jetzt in keinem Betriebsteam.');
		}

		return $out;
	}

	public function u_updates($updates)
	{
		$out = '';
		$i = 0;
		foreach ($updates as $u) {
			$fs = array(
				'id' => $u['foodsaver_id'],
				'name' => $u['foodsaver_name'],
				'photo' => $u['foodsaver_photo'],
				'sleep_status' => $u['sleep_status']
			);
			$out .= '
			<div class="updatepost">
					<a class="poster ui-corner-all" href="/profile/' . (int)$u['foodsaver_id'] . '">
						' . $this->imageService->avatar($fs, 50) . '
					</a>
					<div class="post">
						' . $this->u_update_type($u) . '
					</div>
					<div style="clear:both;"></div>
			</div>';
		}

		return $this->v_utils->v_field($out, $this->translationHelper->s('updates'), array('class' => 'ui-padding'));
	}

	public function u_update_type($u)
	{
		$out = '';
		if ($u['type'] == 'forum') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> hat etwas zum Thema "<a href="/?page=bezirk&bid=' . $u['bezirk_id'] . '&sub=forum&tid=' . $u['id'] . '&pid=' . $u['last_post_id'] . '#post' . $u['last_post_id'] . '">' . $u['name'] . '</a>" ins Forum geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['post_body'] . '
					</div>
	
				</div>
				
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->timeHelper->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		} elseif ($u['type'] == 'bforum') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> hat etwas zum Thema "<a href="/?page=bezirk&bid=' . $u['bezirk_id'] . '&sub=botforum&tid=' . $u['id'] . '&pid=' . $u['last_post_id'] . '#post' . $u['last_post_id'] . '">' . $u['name'] . '</a>" ins Botschafterforum geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['post_body'] . '
					</div>
	
				</div>
			
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->timeHelper->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		} elseif ($u['type'] == 'bpin') {
			$out = '
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<a href="/profile/' . (int)$u['foodsaver_id'] . '">' . $u['foodsaver_name'] . '</a> hat etwas auf die Pinnwand von <a href="/?page=fsbetrieb&id=' . $u['betrieb_id'] . '">' . $u['betrieb_name'] . '</a> geschrieben.
						</div>
					</div>
	
					<div class="activity_feed_content_link">
						' . $u['text'] . '
					</div>
	
				</div>
			
				<div class="js_feed_comment_border">
					<div class="comment_mini_link_like">
						<div class="foot">
							<span class="time">' . $this->timeHelper->niceDate($u['update_time_ts']) . '</span>
						</div>
					</div>
					<div class="clear"></div>
				</div>';
		}

		return $out;
	}

	public function u_invites($invites)
	{
		$this->pageHelper->addStyle('
			@media (max-width: 410px)
			{
				.top_margin_on_small_screen 
				{
					margin-top: 45px;
				}
			}
		');

		$out = '';
		foreach ($invites as $i) {
			$out .= '
			<div class="post event" style="border-bottom:1px solid #E3DED3; padding-bottom:15px;">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->translationHelper->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>
						
				
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>
					<div class="top_margin_on_small_screen">
						<a href="#" onclick="ajreq(\'accept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Einladung annehmen</a> 
						<a href="#" onclick="ajreq(\'maybe\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Vielleicht</a> 
						<a href="#" onclick="ajreq(\'noaccept\',{app:\'event\',id:\'' . (int)$i['id'] . '\'});return false;" class="button">Nein</a>
					</div>
				</div>
				
				<div class="clear"></div>
			</div>
			';
		}

		return $this->v_utils->v_field($out, $this->translationHelper->s('you_were_invited'), array('class' => 'ui-padding'));
	}

	public function u_events($events)
	{
		$out = '';
		foreach ($events as $i) {
			$out .= '
			<div class="post event" style="border-bottom:1px solid #E3DED3; padding-bottom:15px;padding-top:15px;">
				<a href="/?page=event&id=' . (int)$i['id'] . '" class="calendar">
					<span class="month">' . $this->translationHelper->s('month_' . (int)date('m', $i['start_ts'])) . '</span>
					<span class="day">' . date('d', $i['start_ts']) . '</span>
				</a>
			
				<div class="activity_feed_content">
					<div class="activity_feed_content_text">
						<div class="activity_feed_content_info">
							<p><a href="/?page=event&id=' . (int)$i['id'] . '">' . $i['name'] . '</a></p>
							<p>' . $this->timeHelper->niceDate($i['start_ts']) . '</p>
						</div>
					</div>
	
					<div>
						<a href="/?page=event&id=' . (int)$i['id'] . '" class="button">Zum Event</a> 
					</div>
				</div>
			
				<div class="clear"></div>
			</div>
			';
		}

		if (count($events) > 1) {
			$eventTitle = $this->translationHelper->s('events_headline') . ' (' . count($events) . ')';
		} else {
			$eventTitle = $this->translationHelper->s('event_headline');
		}

		return $this->v_utils->v_field($out, $eventTitle, array('class' => 'ui-padding moreswap'));
	}
}
