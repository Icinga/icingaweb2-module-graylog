<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

/** @var Icinga\Application\Modules\Module $this */

//require_once $this->getLibDir() . '/vendor/Psr/Loader.php';
//require_once $this->getLibDir() . '/vendor/iplx/Loader.php';

$this->provideHook('monitoring/HostActions');
