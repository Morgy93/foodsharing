<?php

namespace Foodsharing\Modules\Search;

use Foodsharing\Modules\Core\BaseGateway;
use Foodsharing\Modules\Core\Database;
use Foodsharing\Modules\Region\RegionGateway;

class SearchGateway extends BaseGateway
{
	private $regionGateway;

	public function __construct(Database $db, RegionGateway $regionGateway)
	{
		parent::__construct($db);
		$this->regionGateway = $regionGateway;
	}

	/**
	 * Searches the given term in the database of regions, foodsavers and companies.
	 *
	 * @param string $q Query string / search term
	 * @param bool $showDetails show detailed address info if true. Show only city if false
	 * @param array $regionToSearch Optional region id to limit search to
	 *
	 * @return array Array of regions, foodsavers and stores containing the search term
	 */
	public function search(string $q, bool $showDetails, array $regionToSearch = []): array
	{
		$out = array();

		$regions = false;
		if (!empty($regionToSearch)) {
			$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionToSearch);
		}

		$out['foodsaver'] = $this->searchTable(
			'fs_foodsaver',
			array('name', 'nachname', 'plz', 'stadt'),
			$q,
			array(
				'name' => 'CONCAT(`name`," ",`nachname`)',
				'click' => 'CONCAT("profile(",`id`,");")',
				'teaser' => $showDetails ? 'CONCAT(`anschrift`,", ",`plz`," ",`stadt`)' : 'stadt'
			),
			$regions
		);

		$out['bezirk'] = $this->searchTable(
			'fs_bezirk',
			array('name'),
			$q,
			array(
				'name' => '`name`',
				'click' => 'CONCAT("goTo(\'/?page=bezirk&bid=",`id`,"\');")',
				'teaser' => 'CONCAT("")'
			)
		);

		$out['betrieb'] = $this->searchTable(
			'fs_betrieb',
			array('name', 'stadt', 'plz', 'str'),
			$q,
			array(
				'name' => '`name`',
				'click' => 'CONCAT("betrieb(",`id`,");")',
				'teaser' => 'CONCAT(`str`,", ",`plz`," ",`stadt`)'
			),
			$regions
		 );

		return $out;
	}

	public function searchTable($table, $fields, $query, $show = array(), $regions_to_search = false): array
	{
		$q = trim($query);

		str_replace(array(',', ';', '+', '.'), ' ', $q);

		do {
			$q = str_replace('  ', ' ', $q);
		} while (strpos($q, '  ') !== false);

		$terms = explode(' ', $q);

		foreach ($terms as $i => $t) {
			$terms[$i] = $this->db->quote('%' . $t . '%');
		}

		$fsql = 'CONCAT(' . implode(',', $fields) . ')';

		$fs_sql = '';
		if ($regions_to_search !== false) {
			$fs_sql = ' AND bezirk_id IN(' . implode(',', $regions_to_search) . ')';
		}

		return $this->db->fetchAll('
			SELECT 	`id`,
					 ' . $show['name'] . ' AS name,
					 ' . $show['click'] . ' AS click,
					 ' . $show['teaser'] . ' AS teaser
	
		
			FROM 	' . $table . '
	
			WHERE ' . $fsql . ' LIKE ' . implode(' AND ' . $fsql . ' LIKE ', $terms) . '
			' . $fs_sql . '
	
			ORDER BY `name`
				
			LIMIT 0,50
			
		');
	}
}
