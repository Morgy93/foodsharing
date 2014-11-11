<?php
class BellModel extends Model
{
	/**
	 * Method returns an array of all conversation from the user
	 *
	 * @return Ambigous <boolean, array >
	 */
	public function listBells($limit = '')
	{
		if($limit != '')
		{
			$limit = ' LIMIT 0,'.(int)$limit;
		}
	
		if($bells = $this->q('
			SELECT
				b.`id`,
				b.`name`, 
				b.`body`, 
				b.`vars`, 
				b.`attr`, 
				b.`icon`, 
				b.`identifier`, 
				b.`time`,
				UNIX_TIMESTAMP(b.`time`) AS time_ts,
				hb.seen,
				b.closeable
	
			FROM
				'.PREFIX.'bell b,
				`'.PREFIX.'foodsaver_has_bell` hb
	
			WHERE
				hb.bell_id = b.id
	
			AND
				hb.foodsaver_id = '.(int)fsId().'
	
			ORDER BY b.`time` DESC
			'.$limit.'
		'))
		{
			$ids = array();
			for($i=0;$i<count($bells);$i++)
			{
				$ids[] = (int)$bells[$i]['id'];
				
				if(!empty($bells[$i]['vars']))
				{
					$bells[$i]['vars'] = unserialize($bells[$i]['vars']);
				}
				
				if(!empty($bells[$i]['attr']))
				{
					$bells[$i]['attr'] = unserialize($bells[$i]['attr']);
				}
			}
			
			$this->setBellsAsSeen($ids);
				
			return $bells;
		}
		return false;
	}
	
	public function getBetriebBells($bids)
	{
		return $this->q('SELECT COUNT( b.id ) AS count, b.name, b.id, MAX( a.date ) AS `date`, UNIX_TIMESTAMP(MAX( a.date )) AS date_ts FROM `fs_betrieb` b, fs_abholer a	WHERE a.betrieb_id = b.id AND a.betrieb_id IN('.implode(',',$bids).') AND	a.confirmed = 0 GROUP BY b.id');
	}
	
	public function delbell($id)
	{
		return $this->del('DELETE FROM `'.PREFIX.'foodsaver_has_bell` WHERE `bell_id` = '.(int)$id.' AND foodsaver_id = '.(int)fsId());
	}
	
	public function setBellsAsSeen($bids)
	{
		$this->update('UPDATE `'.PREFIX.'foodsaver_has_bell` SET `seen` = 1 WHERE `bell_id` IN('.implode(',', $bids).')');
	}
}