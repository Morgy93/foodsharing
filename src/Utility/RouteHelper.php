<?php

namespace Foodsharing\Utility;

use Foodsharing\Lib\Session;
use Foodsharing\Modules\Legal\LegalControl;
use Foodsharing\Modules\Legal\LegalGateway;
use Symfony\Contracts\Translation\TranslatorInterface;

final class RouteHelper
{
    public function __construct(
        private readonly Session $session,
        private readonly TranslatorInterface $translator,
        private readonly LegalGateway $legalGateway
    ) {
    }

    public function goAndExit(string $url): never
    {
        header('Location: ' . $url);
        exit;
    }

    public function goSelfAndExit(): never
    {
        $this->goAndExit($this->getSelf());
    }

    public function goLoginAndExit(): never
    {
        $this->goAndExit('/?page=login&ref=' . urlencode($this->getSelf()));
    }

    public function goPageAndExit(string $page = ''): never
    {
        if (empty($page)) {
            $page = $this->getPage();
            if (isset($_GET['bid'])) {
                $page .= '&bid=' . (int)$_GET['bid'];
            }
        }
        $this->goAndExit('/?page=' . $page);
    }

    public function getSelf()
    {
        return $_SERVER['REQUEST_URI'];
    }

    public function getPage(): string
    {
        return $this->getGet('page') ?: 'index';
    }

    public function getSubPage(): string
    {
        return $this->getGet('sub') ?: 'index';
    }

    private function getGet(string $name): string
    {
        return $_GET[$name] ?? false;
    }

    public function pageLink(string $page, ?string $title = null): array
    {
        return ['href' => '/?page=' . $page, 'name' => $title ?? $this->translator->trans('bread.backToOverview')];
    }

    public function autolink(string $str, array $attributes = []): string
    {
        $attributes['target'] = '_blank';
        $attrs = '';
        foreach ($attributes as $attribute => $value) {
            $attrs .= " {$attribute}=\"{$value}\"";
        }
        $str = ' ' . $str;
        $str = preg_replace(
            '`([^"=\'>])(((http|https|ftp)://|www.)[^\s<]+[^\s<\.)])`i',
            '$1<a href="$2"' . $attrs . '>$2</a>',
            $str
        ) ?: '';
        $str = substr($str, 1);
        // adds http:// if not existing
        return preg_replace('`href=\"www`', 'href="http://www', $str) ?: '';
    }

    public function getLegalControlIfNecessary(): ?string
    {
        if ($this->session->mayRole() && !$this->onSettingsOrLogoutPage() && !$this->legalRequirementsMetByUser()) {
            return LegalControl::class;
        }

        return null;
    }

    private function legalRequirementsMetByUser(): bool
    {
        return $this->usersPrivacyPolicyUpToDate() && $this->usersPrivacyNoticeUpToDate();
    }

    private function usersPrivacyPolicyUpToDate(): bool
    {
        $privacyPolicyVersion = $this->legalGateway->getPpVersion();

        return $privacyPolicyVersion && $privacyPolicyVersion == $this->session->user('privacy_policy_accepted_date');
    }

    private function usersPrivacyNoticeUpToDate(): bool
    {
        if ($this->session->user('rolle') < 2) {
            return true;
        }
        $privacyNoticeVersion = $this->legalGateway->getPnVersion();

        return $privacyNoticeVersion && $privacyNoticeVersion == $this->session->user('privacy_notice_accepted_date');
    }

    private function onSettingsOrLogoutPage(): bool
    {
        return in_array($this->getPage(), ['settings', 'logout']);
    }
}
