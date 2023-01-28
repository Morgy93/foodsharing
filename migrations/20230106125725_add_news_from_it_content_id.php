<?php

declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AddNewsFromItContentId extends AbstractMigration
{
    /**
     * Change Method.
     *
     * Write your reversible migrations using this method.
     *
     * More information on writing migrations is available here:
     * https://book.cakephp.org/phinx/0/en/migrations.html#the-change-method
     *
     * Remember to call "create()" or "update()" and NOT "save()" when working
     * with the Table class.
     */
    public function change(): void
    {
        $body = '<p>Stand: 28.12.2022 - 13:08 Uhr</p>
<div class="broadcastfield__description" data-v-4bfff960="">
<ul>
<li>Seit dem letzten Update am Sonntag den 18.12 werden interne E-Mails von @foodsharing.network an @foodsharing.network nicht zugestellt. <br />Wir arbeiten an einer L&ouml;sung. siehe Gitlab Issue&nbsp;<a href="https://gitlab.com/foodsharing-dev/foodsharing/-/issues/1541" target="_blank">1541</a></li>
<li>Links&nbsp;im Chat sind nicht korrekt - Gitlab Issue&nbsp;<a href="https://gitlab.com/foodsharing-dev/foodsharing/-/issues/1531" target="_blank">1531</a>&nbsp;<br />
<p>Workaround: Weist die Empf&auml;nger eurer PNs/ Links darauf hin, dass&nbsp;<span>ein einfacher Klick auf den Link zum falschen Ort f&uuml;hrt</span>, auch ein Rechtsklick auf den Link mit "Linkadresse kopieren" erzeugt euch nicht den korrekten Link.</p>
<p>Manuell l&auml;sst sich der im Chat&nbsp;<span>lesbare Link-Text aber markieren und der markierte Text kopieren und in ein Browser-Fenster einf&uuml;gen</span>. Dann kommt man zum richtigen Ziel. (Alternativ k&ouml;nnte man es auch &uuml;ber tinyurl.com etc versuchen... die Links d&uuml;rfen kein &amp;-Zeichen enthalten)</p>
<p>... dies nur als ein Workaround als Notl&ouml;sung, bis die Links wieder funktionieren ...<br /><br /></p>
</li>
<li>E-Mail-Benachrichtungen &uuml;ber leere Slots in Betrieben. Seit dem letzten Update wurden die E-Mail-Benachrichtigen repariert. Leider werden&nbsp;bei manuell geplanten Abholungen oder gel&ouml;schten Abholungen diese nicht korrekt bei den Benachrichtigungen ber&uuml;cksichtigt. Siehe MR&nbsp;<a href="https://gitlab.com/foodsharing-dev/foodsharing/-/merge_requests/2574" target="_blank">2574</a> wurde bereits an einer &Auml;nderung gearbeitet. Diese muss noch gepr&uuml;ft werden und online gehen.<br /><br /></li>
<li>Chat-Nachrichten auf iOS-Ger&auml;ten laden nicht. Die Behebung ist nicht so einfach und wir suchen noch eine L&ouml;sung.<br /><br /></li>
<li><span>Es gibt aktuell eine Einschr&auml;nkung beim austragen von Slots durch Betriebsverantwortliche. Die Nachricht beim austragen darf maximal 255 Zeichen lang sein. S</span><span>iehe Gitlab Issue&nbsp;<a href="https://gitlab.com/foodsharing-dev/foodsharing/-/issues/1540" target="_blank">1540</a></span></li>
</ul>
</div>';

        $this->table('fs_content')
            ->insert([
                'id' => '1',
                'name' => 'news-from-it',
                'title' => 'Aktuelle Fehler und Störungen',
                'body' => $body,
            ])
            ->save();
    }
}
