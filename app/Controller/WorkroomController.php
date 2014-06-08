<?php namespace App\Controller;

class WorkroomController extends Controller {
	protected $repository = 'WorkEntry';
	protected $responseAge = 0;

	public function indexAction($status, $page) {
		$_REQUEST['status'] = $status;
		$_REQUEST['page'] = $page;

		return $this->legacyPage('Work');
	}

	public function listAction($_format) {
		$_REQUEST['vl'] = 'listonly';
		$this->responseFormat = $_format;

		return $this->legacyPage('Work');
	}

	public function listContributorsAction() {
		$_REQUEST['vl'] = 'contrib';

		return $this->legacyPage('Work');
	}

	public function showAction($id) {
		$_REQUEST['id'] = $id;

		return $this->legacyPage('Work');
	}

	public function newAction() {
		if ($this->getUser()->isAnonymous()) {
			throw $this->createAccessDeniedException('Нямате достатъчни права за това действие.');
		}
		$_REQUEST['id'] = 0;
		$_REQUEST['status'] = 'edit';

		return $this->legacyPage('Work');
	}
	public function createAction() {
		return $this->legacyPage('Work');
	}
	public function editAction($id) {
		$_REQUEST['id'] = $id;
		$_REQUEST['status'] = 'edit';

		return $this->legacyPage('Work');
	}
	public function updateAction() {
		return $this->legacyPage('Work');
	}
	public function deleteAction() {
		return $this->legacyPage('Work');
	}

	public function deleteContribAction($id) {
		$this->responseAge = 0;

		if (!$this->getUser()->inGroup('workroom-admin')) {
			throw $this->createAccessDeniedException('Нямате достатъчни права за това действие.');
		}

		$contrib = $this->em()->getWorkContribRepository()->find($id);
		if ($contrib === null) {
			throw $this->createNotFoundException();
		}
		$entry = $contrib->getEntry();
		$contrib->delete();
		$this->em()->getWorkContribRepository()->save($contrib);

		if ($this->get('request')->isXmlHttpRequest()) {
			return $this->displayJson($contrib);
		}

		return $this->urlRedirect($this->generateUrl('workroom_entry_edit', array('id' => $entry->getId())));
	}

	public function rssAction() {
		$_REQUEST['type'] = 'work';

		return $this->legacyPage('Feed');
	}

	public function latestAction($limit = 10) {
		$this->view = array(
			'entries' => $this->em()->getWorkEntryRepository()->getLatest($limit),
		);

		return $this->display('WorkEntry:list');
	}
}
