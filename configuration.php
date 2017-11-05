<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

/** @var Icinga\Application\Modules\Module $this */

$this->providePermission(
    'graylog/config',
    $this->translate('Allow to configure Graylog instances and event types')
);

/*
$this->provideRestriction(
    'graylog/eventtypes',
    $this->translate('Restrict the event types the user may use')
);
*/

$this->provideConfigTab('graylog/instances', array(
    'title' => $this->translate('Configure Graylog Instances'),
    'label' => $this->translate('Graylog Instances'),
    'url'   => 'instances'
));

/*
$this->provideConfigTab('graylog/eventtypes', array(
    'title' => $this->translate('Configure Event Types'),
    'label' => $this->translate('Event Types'),
    'url'   => 'eventtypes'
));
*/
