<?php
/* Icinga Web 2 Graylog Module (c) 2017 Icinga Development Team | GPLv2+ */

namespace Icinga\Module\Graylog\Forms;

use Icinga\Data\Filter\Filter;
use Icinga\Forms\RepositoryForm;
use Icinga\Module\Graylog\Instances;

/**
 * Create, update and delete Graylog instances
 */
class InstanceConfigForm extends RepositoryForm
{
    public function init()
    {
        $this->repository = new Instances();
        $this->redirectUrl = 'graylog/instances';
    }

    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    protected function onUpdateSuccess()
    {
        if ($this->getElement('btn_remove')->isChecked()) {
            $this->setRedirectUrl("graylog/instances/delete?instance={$this->getIdentifier()}");
            $success = true;
        } else {
            $success = parent::onUpdateSuccess();
        }

        return $success;
    }

    protected function createBaseElements(array $formData)
    {
        $this->addElement(
            'text',
            'name',
            array(
                'description'       => $this->translate('Name of the Graylog instance'),
                'label'             => $this->translate('Instance name'),
                'placeholder'       => 'Graylog',
                'required'          => true
            )
        );
        $this->addElement(
            'text',
            'uri',
            array(
                'description'       => $this->translate('Name of the Graylog instance'),
                'label'             => $this->translate('Instance name'),
                'placeholder'       => 'http://localhost:9000/api',
                'required'          => true
            )
        );
        $this->addElement(
            'text',
            'user',
            array(
                'description'       => $this->translate('Basic Auth username'),
                'label'             => $this->translate('User'),
                'placeholder'       => 'admin',
                'required'          => true
            )
        );
        $this->addElement(
            'password',
            'password',
            array(
                'description'       => $this->translate('Basic Auth password'),
                'label'             => $this->translate('Password'),
                'renderPassword'    => true,
                'required'          => true
            )
        );
    }

    protected function createInsertElements(array $formData)
    {
        $this->createBaseElements($formData);

        $this->setTitle($this->translate('Create a New Instance'));

        $this->setSubmitLabel($this->translate('Save'));
    }

    protected function createUpdateElements(array $formData)
    {
        $this->createBaseElements($formData);

        $this->setTitle(sprintf($this->translate('Update instance %s'), $this->getIdentifier()));

        $this->addElement(
            'submit',
            'btn_submit',
            [
                'decorators'        => ['ViewHelper'],
                'ignore'            => true,
                'label'             => $this->translate('Save')
            ]
        );

        $this->addElement(
            'submit',
            'btn_remove',
            [
                'decorators'        => ['ViewHelper'],
                'ignore'            => true,
                'label'             => $this->translate('Remove')
            ]
        );

        $this->addDisplayGroup(
            ['btn_submit', 'btn_remove'],
            'form-controls',
            [
                'decorators' => [
                    'FormElements',
                    ['HtmlTag', ['tag' => 'div', 'class' => 'control-group form-controls']]
                ]
            ]
        );
    }

    protected function createDeleteElements(array $formData)
    {
        $this->setTitle(sprintf($this->translate('Remove instance %s'), $this->getIdentifier()));

        $this->setSubmitLabel($this->translate('Yes'));
    }

    protected function createFilter()
    {
        return Filter::where('name', $this->getIdentifier());
    }

    protected function getInsertMessage($success)
    {
        return $success
            ? $this->translate('Graylog instance created')
            : $this->translate('Failed to create Graylog instance');
    }

    protected function getUpdateMessage($success)
    {
        return $success
            ? $this->translate('Graylog instance updated')
            : $this->translate('Failed to update Graylog instance');
    }

    protected function getDeleteMessage($success)
    {
        return $success
            ? $this->translate('Graylog instance removed')
            : $this->translate('Failed to remove Graylog instance');
    }
}