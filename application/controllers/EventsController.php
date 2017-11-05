<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Module\Graylog\Controller;
use Icinga\Module\Graylog\Eventtypes;
use Icinga\Module\Graylog\Forms\EventtypeControlForm;
use Icinga\Module\Graylog\Instances;
use Icinga\Module\Nonitoring\Backend\MonitoringBackend;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Module\Monitoring\Object\Macro;
use Icinga\Util\StringHelper;

class EventsController extends Controller
{
    public function indexAction()
    {
        //TODO
    }
}