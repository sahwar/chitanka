<?php namespace App\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class SiteAdmin extends Admin {
	protected $baseRoutePattern = 'site';
	protected $baseRouteName = 'admin_site';

	protected function configureShowField(ShowMapper $showMapper) {
		$showMapper
			->add('name')
			->add('url')
			->add('description')
		;
	}

	protected function configureListFields(ListMapper $listMapper) {
		$listMapper
			->addIdentifier('name')
			->add('url')
			->add('_action', 'actions', [
				'actions' => [
					'show' => [],
					'edit' => [],
					'delete' => [],
				]
			])
		;
	}

	protected function configureFormFields(FormMapper $formMapper) {
		$formMapper->with('General attributes')
			->add('name')
			->add('url')
			->add('description', null, ['required' => false])
			->end();
	}

	protected function configureDatagridFilters(DatagridMapper $datagrid) {
		$datagrid
			->add('name')
			->add('url')
			->add('description')
		;
	}

}
