<?php

namespace OroCRM\Bundle\ReportBundle\Datagrid\Accounts;

use Oro\Bundle\GridBundle\Action\ActionInterface;
use Oro\Bundle\GridBundle\Property\UrlProperty;
use OroCRM\Bundle\ReportBundle\Datagrid\ReportGridManagerAbstract;
use Oro\Bundle\GridBundle\Field\FieldDescription;
use Oro\Bundle\GridBundle\Field\FieldDescriptionCollection;
use Oro\Bundle\GridBundle\Field\FieldDescriptionInterface;
use Oro\Bundle\GridBundle\Filter\FilterInterface;

class ByOpportunitiesManager extends ReportGridManagerAbstract
{
    /**
     * {@inheritDoc}
     */
    protected function getProperties()
    {
        return array(
            new UrlProperty('view_link', $this->router, 'orocrm_account_view', array('id')),
        );
    }

    /**
     * {@inheritDoc}
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function configureFields(FieldDescriptionCollection $fieldsCollection)
    {
        $field = new FieldDescription();
        $field->setName('name');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_TEXT,
                'label'        => 'Account name',
                'entity_alias' => 'a',
                'field_name'   => 'name',
                'filter_type'  => FilterInterface::TYPE_STRING,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        $this->addIntField('wonCount', 'Number Won', $fieldsCollection);
        $this->addIntField('lostCount', 'Number Lost', $fieldsCollection);
        $this->addIntField('inProgressCount', 'Number In Progress', $fieldsCollection);

        $field = new FieldDescription();
        $field->setName('total_ops');
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => 'Total',
                'field_name'   => 'total_ops',
                'expression'   => 'total_ops',
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('close_date');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => 'Close date',
                'field_name'  => 'closeDate',
                'filter_type' => FilterInterface::TYPE_DATE,
                'required'    => false,
                'sortable'    => false,
                'show_column' => false,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);

        $field = new FieldDescription();
        $field->setName('created_at');
        $field->setOptions(
            array(
                'type'        => FieldDescriptionInterface::TYPE_DATE,
                'label'       => 'Created date',
                'field_name'  => 'createdAt',
                'filter_type' => FilterInterface::TYPE_DATE,
                'required'    => false,
                'sortable'    => false,
                'show_column' => false,
                'filterable'  => true,
                'show_filter' => true,
            )
        );
        $fieldsCollection->add($field);
    }

    /**
     * Add won field
     *
     * @param string $name won|lost|in_progress
     * @param string $label
     * @param FieldDescriptionCollection $fieldsCollection
     * @return $this
     */
    public function addIntField($name, $label, FieldDescriptionCollection $fieldsCollection)
    {
        if (!in_array($name, array('wonCount', 'lostCount', 'inProgressCount'))) {
            return $this;
        }

        $field = new FieldDescription();
        $field->setName($name);
        $field->setOptions(
            array(
                'type'         => FieldDescriptionInterface::TYPE_INTEGER,
                'label'        => $label,
                'expression'   => $name,
                'filter_by_having' => true,
                'filter_type'  => FilterInterface::TYPE_NUMBER,
                'required'     => false,
                'sortable'     => true,
                'filterable'   => true,
                'show_filter'  => true,
            )
        );
        $fieldsCollection->add($field);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    protected function getRowActions()
    {
        $clickAction = array(
            'name'         => 'rowClick',
            'type'         => ActionInterface::TYPE_REDIRECT,
            'acl_resource' => 'orocrm_account_view',
            'options'      => array(
                'label'         => $this->translate('View'),
                'link'          => 'view_link',
                'runOnRowClick' => true,
            )
        );

        return array($clickAction);
    }

}
