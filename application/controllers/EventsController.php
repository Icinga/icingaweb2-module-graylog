<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog\Controllers;

use Icinga\Data\Filter\Filter;
use Icinga\Data\Filter\FilterOr;
use Icinga\Module\Graylog\Controller;
use Icinga\Module\Graylog\Graylog;
use Icinga\Module\Graylog\Eventtypes;
use Icinga\Module\Graylog\FilterRenderer;
use Icinga\Module\Graylog\Forms\EventtypeControlForm;
use Icinga\Module\Graylog\Instances;
use Icinga\Module\Monitoring\Backend\MonitoringBackend;
use Icinga\Module\Monitoring\Object\Host;
use Icinga\Module\Monitoring\Object\Macro;
use Icinga\Util\StringHelper;

class EventsController extends Controller
{
    public function indexAction()
    {
        $host = new Host(MonitoringBackend::instance(), $this->params->getRequired('host'));
//        $this->applyRestriction('monitoring/filter/objects', $host);
        if ($host->fetch() === false) {
            $this->httpNotFound($this->translate('Host not found or not permitted'));
        }

        $host->populate();

        $eventtypesRepo = new Eventtypes();

        if (! (new Instances())->select()->hasResult()) {
            $this->setTitle($this->translate('Events'));

            $this->_helper->viewRenderer->setRender('eventtypes/create-instance', null, true);

            return;
        }

        if (! $eventtypesRepo->select()->hasResult()) {
            $this->setTitle($this->translate('Events'));

            $this->_helper->viewRenderer->setRender('create-eventtype');

            return;
        }

        $eventtypeForm = new EventtypeControlForm();
        $eventtypeForm->handleRequest();

        $this->view->eventtypeForm = $eventtypeForm;

        $eventtypeFilter = Filter::where(
            'name',
            $this->params->get('eventtype', $eventtypesRepo->select(['name'])->fetchRow()->name)
        );

        $allowedTypes = array_reduce(
            $this->getRestrictions('graylog/eventtypes'),
            function (FilterOr $carry, $item) {
                foreach (StringHelper::trimSplit($item) as $eventtype) {
                    return $carry->orFilter(Filter::where('name', $eventtype));
                }
            },
            Filter::matchAny()
        );

        $eventtype = $eventtypesRepo
            ->select()
            ->applyFilter(! $allowedTypes->isEmpty() ? Filter::matchAll($eventtypeFilter, $allowedTypes) : $eventtypeFilter)
            ->fetchRow();

        if ($eventtype === false) {
            $this->httpNotFound($this->translate('Event type not found or not permitted'));
        }

        $this->setTitle(sprintf($this->translate('%s Events'), $eventtype->name));

        $instance = (new Instances())
            ->select()
            ->where('name', $eventtype->instance)
            ->fetchRow();

        if ($instance === false) {
            $this->httpNotFound($this->translate('Instance for the event type not found'));
        }

        $filterString = preg_replace_callback('/\{([\w._]+)\}/', function ($match) use ($host) {
            return Macro::resolveMacro($match[1], $host);
        }, $eventtype->filter);


        $graylogFilter = new FilterRenderer(Filter::fromQueryString($filterString));

        $query = (new Graylog($instance))
            ->select(StringHelper::trimSplit($eventtype->fields))
            ->from($eventtype->index)
            ->filter($graylogFilter->getQuery());

        //var_dump($query);

        $this->paginate($query);

        $this->view->host = $host;
        $this->view->events = $query->fetchAll();
        $this->view->fields = $query->getFields();

        $this->setAutorefreshInterval(10);
    }
}