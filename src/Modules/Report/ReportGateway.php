<?php

namespace Foodsharing\Modules\Report;

use Envms\FluentPDO\Queries\Select;
use Foodsharing\Modules\Core\BaseGateway;

class ReportGateway extends BaseGateway
{
	/* Reporttype: 1: Other (see list ReportView for list of possible reasons, they are all mapped to 1...), 2: missed pickup */

	public function addBetriebReport($reportedId, $reporterId, $reasonId, $reason, $message, $storeId = 0): int
	{
		return $this->db->insert(
			'fs_report',
			[
				'foodsaver_id' => (int)$reportedId,
				'reporter_id' => (int)$reporterId,
				'reporttype' => (int)$reasonId,
				'betrieb_id' => (int)$storeId,
				'time' => date('Y-m-d H:i:s'),
				'committed' => 0,
				'msg' => strip_tags($message),
				'tvalue' => strip_tags($reason),
			]
		);
	}

	public function getFoodsaverBetriebe($fsId): array
	{
		$stm = '
			SELECT 	b.id, b.name
			FROM 	fs_betrieb_team t,
					fs_betrieb b
			WHERE 	t.betrieb_id = b.id
			AND 	t.foodsaver_id = :foodsaver_id				
		';

		return $this->db->fetchAll($stm, [':foodsaver_id' => (int)$fsId]);
	}

	public function delReport($id): void
	{
		$this->db->delete('fs_report', ['id' => (int)$id]);
	}

	public function confirmReport($id): void
	{
		$this->db->update('fs_report', ['committed' => 1], ['id' => $id]);
	}

	public function getReportStats(): array
	{
		$ret = $this->db->fetchAll(
			'
			SELECT 	`committed`, COUNT(`id`) as count
			FROM 	fs_report
			GROUP BY `committed`
			ORDER BY `committed`
		'
		);
		$new = 0;
		$com = 0;
		foreach ($ret as $r) {
			if ($r['committed'] == 0) {
				$new = $r['count'];
			} else {
				$com = $r['count'];
			}
		}

		return [
			'com' => $com,
			'new' => $new
		];
	}

	public function getReportedSaver($id): ?array
	{
		$stm = '
			SELECT 	`id`,
					`name`,
					`nachname`,
					`photo`,
					sleep_status

			FROM 	`fs_foodsaver`
				
			WHERE 	id = :id
		';
		if ($fs = $this->db->fetch($stm, [':id' => (int)$id])
		) {
			$stm = '
				SELECT 
					r.id,
	            	r.`msg`,
	            	r.`tvalue`,
	            	r.`reporttype`,
					r.`time`,
					UNIX_TIMESTAMP(r.`time`) AS time_ts,
					
					rp.id AS rp_id,
					rp.name AS rp_name,
					rp.nachname AS rp_nachname,
					rp.photo AS rp_photo					
          
				FROM
	            	`fs_report` r
					
	         	LEFT JOIN
	            	`fs_foodsaver` fs ON r.foodsaver_id = fs.id 
					
				LEFT JOIN
	            	`fs_foodsaver` rp ON r.reporter_id = rp.id 
				
				WHERE
					r.foodsaver_id = :id
					
	          	ORDER BY 
					r.`time` DESC					
			';
			$fs['reports'] = $this->db->fetchAll($stm, [':id' => (int)$id]);

			if ($fs['reports'] === false) {
				$fs['reports'] = [];
			}

			return $fs;
		}

		return null;
	}

	public function getReport($id): ?array
	{
		$stm = '
			SELECT 
				r.id,
            	r.`msg`,
            	r.`tvalue`,
            	r.`reporttype`,
				r.`time`,
				r.committed,
				r.betrieb_id,
				UNIX_TIMESTAMP(r.`time`) AS time_ts,
				
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.nachname AS fs_nachname,
				fs.photo AS fs_photo,
				
				rp.id AS rp_id,
				rp.name AS rp_name,
				rp.nachname AS rp_nachname,
				rp.photo AS rp_photo				
          
			FROM
            	`fs_report` r
				
         	LEFT JOIN
            	`fs_foodsaver` fs ON r.foodsaver_id = fs.id 
				
			LEFT JOIN
            	`fs_foodsaver` rp ON r.reporter_id = rp.id 

			WHERE
				r.`id` = :id
		';
		$report = $this->db->fetch($stm, [':id' => (int)$id]);
		if (!$report) {
			return null;
		}

		$stm = 'SELECT id, name FROM fs_betrieb WHERE id = :store_id';
		if ($report['betrieb_id'] > 0 && $betrieb = $this->db->fetch(
				$stm,
				[':store_id' => (int)$report['betrieb_id']]
			)) {
			$report['betrieb'] = $betrieb;
		}

		return $report;
	}

	private function reportSelect(): Select
	{
		$query = $this->db->fluent()
			->from('fs_report r')
			->disableSmartJoin()
			->select('
				r.id,
				r.`msg`,
				r.`tvalue`,
				r.`reporttype`,
				r.`time`,
				r.`betrieb_id`,
				s.`name` as betrieb_name,
				UNIX_TIMESTAMP(r.`time`) AS time_ts,
					
				fs.id AS fs_id,
				fs.name AS fs_name,
				fs.nachname AS fs_nachname,
				fs.photo AS fs_photo,
				fs.stadt AS fs_stadt,
	
				rp.id AS rp_id,
				rp.name AS rp_name,
				rp.nachname AS rp_nachname,
				rp.photo AS rp_photo,
				
				b.name AS b_name')
			->leftJoin('fs_foodsaver fs ON r.foodsaver_id = fs.id')
			->leftJoin('fs_foodsaver rp ON r.reporter_id = rp.id')
			->leftJoin('fs_bezirk b ON fs.bezirk_id = b.id')
			->leftJoin('fs_betrieb s ON r.betrieb_id = s.id')
			->orderBy('r.time DESC');

		return $query;
	}

	public function getReportsByReporteeRegions($regions, $excludeReportsAboutUser = null, $includeOnlyAdminsOfSelectedGroups = false)
	{
		$query = $this->reportSelect();

		if ($regions !== null && is_array($regions)) {
			if (!empty($regions)) {
				/* fluentpdo ignores the where clause when $regions is empty... */
				$query = $query->where('fs.bezirk_id', $regions);
			} else {
				return [];
			}
		}
		if ($excludeReportsAboutUser !== null) {
			$query = $query->where('fs.id != ?', $excludeReportsAboutUser);
		}
		if ($includeOnlyAdminsOfSelectedGroups) {
			$query = $query->innerJoin('fs_botschafter admin ON admin.foodsaver_id = fs.id AND admin.bezirk_id = fs.bezirk_id');
		}

		return $query->fetchAll();
	}

	public function getReportsForRegionlessByReporterRegion($regions, $excludeReportsAboutUser = null)
	{
		$query = $this->reportSelect();
		$query->where('fs.bezirk_id = 0');
		if ($regions !== null && is_array($regions)) {
			$query = $query->where('rp.bezirk_id', $regions);
		}
		if ($excludeReportsAboutUser !== null) {
			$query = $query->where('fs.id != ?', $excludeReportsAboutUser);
		}

		return $query->fetchAll();
	}

	public function getReports($committed = '0'): array
	{
		$query = $this->reportSelect();
		$query = $query->where('r.committed = ?', $committed);

		return $query->fetchAll();
	}
}
