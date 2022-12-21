<?php

namespace Foodsharing\Modules\WallPost;

use Foodsharing\Modules\Core\View;

class WallPostView extends View
{
    private string $table;
    private int $wallId;

    public function setTable(string $table, int $wallId): void
    {
        $this->table = $table;
        $this->wallId = $wallId;
    }

    public function posts(array $posts, bool $mayDelete): string
    {
        /*
         [0] => Array
        (
            [id] => 1
            [body] => dfghfgh
            [time] => 2014-01-25 22:27:13
            [time_ts] => 1390685233
            [attach] =>
            [foodsaver_id] => 56
            [name] => Raphael
            [nachname] => Wintrich
            [photo] => 2cb1258a658ed46e0704764e1a2f491d.png
        )
         */
        $out = '
		<table class="pintable">
			<tbody>';

        foreach ($posts as $p) {
            $gallery = '';
            $gal_col = '';
            if (isset($p['gallery'])) {
                $gal_col = '';
                $gallery = '
				<div class="gallery">';
                foreach ($p['gallery'] as $img) {
                    $gallery .= '<a href="/' . $img['image'] . '" class="preview-thumb" rel="wallpost-gallery-' . $p['id'] . '"><img src="/' . $img['medium'] . '" /></a>';
                }
                $gallery .= '
					<div class="clear"></div>
				</div>';
            }
            $del = '';
            if ($mayDelete || $p['foodsaver_id'] == $this->session->id()) {
                $del = '<span class="dot">·</span><a onclick="delWallpost(' . $p['id'] . ', \'' . $this->table . '\', ' . $this->wallId . ');return false;" href="#p' . $p['id'] . '" class="pdelete light">' . $this->translator->trans('wall.delete') . '</a>';
            }

            $out .= '
				<tr class="odd bpost wallpost-' . $p['id'] . '">
					<td class="img">
						<input type="hidden" name="pid" class="pid" value="' . $p['id'] . '" />
						<a href="/profile/' . $p['foodsaver_id'] . '">
							<img src="' . $this->imageService->img($p['photo']) . '" />
						</a>
					</td>
					<td' . $gal_col . '>
					<span class="msg">
						' . nl2br($p['body']) . '
						' . $gallery . '
					</span>
					<div class="foot">
						<span class="time">' . $this->timeHelper->niceDate($p['time_ts']) . ' ' . $this->translator->trans('tablesorter.from') . ' ' . $p['name'] . '</span>' . $del . '
					</div>
					</td>
				</tr>';
        }

        return $out . '
			</tbody>
		</table>';
    }
}
