<?php
/* Icinga Web 2 Elasticsearch Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog;

use Icinga\Data\Paginatable;
use Icinga\Web\Widget\Paginator;

class Controller extends \Icinga\Web\Controller
{
    /**
     * Set the title tab
     *
     * @param   string  label
     */
    public function setTitle($label)
    {
        $this->getTabs()->add(uniqid(), [
                'active' => true,
                'label'  => $label,
                'url'    => $this->getRequest()->getUrl()
        ]);
    }

    public function Paginate(Paginatable $paginatable, $itemsPerPage = 25, $pageNumber = 0)
    {
        $request = $this->getRequest();
        $limit = $request->getParam('limit', $itemsPerPage);
        $page = $request->getParam('page', $pageNumber);
        $paginatable->limit($limit, $page > 0 ? ($page - 1) * $limit : 0);

        if (! $this->view->compact) {
            $paginator = new Paginator();
            $paginator->setQuery($paginatable);
            $this->view->paginator = $paginator;

        }

        return $this;
    }

}
