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
	 * @param mixed $regionToSearch optional region id to limit search to
	 *
	 * @return array Array of regions, foodsavers and stores containing the search term
	 */
	public function search(string $q, bool $showDetails, $regionToSearch = null): array
	{
		$out = [];

		$regions = false;
		if (!empty($regionToSearch)) {
			$regions = $this->regionGateway->listIdsForDescendantsAndSelf($regionToSearch);
		}

		$out['foodsaver'] = $this->searchTable(
			'fs_foodsaver',
			['name', 'nachname', 'plz', 'stadt'],
			$q,
			[
				'name' => 'CONCAT(`name`," ",`nachname`)',
				'click' => 'CONCAT("profile(",`id`,");")',
				'teaser' => $showDetails ? 'CONCAT(`anschrift`,", ",`plz`," ",`stadt`)' : 'stadt'
			],
			$regions
		);

		$out['bezirk'] = $this->searchTable(
			'fs_bezirk',
			['name'],
			$q,
			[
				'name' => '`name`',
				'click' => 'CONCAT("goTo(\'/?page=bezirk&bid=",`id`,"\');")',
				'teaser' => 'CONCAT("")'
			]
		);

		$out['betrieb'] = $this->searchTable(
			'fs_betrieb',
			['name', 'stadt', 'plz', 'str'],
			$q,
			[
				'name' => '`name`',
				'click' => 'CONCAT("betrieb(",`id`,");")',
				'teaser' => 'CONCAT(`str`,", ",`plz`," ",`stadt`)'
			],
			$regions
		 );

		return $out;
	}

	public function searchTable($table, $fields, $query, $show = [], $regions_to_search = false): array
	{
		$q = trim($query);

		str_replace([',', ';', '+', '.'], ' ', $q);

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

	/**
	 * @param string $q Search string as provided by an end user. Individual words all have to be found in the result, each being the prefixes of words of the results
	 *(e.g. hell worl is expanded to a MySQL match condition of +hell* +worl*). The input string is properly sanitized, e.g. no further control over the search operation is possible.
	 * @param array|null $groupIds the groupids a person must be in to be found. Set to null to query over all users.
	 */
	public function searchUserInGroups(string $q, ?array $groupIds = []): array
	{
		/*
		 * SELECT name, nachname FROM fs_foodsaver fs, fs_foodsaver_has_bezirk hb WHERE MATCH (fs.name, fs.nachname) AGAINST ('+Jan* +Beck*' IN BOOLEAN MODE) AND hb.bezirk_id IN (741) AND hb.foodsaver_id = fs.id
		 */
		/* remove all non-word characters as they will not be indexed by the database and might change the search condition */
		$q = mb_ereg_replace('\W', ' ', $q);
		/* put + before and * after the words, omitting all words with less than 3 characters, because they would not be found in the result. */
		/* TODO: this number depends on innodb_ft_min_token_size MySQL setting. It could be viable setting it to 1 alternatively. */
		$searchString = implode(' ',
			array_map(
				function ($a) { return '+' . $a . '*'; },
				array_filter(
					explode(' ', $q),
					function ($v) { return mb_strlen($v) > 2; }
					)
			)
		);
		$select = 'SELECT fs.id, fs.name, fs.nachname FROM fs_foodsaver fs';
		$fulltextCondition = 'MATCH (fs.name, fs.nachname) AGAINST (? IN BOOLEAN MODE) AND deleted_at IS NULL';
		if ($groupIds === null) {
			return $this->db->fetchAll($select . ' WHERE ' . $fulltextCondition, [$searchString]);
		} else {
			return $this->db->fetchAll(
				$select . ', fs_foodsaver_has_bezirk hb WHERE ' .
				$fulltextCondition .
				' AND fs.id = hb.foodsaver_id AND hb.bezirk_id IN (' . $this->db->generatePlaceholders(count($groupIds)) . ')',
				array_merge([$searchString], $groupIds));
		}
	}
}
