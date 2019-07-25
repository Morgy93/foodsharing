<?php

namespace Foodsharing\Modules\Store;

use Carbon\Carbon;
use Foodsharing\Lib\Xhr\Xhr;
use Foodsharing\Lib\Xhr\XhrResponses;
use Foodsharing\Lib\Xhr\XhrDialog;
use Foodsharing\Modules\Core\Control;
use Foodsharing\Modules\Core\DBConstants\Region\Type;
use Foodsharing\Permissions\StorePermissions;
use Foodsharing\Services\SanitizerService;
use Foodsharing\Services\StoreService;

class StoreXhr extends Control
{
	private $storeGateway;
	private $storePermissions;
	private $storeService;
	private $sanitizerService;

	public function __construct(
		StoreModel $model,
		StoreView $view,
		StoreGateway $storeGateway,
		StorePermissions $storePermissions,
		StoreService $storeService,
		SanitizerService $sanitizerService
	) {
		$this->model = $model;
		$this->view = $view;
		$this->storeGateway = $storeGateway;
		$this->storePermissions = $storePermissions;
		$this->storeService = $storeService;
		$this->sanitizerService = $sanitizerService;

		parent::__construct();

		if (!$this->session->may('fs')) {
			exit();
		}
	}

	public function savedate()
	{
		$storeId = (int)$_GET['bid'];
		if (!$this->storePermissions->mayAddPickup($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if (strtotime($_GET['time']) > 0 && $_GET['fetchercount'] >= 0) {
			$fetchercount = (int)$_GET['fetchercount'];
			$time = $_GET['time'];
			if ($fetchercount > 8) {
				$fetchercount = 8;
			}

			if ($this->storeService->changePickupSlots($storeId, Carbon::createFromTimeString($time), $fetchercount)) {
				$this->flashMessageHelper->info('Abholtermin wurde eingetragen!');

				return array(
					'status' => 1,
					'script' => 'reload();'
				);
			}
		}
	}

	public function deldate()
	{
		$storeId = (int)$_GET['id'];
		if (!$this->storePermissions->mayDeletePickup($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if (isset($storeId, $_GET['time']) && strtotime($_GET['time']) > 0) {
			$this->model->deldate($storeId, $_GET['time']);

			$this->flashMessageHelper->info('Abholtermin wurde gelöscht.');

			return array(
				'status' => 1,
				'script' => 'reload();'
			);
		}
	}

	public function getfetchhistory()
	{
		$storeId = (int)$_GET['bid'];

		if (!$this->storePermissions->maySeeFetchHistory($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		if ($history = $this->model->getFetchHistory($storeId, $_GET['from'], $_GET['to'])) {
			return array(
				'status' => 1,
				'script' => '
				$("daterange_from").datepicker("close");
				$("daterange_to").datepicker("close");
					
				$("#daterange_content").html(\'' . $this->sanitizerService->jsSafe($this->view->fetchlist($history)) . '\');
					'
			);
		}
	}

	public function fetchhistory()
	{
		$storeId = (int)$_GET['bid'];

		if (!$this->storePermissions->maySeeFetchHistory($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$dia = new XhrDialog();
		$dia->setTitle('Abholungshistorie');

		$id = 'daterange';

		$dia->addContent($this->view->fetchHistory());

		$dia->addJsAfter('

				$( "#' . $id . '_from" ).datepicker({
					changeMonth: true,
					maxDate: "0",
					
					onClose: function( selectedDate ) {
						$( "#' . $id . '_to" ).datepicker( "option", "minDate", selectedDate );
					}
				});
				$( "#' . $id . '_to" ).datepicker({
					changeMonth: true,
					maxDate: "0",
					autoOpen: true,
					onClose: function( selectedDate ) {
						$( "#' . $id . '_from" ).datepicker( "option", "maxDate", selectedDate );
					}
				});
				
				$( "#' . $id . '_to" ).val(new Date(Date.now()).toLocaleDateString("de-DE", {year: "numeric", month: "2-digit", day: "2-digit", }));
				$( "#' . $id . '_from" ).datepicker("show");
				
				
				$(window).on("resize", function(){
					$("#' . $dia->getId() . '").dialog("option",{
						height:($(window).height()-40)
					});
				});
				
				$("#daterange_submit").on("click", function(ev){
					ev.preventDefault();
				
					var date = $( "#' . $id . '_from" ).datepicker("getDate");
					
					var from = "";
					var to = "";
					
					if(date !== null)
					{
						from = date.getFullYear() + "-" + preZero((date.getMonth()+1)) + "-" + preZero(date.getDate());
						date = $( "#' . $id . '_to" ).datepicker("getDate");
					
						if(date === null)
						{
							to = from;
						}
						else
						{
							to = date.getFullYear() + "-" + preZero((date.getMonth()+1)) + "-" + preZero(date.getDate());

							var now = new Date();
							if(date.toDateString() == now.toDateString()) {
								to = to + " " + preZero(now.getHours()) + ":" + preZero(now.getMinutes()) + ":59"
							} else {
								to = to + " " + "23:59:59"
							}
						}
				
						ajreq("getfetchhistory",{app:"betrieb",from:from,to:to,bid:' . $storeId . '});
					}
					else
					{
						alert("Du musst erst ein Datum ausw&auml;hlen ;)");
					}
				});
				
		');

		if ($this->session->isMob()) {
			$dia->addOpt('width', '95%');
		} else {
			$dia->addOpt('width', '40%');
		}

		$dia->addOpt('height', '($(window).height()-40)', false);
		$dia->noOverflow();

		return $dia->xhrout();
	}

	public function adddate()
	{
		$storeId = (int)$_GET['id'];
		if (!$this->storePermissions->mayAddPickup($storeId)) {
			return XhrResponses::PERMISSION_DENIED;
		}

		$dia = new XhrDialog();
		$dia->setTitle('Abholtermin eintragen');
		$dia->addContent($this->view->dateForm());
		$dia->addOpt('width', 280);
		$dia->setResizeable(false);
		$dia->addAbortButton();
		$dia->addButton('Speichern', 'saveDate();');

		$dia->addJs('
				
			function saveDate()
			{
				var date = $("#datepicker").datepicker( "getDate" );
				
				date = date.getFullYear() + "-" +
				    ("00" + (date.getMonth()+1)).slice(-2) + "-" +
				    ("00" + date.getDate()).slice(-2) + " " + 
				    ("00" + $("select[name=\'time[hour]\']").val()).slice(-2) + ":" + 
				    ("00" + $("select[name=\'time[min]\']").val()).slice(-2) + ":00";
				
				if($("#fetchercount").val() >= 0)
				{
					ajreq("savedate",{
						app:"betrieb",
						time:date,
						fetchercount:$("#fetchercount").val(),
						bid:' . $storeId . '
					});
				}
				else
				{
					pulseError("Du musst noch die Anzahl der Abholer/innen auswählen");
				}
			}
				
			$("#datepicker").datepicker({
				minDate: new Date()
			});	
		');

		return $dia->xhrout();
	}

	public function savebezirkids()
	{
		if (isset($_GET['ids']) && is_array($_GET['ids']) && count($_GET['ids']) > 0) {
			foreach ($_GET['ids'] as $b) {
				if ($this->storePermissions->mayEditStore($b['id']) && (int)$b['v'] > 0) {
					$this->model->updateBetriebBezirk($b['id'], $b['v']);
				}
			}
		}

		return array('status' => 1);
	}

	public function setbezirkids()
	{
		if (isset($_SESSION['client']['verantwortlich']) && is_array($_SESSION['client']['verantwortlich'])) {
			$ids = array();
			foreach ($_SESSION['client']['verantwortlich'] as $b) {
				$ids[] = (int)$b['betrieb_id'];
			}
			if (!empty($ids)) {
				if ($betriebe = $this->model->q('SELECT id,name,bezirk_id,str,hsnr FROM fs_betrieb WHERE id IN(' . implode(',', $ids) . ') AND ( bezirk_id = 0 OR bezirk_id IS NULL)')) {
					$dia = new XhrDialog();

					$dia->setTitle('Fehlende Zuordnung');
					$dia->addContent($this->v_utils->v_info('Für folgende Betriebe wurde noch kein Bezirk zugeordnet. Bitte gib einen Bezirk an!'));
					$dia->addOpt('width', '650px');
					$dia->noOverflow();

					$bezirks = $this->session->getRegions();

					foreach ($bezirks as $key => $b) {
						if (!in_array($b['type'], [Type::CITY, Type::DISTRICT, Type::REGION, Type::PART_OF_TOWN])) {
							unset($bezirks[$key]);
						}
					}

					$cnt = '
					<div id="betriebetoselect">';
					foreach ($betriebe as $b) {
						$cnt .= $this->v_utils->v_form_select('b_' . $b['id'], array(
							'label' => $b['name'] . ', ' . $b['str'] . ' ' . $b['hsnr'],
							'values' => $bezirks
						));
					}
					$cnt .= '
					</div>';
					$dia->addJs('
						$("#savebetriebetoselect").on("click", function(ev){
							ev.preventDefault();
							
							var saveArr = new Array();
							
							$("#betriebetoselect select.input.select").each(function(){
								var $this = $(this);
								var value = parseInt($this.val());
								var id = parseInt($this.attr("id").split("b_")[1]);
							
								if(id > 0 && value > 0)
								{
									saveArr.push({
										id:id,
										v:value
									});
								}
							});
							
							if(saveArr.length > 0)
							{
								ajax.req("betrieb","savebezirkids",{
									data: {ids: saveArr},
									success: function(){
										pulseInfo("Erfolgreich gespeichert!");
										$("#' . $dia->getId() . '").dialog("close");
									}
								});
							}
						});		
					');
					$dia->addContent($cnt);
					$dia->addContent($this->v_utils->v_input_wrapper(false, '<a class="button" id="savebetriebetoselect" href="#">' . $this->translationHelper->s('save') . '</a>'));

					return $dia->xhrout();
				}
			}
		}
	}

	public function signout()
	{
		$xhr = new Xhr();
		$status = $this->storeGateway->getUserTeamStatus($this->session->id(), $_GET['id']);
		if ($status === TeamStatus::Coordinator) {
			$xhr->addMessage($this->translationHelper->s('signout_error_admin'), 'error');
		} elseif ($status >= TeamStatus::Applied) {
			$this->model->signout($_GET['id'], $this->session->id());
			$xhr->addScript('goTo("/?page=relogin&url=" + encodeURIComponent("/?page=dashboard") );');
		} else {
			$xhr->addMessage($this->translationHelper->s('no_member'), 'error');
		}
		$xhr->send();
	}
}
