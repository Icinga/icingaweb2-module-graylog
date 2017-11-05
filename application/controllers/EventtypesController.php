<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog\Controllers;

use Icinga\Module\Graylog\Controller;
use Icinga\Module\Graylog\Eventtypes;
use Icinga\Module\Graylog\Forms\EventtypeConfigForm;
use Icinga\Module\Graylog\Instances;
use Icinga\Web\Url;

class EventtypesController extends Controller
{
    public function init()
    {
        $this->assertPermission('graylog/config');
    }

    public function indexAction()
    {
        $this->getTabs()->add(uniqid(), [
            'label'     => $this->translate('Instances'),
            'url'       => Url::fromPath('graylog/instances')
        ]);

        $this->setTitle($this->translate('Event Types'));

        if (! (new Instances())->select()->hasResult()) {
            $this->_helper->viewRenderer->setRender('create-instance');

            return;
        }

        $this->view->eventtypes = (new Eventtypes())->select(['name', 'instance', 'index', 'filter', 'fields']); //TODO: index -> stream?
    }

    public function newAction()
    {
        $form = new EventtypeConfigForm([
            'mode'  => EventtypeConfigForm::MODE_INSERT
        ]);

        $form->handleRequest();

        $this->setTitle($this->translate('New Event Type'));

        $this->view->form = $form;

        $this->_helper->viewRenderer->setRender('form', null, true);
    }

    public function updateAction()
    {
        $name = $this->params->getRequired('eventtype');

        $form = new EventtypeConfigForm([
            'mode'          => EventtypeConfigForm::MODE_UPDATE,
            'identifier'    => $name
        ]);

        $form->handleRequest();

        $this->setTitle($this->translate('Update Event Type'));

        $this->view->form = $form;

        $this->_helper->viewRenderer->setRender('form', null, true);
    }

    public function deleteAction()
    {
        $name = $this->params->getRequired('eventtype');

        $form = new EventtypeConfigForm([
            'mode'          => EventtypeConfigForm::MODE_DELETE,
            'identifier'    => $name
        ]);

        $form->handleRequest();

        $this->setTitle($this->translate('Remove Event Type'));

        $this->view->form = $form;

        $this->_helper->viewRenderer->setRender('form', null, true);
    }
}