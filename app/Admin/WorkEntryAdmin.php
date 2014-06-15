<?php namespace App\Admin;

use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class WorkEntryAdmin extends Admin {
	protected $baseRoutePattern = 'work-entry';
	protected $baseRouteName = 'admin_work_entry';

	protected function configureShowField(ShowMapper $showMapper) {
		$showMapper
			->add('type')
			->add('title')
			->add('author')
			->add('user')
			->add('comment')
			->add('status')
			->add('progress')
			->add('isFrozen')
			->add('tmpfiles')
			->add('tfsize')
			->add('uplfile')
			->add('adminStatus')
			->add('adminComment')
			->add('deletedAt')
		;
	}

	protected function configureListFields(ListMapper $listMapper) {
		$listMapper
			->addIdentifier('title')
			->add('author')
			->add('_action', 'actions', array(
				'actions' => array(
					'view' => array(),
					'edit' => array(),
					'delete' => array(),
				)
			))
		;
	}

	protected function configureFormFields(FormMapper $formMapper) {
		$formMapper->with('General attributes');
		$formMapper
			->add('type')
			->add('title')
			->add('author', null, array('required' => false))
			->add('user', 'sonata_type_model_list', array('required' => false))
			->add('comment', null, array('required' => false))
			->add('status')
			->add('progress')
			->add('isFrozen', null, array('required' => false))
			->add('tmpfiles', null, array('required' => false))
			->add('tfsize', null, array('required' => false))
			->add('uplfile', null, array('required' => false))
			->add('adminStatus')
			->add('adminComment')
			->add('deletedAt', null, array('required' => false));
	}

	protected function configureDatagridFilters(DatagridMapper $datagrid) {
		$datagrid
			->add('title')
			->add('author')
//			->add('user')
			->add('status')
			->add('progress')
			->add('isFrozen')
			->add('type')
			->add('adminStatus')
//			->add('date')
			->add('is_deleted', 'doctrine_orm_callback', array(
				'callback' => function($queryBuilder, $alias, $field, $value) {
					if (!$value) {
						return;
					}

					$queryBuilder->andWhere("$alias.deletedAt IS NOT NULL");
				},
				'field_type' => 'checkbox'
			))
		;
	}

}
