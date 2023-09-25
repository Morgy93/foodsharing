<?php

namespace Foodsharing\Utility;

use Html2Text\Html2Text;
use HTMLPurifier;
use Parsedown;

class Sanitizer
{
    private Parsedown $parseDown;
    private HTMLPurifier $htmlPurifier;

    public function __construct(Parsedown $parseDown, HTMLPurifier $HTMLPurifier)
    {
        $this->parseDown = $parseDown;
        $this->htmlPurifier = $HTMLPurifier;
    }

    public function plainToHtml(string $text): string
    {
        return nl2br(htmlspecialchars($text));
    }

    public function markdownToHtml(string $text): string
    {
        $html = $this->parseDown->text($text);

        return $this->htmlPurifier->purify($html);
    }

    public function purifyHtml(string $html): string
    {
        return $this->htmlPurifier->purify($html);
    }

    public function htmlToPlain(string $html): string
    {
        $html = new Html2Text($html);

        return $html->getText();
    }

    public function tagSelectIds(array $v): array
    {
        $result = [];
        foreach ($v as $idKey => $value) {
            $result[] = explode('-', $idKey)[0];
        }

        return $result;
    }

    public function handleTagSelect(string $identifier, array $data): array
    {
        $recip = [];
        if (isset($data[$identifier]) && is_array($data[$identifier])) {
            foreach ($data[$identifier] as $key => $r) {
                if ($key != '') {
                    $part = explode('-', $key);
                    $recip[$part[0]] = $part[0];
                }
            }
        }

        $data[$identifier] = $recip;

        return $data;
    }

    public function jsSafe(string $str, string $quote = "'"): string
    {
        return str_replace([$quote, "\n", "\r"], ['\\' . $quote . '', '\\n', ''], $str);
    }

    /* this method returns the string $str truncated to the first $length characters while keeping words intact.
       It adds ' ...' as an ellipsis. */
    public function tt(string $str, int $length = 160): string
    {
        if (mb_strlen($str) > $length) {
            $shortened = mb_substr($str, 0, $length - 4);
            $followingChar = mb_substr($str, $length - 4, 1);
            if (preg_match('/\s/', $followingChar) !== false) {
                /* following char is whitespace -> last word is complete but might have whitespace at the end */
                return preg_replace('/\s$/', '', $shortened) . ' ...';
            }

            /* word is broken: remove it */
            return preg_replace('/\s?\S*$/', '', $shortened) . ' ...';
        }

        return $str;
    }
}
