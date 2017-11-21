<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog\ProvidedHook\Monitoring;

use Icinga\Module\Monitoring\Web\Hook\HostActionsHook;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Web\Url;

class HostActions extends HostActionsHook
{
    public function getActionsForHost(Host $host)
    {
        return $this->createNavigation([
            mt('graylog', 'Graylog Events') => [
                'icon'          => 'doc-text',
                'permission'    => 'graylog/events',
                'url'           => Url::fromPath('graylog/events', ['host' => $host->getName()])
            ]
        ]);
    }
}
